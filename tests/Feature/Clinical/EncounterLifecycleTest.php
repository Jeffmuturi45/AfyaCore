<?php

namespace Tests\Feature\Clinical;

use App\Models\Branch;
use App\Models\Encounter\Encounter;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Services\Patient\PatientService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EncounterLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $receptionist;

    protected User $nurse;

    protected User $doctor;

    protected Branch $hospital;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hospital = Branch::where('code', 'HOS')->first();
        $this->receptionist = User::where('email', 'reception.hos@afyacore.test')->first();
        $this->nurse = User::where('email', 'nurse.hos@afyacore.test')->first();
        $this->doctor = User::where('email', 'doctor2.hos@afyacore.test')->first();

        // Create a test patient
        $patientService = app(PatientService::class);
        $this->patient = $patientService->registerPatient([
            'first_name' => 'Grace',
            'last_name' => 'Wanjiru',
            'date_of_birth' => '1995-11-20',
            'gender' => 'female',
            'phone_primary' => '0722334455',
            'national_id' => '31223344',
        ], $this->hospital, $this->receptionist);
    }

    /**
     * AC-13: Encounter is created with correct branch, patient, and status 'registered'.
     */
    public function test_receptionist_can_create_encounter_with_correct_branch_and_status(): void
    {
        $payload = [
            'patient_id' => $this->patient->id,
            'encounter_type' => 'opd',
            'chief_complaint' => 'Severe abdominal pain since morning',
        ];

        $response = $this->actingAs($this->receptionist)
            ->post(route('encounters.store'), $payload);

        $encounter = Encounter::where('patient_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($encounter);

        // Check attributes
        $this->assertEquals($this->hospital->id, $encounter->branch_id);
        $this->assertEquals(Encounter::STATUS_REGISTERED, $encounter->status);
        $this->assertEquals('opd', $encounter->encounter_type);
        $this->assertEquals('Severe abdominal pain since morning', $encounter->chief_complaint);
        $this->assertStringStartsWith('HOS-', $encounter->encounter_number);

        // Check status log
        $this->assertDatabaseHas('encounter_status_log', [
            'encounter_id' => $encounter->id,
            'from_status' => null,
            'to_status' => Encounter::STATUS_REGISTERED,
            'changed_by' => $this->receptionist->id,
        ]);

        // Redirects to triage for OPD encounters
        $response->assertRedirect(route('triage.create', $encounter));
    }

    /**
     * AC-14: Nurse can record triage vitals and encounter transitions to triaged or in_queue.
     */
    public function test_nurse_records_triage_and_encounter_transitions_to_triaged(): void
    {
        // 1. Create encounter
        $encounter = Encounter::create([
            'branch_id' => $this->hospital->id,
            'patient_id' => $this->patient->id,
            'encounter_type' => 'opd',
            'encounter_number' => 'HOS-20261031-9901',
            'status' => Encounter::STATUS_REGISTERED,
            'created_by' => $this->receptionist->id,
            'visit_date' => now()->toDateString(),
            'arrival_time' => now(),
        ]);

        // 2. Nurse submits triage vitals
        $triagePayload = [
            'temperature_c' => 38.5,
            'bp_systolic' => 130,
            'bp_diastolic' => 85,
            'pulse_bpm' => 88,
            'spo2_percent' => 97.5,
            'weight_kg' => 65.0,
            'height_cm' => 165.0,
            'notes' => 'Patient febrile and visibly uncomfortable',
        ];

        $response = $this->actingAs($this->nurse)
            ->post(route('triage.store', $encounter), $triagePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('triage.index'));

        // Refresh encounter
        $encounter->refresh();
        $this->assertEquals(Encounter::STATUS_TRIAGED, $encounter->status);

        // Verify TriageRecord was created with calculated BMI
        $triage = $encounter->triageRecord;
        $this->assertNotNull($triage);
        $this->assertEquals(38.5, (float) $triage->temperature_c);
        $this->assertEquals(130, $triage->bp_systolic);
        $this->assertEquals(85, $triage->bp_diastolic);
        $this->assertEquals('130/85 mmHg', $triage->bp);
        // BMI = 65 / (1.65 * 1.65) = 23.9
        $this->assertEquals(23.9, (float) $triage->bmi);

        // Verify status log entry
        $this->assertDatabaseHas('encounter_status_log', [
            'encounter_id' => $encounter->id,
            'from_status' => Encounter::STATUS_REGISTERED,
            'to_status' => Encounter::STATUS_TRIAGED,
            'changed_by' => $this->nurse->id,
        ]);
    }

    /**
     * AC-14: Nurse can assign to a specific doctor which transitions status to in_queue.
     */
    public function test_nurse_can_assign_doctor_advancing_encounter_to_in_queue(): void
    {
        $encounter = Encounter::create([
            'branch_id' => $this->hospital->id,
            'patient_id' => $this->patient->id,
            'encounter_type' => 'opd',
            'encounter_number' => 'HOS-20261031-9902',
            'status' => Encounter::STATUS_REGISTERED,
            'created_by' => $this->receptionist->id,
            'visit_date' => now()->toDateString(),
            'arrival_time' => now(),
        ]);

        $triagePayload = [
            'temperature_c' => 36.8,
            'bp_systolic' => 120,
            'bp_diastolic' => 80,
            'assign_to_doctor_id' => $this->doctor->id,
        ];

        $this->actingAs($this->nurse)
            ->post(route('triage.store', $encounter), $triagePayload);

        $encounter->refresh();
        $this->assertEquals(Encounter::STATUS_IN_QUEUE, $encounter->status);
        $this->assertEquals($this->doctor->id, $encounter->assigned_to);
    }

    /**
     * AC-15: Doctor sees triaged patient in consultations queue.
     */
    public function test_doctor_sees_triaged_patient_in_consultation_queue(): void
    {
        $encounter = Encounter::create([
            'branch_id' => $this->hospital->id,
            'patient_id' => $this->patient->id,
            'encounter_type' => 'opd',
            'encounter_number' => 'HOS-20261031-9903',
            'status' => Encounter::STATUS_TRIAGED,
            'created_by' => $this->receptionist->id,
            'visit_date' => now()->toDateString(),
            'arrival_time' => now(),
            'chief_complaint' => 'Throat irritation and dry cough',
        ]);

        $response = $this->actingAs($this->doctor)
            ->get(route('consultations.index'));

        $response->assertOk();
        $response->assertSee($this->patient->full_name);
        $response->assertSee('HOS-20261031-9903');
    }

    /**
     * AC-19: Voiding encounter requires mandatory audit reason.
     */
    public function test_voiding_encounter_requires_mandatory_reason(): void
    {
        $encounter = Encounter::create([
            'branch_id' => $this->hospital->id,
            'patient_id' => $this->patient->id,
            'encounter_type' => 'opd',
            'encounter_number' => 'HOS-20261031-9904',
            'status' => Encounter::STATUS_REGISTERED,
            'created_by' => $this->receptionist->id,
            'visit_date' => now()->toDateString(),
            'arrival_time' => now(),
        ]);

        $branchAdmin = User::where('email', 'doctor.hos@afyacore.test')->first();

        // 1. Attempt voiding without reason -> fails validation
        $failResponse = $this->actingAs($branchAdmin)
            ->post(route('encounters.void', $encounter), ['void_reason' => '']);
        $failResponse->assertSessionHasErrors('void_reason');

        // 2. Void with valid reason
        $successResponse = $this->actingAs($branchAdmin)
            ->post(route('encounters.void', $encounter), [
                'void_reason' => 'Patient left without waiting for triage',
            ]);

        $successResponse->assertSessionHasNoErrors();

        $encounter->refresh();
        $this->assertTrue($encounter->isVoid());
        $this->assertEquals('Patient left without waiting for triage', $encounter->void_reason);
        $this->assertEquals($branchAdmin->id, $encounter->voided_by);
        $this->assertNotNull($encounter->void_at);
    }
}
