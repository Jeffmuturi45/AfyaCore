<?php

namespace Tests\Feature\Clinical;

use App\Models\Branch;
use App\Models\Encounter\Encounter;
use App\Models\Inventory\StockItem;
use App\Models\Patient\Patient;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use App\Services\Patient\PatientService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConsultationAndNotesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $doctor;

    protected User $receptionist;

    protected Branch $hospital;

    protected Patient $patient;

    protected Encounter $encounter;

    protected StockItem $stockMedication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hospital = Branch::where('code', 'HOS')->first();
        $this->doctor = User::where('email', 'doctor2.hos@afyacore.test')->first();
        $this->receptionist = User::where('email', 'reception.hos@afyacore.test')->first();
        $this->stockMedication = StockItem::first();

        $patientService = app(PatientService::class);
        $this->patient = $patientService->registerPatient([
            'first_name' => 'David',
            'last_name' => 'Kiprono',
            'date_of_birth' => '1988-07-12',
            'gender' => 'male',
            'phone_primary' => '0733445566',
            'national_id' => '29334455',
        ], $this->hospital, $this->receptionist);

        $this->encounter = Encounter::create([
            'branch_id' => $this->hospital->id,
            'patient_id' => $this->patient->id,
            'encounter_type' => 'opd',
            'encounter_number' => 'HOS-20261031-8801',
            'status' => Encounter::STATUS_TRIAGED,
            'created_by' => $this->receptionist->id,
            'visit_date' => now()->toDateString(),
            'arrival_time' => now(),
            'chief_complaint' => 'High fever, chills and joint aches x 3 days',
        ]);
    }

    /**
     * AC-16: Doctor can create and save draft clinical notes, resuming later.
     */
    public function test_doctor_can_save_draft_clinical_notes_advancing_encounter_to_in_progress(): void
    {
        $payload = [
            'presenting_complaints' => 'High fever, chills, severe frontal headache x 3 days',
            'history_of_illness' => 'Symptoms started suddenly on Wednesday. No cough or chest pain.',
            'examination_findings' => 'Patient febrile (38.8C), moderate dehydration, throat clear.',
            'diagnosis' => 'Suspected Malaria / Acute Febrile Illness',
        ];

        $response = $this->actingAs($this->doctor)
            ->post(route('consultations.draft', $this->encounter), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('consultations.show', $this->encounter));

        $this->encounter->refresh();
        $this->assertEquals(Encounter::STATUS_IN_PROGRESS, $this->encounter->status);

        $currentNote = $this->encounter->currentNote();
        $this->assertNotNull($currentNote);
        $this->assertEquals(1, $currentNote->version);
        $this->assertTrue($currentNote->is_current);
        $this->assertFalse($currentNote->is_finalized);
        $this->assertEquals('Suspected Malaria / Acute Febrile Illness', $currentNote->diagnosis);
    }

    /**
     * AC-16: Doctor completes consultation; clinical notes finalized and encounter status becomes consultation_complete.
     */
    public function test_doctor_completes_consultation_finalizing_notes(): void
    {
        $payload = [
            'presenting_complaints' => 'Fever, joint pain, nausea',
            'diagnosis' => 'Confirmed Malaria (Plasmodium falciparum)',
            'diagnosis_codes' => ['B50.9'],
            'treatment_plan' => 'Initiate Artemether/Lumefantrine immediately. Oral Paracetamol for fever.',
            'follow_up_instructions' => 'Return after 3 days if symptoms persist',
        ];

        $response = $this->actingAs($this->doctor)
            ->post(route('consultations.complete', $this->encounter), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('consultations.index'));

        $this->encounter->refresh();
        $this->assertEquals(Encounter::STATUS_CONSULTATION_COMPLETE, $this->encounter->status);
        $this->assertNotNull($this->encounter->completed_at);

        $note = $this->encounter->currentNote();
        $this->assertTrue($note->is_finalized);
        $this->assertNotNull($note->finalized_at);
        $this->assertEquals('Confirmed Malaria (Plasmodium falciparum)', $note->diagnosis);
    }

    /**
     * AC-17: Completed clinical notes cannot be silently overwritten; amendments preserve original note.
     */
    public function test_amendment_of_finalized_notes_creates_version_2_preserving_original(): void
    {
        // 1. First finalize Version 1
        $initialPayload = [
            'presenting_complaints' => 'Initial complaint',
            'diagnosis' => 'Initial Diagnosis (Acute Gastritis)',
            'treatment_plan' => 'Antacids and Omeprazole',
        ];

        $this->actingAs($this->doctor)
            ->post(route('consultations.complete', $this->encounter), $initialPayload);

        $v1Note = $this->encounter->currentNote();
        $this->assertEquals(1, $v1Note->version);
        $this->assertTrue($v1Note->is_finalized);

        // 2. Submit an amendment (Version 2)
        $amendmentPayload = [
            'amendment_reason' => 'Secondary lab results confirmed Helicobacter pylori infection',
            'presenting_complaints' => 'Initial complaint + epigastric burning',
            'diagnosis' => 'H. Pylori Peptic Ulcer Disease',
            'treatment_plan' => 'Triple therapy: Amoxicillin + Clarithromycin + Omeprazole for 14 days',
            'follow_up_instructions' => 'Review in 2 weeks',
        ];

        $response = $this->actingAs($this->doctor)
            ->post(route('consultations.amend', $this->encounter), $amendmentPayload);

        $response->assertSessionHasNoErrors();

        // 3. Verify Original Version 1 was preserved (NOT overwritten)
        $v1Note->refresh();
        $this->assertEquals(1, $v1Note->version);
        $this->assertFalse($v1Note->is_current); // no longer current
        $this->assertEquals('Initial Diagnosis (Acute Gastritis)', $v1Note->diagnosis);

        // 4. Verify Version 2 was created and is active
        $v2Note = $this->encounter->fresh()->currentNote();
        $this->assertNotNull($v2Note);
        $this->assertEquals(2, $v2Note->version);
        $this->assertTrue($v2Note->is_current);
        $this->assertTrue($v2Note->is_finalized);
        $this->assertEquals('H. Pylori Peptic Ulcer Disease', $v2Note->diagnosis);
        $this->assertEquals('Secondary lab results confirmed Helicobacter pylori infection', $v2Note->amendment_reason);
        $this->assertEquals($this->doctor->id, $v2Note->amended_by);
    }

    /**
     * AC-18: Prescription can be created with all required fields.
     */
    public function test_doctor_can_prescribe_medications(): void
    {
        $payload = [
            'notes' => 'Take with plenty of water',
            'items' => [
                [
                    'stock_item_id' => $this->stockMedication->id,
                    'drug_name' => $this->stockMedication->name,
                    'dose' => '500mg',
                    'route' => 'oral',
                    'frequency' => 'TDS',
                    'duration' => '5 days',
                    'quantity_prescribed' => 15,
                    'instructions' => 'Take 1 tablet three times daily after food',
                ],
            ],
        ];

        $response = $this->actingAs($this->doctor)
            ->post(route('prescriptions.store', $this->encounter), $payload);

        $response->assertSessionHasNoErrors();

        $prescription = Prescription::where('encounter_id', $this->encounter->id)->first();
        $this->assertNotNull($prescription);
        $this->assertEquals($this->doctor->id, $prescription->prescribed_by);
        $this->assertEquals('pending', $prescription->status);

        $item = $prescription->items()->first();
        $this->assertNotNull($item);
        $this->assertEquals($this->stockMedication->name, $item->drug_name);
        $this->assertEquals('500mg', $item->dose);
        $this->assertEquals('TDS', $item->frequency);
        $this->assertEquals(15, $item->quantity_prescribed);
    }

    /**
     * Test doctor can create outgoing referral.
     */
    public function test_doctor_can_create_referral(): void
    {
        $payload = [
            'destination' => 'Nyeri County Referral Hospital (ENT Clinic)',
            'urgency' => 'urgent',
            'reason' => 'Chronic suppurative otitis media unresponsive to oral antibiotics',
            'clinical_summary' => 'Patient has right ear discharge for 3 weeks with reduced hearing acuity.',
            'follow_up_required' => true,
        ];

        $response = $this->actingAs($this->doctor)
            ->post(route('referrals.store', $this->encounter), $payload);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('referrals', [
            'encounter_id' => $this->encounter->id,
            'destination' => 'Nyeri County Referral Hospital (ENT Clinic)',
            'urgency' => 'urgent',
            'referred_by' => $this->doctor->id,
        ]);
    }
}
