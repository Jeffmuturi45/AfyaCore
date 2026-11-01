<?php

namespace Tests\Feature\Clinical;

use App\Models\Branch;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $receptionist;

    protected Branch $hospital;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hospital = Branch::where('code', 'HOS')->first();
        $this->receptionist = User::where('email', 'reception.hos@afyacore.test')->first();
    }

    /**
     * AC-11: Receptionist can register a new patient with required fields.
     */
    public function test_receptionist_can_register_new_patient_with_global_id_and_mrn(): void
    {
        $payload = [
            'first_name' => 'Samuel',
            'middle_name' => 'Maina',
            'last_name' => 'Kariuki',
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'national_id' => '28941234',
            'phone_primary' => '0712345678',
            'nationality' => 'Kenyan',
            'county' => 'Nyeri',
            'sub_county' => 'Nyeri Central',
            'ward' => 'Rware',
            'village' => 'Skuta',
            'next_of_kin_name' => 'Mary Kariuki',
            'next_of_kin_phone' => '0722112233',
            'next_of_kin_relation' => 'Spouse',
        ];

        $response = $this->actingAs($this->receptionist)
            ->post(route('patients.store'), $payload);

        $response->assertSessionHasNoErrors();

        // Assert patient was created
        $patient = Patient::where('national_id', '28941234')->first();
        $this->assertNotNull($patient);
        $this->assertEquals('Samuel', $patient->first_name);
        $this->assertEquals('Kariuki', $patient->last_name);
        $this->assertStringStartsWith('P-', $patient->global_patient_id);

        // Assert branch record and MRN
        $branchRecord = $patient->branchRecord($this->hospital->id);
        $this->assertNotNull($branchRecord);
        $this->assertStringStartsWith('HOS', $branchRecord->mrn);
        $this->assertEquals(1, $branchRecord->visit_count);

        // Assert audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'patient.registered',
            'model_type' => Patient::class,
            'model_id' => $patient->id,
            'user_id' => $this->receptionist->id,
        ]);

        $response->assertRedirect(route('patients.show', $patient));
    }

    /**
     * AC-12: Duplicate patient detection triggers on matching phone or national ID.
     */
    public function test_duplicate_detection_triggers_on_matching_national_id_or_phone(): void
    {
        // 1. Register first patient
        $firstPayload = [
            'first_name' => 'John',
            'last_name' => 'Kamau',
            'date_of_birth' => '1985-08-20',
            'gender' => 'male',
            'national_id' => '12345678',
            'phone_primary' => '0700112233',
        ];

        $this->actingAs($this->receptionist)
            ->post(route('patients.store'), $firstPayload);

        $firstPatient = Patient::where('national_id', '12345678')->first();
        $this->assertNotNull($firstPatient);

        // 2. Register second patient with the SAME national ID and phone
        $secondPayload = [
            'first_name' => 'Jonathan',
            'last_name' => 'Kamau',
            'date_of_birth' => '1985-08-20',
            'gender' => 'male',
            'national_id' => '12345678', // matching ID
            'phone_primary' => '0700112233', // matching Phone
        ];

        $response = $this->actingAs($this->receptionist)
            ->post(route('patients.store'), $secondPayload);

        $response->assertSessionHasNoErrors();

        $secondPatient = Patient::where('first_name', 'Jonathan')->first();
        $this->assertNotNull($secondPatient);

        // Assert duplicate flag was created linking patient A and B
        $this->assertDatabaseHas('patient_duplicate_flags', [
            'patient_a_id' => $secondPatient->id,
            'patient_b_id' => $firstPatient->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test patient search by name, MRN, national ID, and phone.
     */
    public function test_can_search_patients_by_name_or_mrn(): void
    {
        $payload = [
            'first_name' => 'Eunice',
            'last_name' => 'Wambui',
            'date_of_birth' => '1992-03-10',
            'gender' => 'female',
            'national_id' => '30456789',
            'phone_primary' => '0711998877',
        ];

        $this->actingAs($this->receptionist)
            ->post(route('patients.store'), $payload);

        $patient = Patient::where('national_id', '30456789')->first();
        $mrn = $patient->mrn($this->hospital->id);

        // Search by name
        $response = $this->actingAs($this->receptionist)
            ->get(route('patients.index', ['search' => 'Eunice']));
        $response->assertOk();
        $response->assertSee('Eunice');
        $response->assertSee($mrn);

        // Search by MRN
        $responseMrn = $this->actingAs($this->receptionist)
            ->get(route('patients.index', ['search' => $mrn]));
        $responseMrn->assertOk();
        $responseMrn->assertSee('Eunice Wambui');
    }

    /**
     * Test AJAX patient search returns matching records for check-in.
     */
    public function test_patient_search_ajax_returns_json_matches_for_checkin(): void
    {
        $payload = [
            'first_name'    => 'Patrick',
            'last_name'     => 'Nderitu',
            'date_of_birth' => '1987-09-05',
            'gender'        => 'male',
            'national_id'   => '25667788',
            'phone_primary' => '0788112233',
        ];

        $this->actingAs($this->receptionist)
            ->post(route('patients.store'), $payload);

        // Search by full name
        $response = $this->actingAs($this->receptionist)
            ->getJson(route('patients.search_ajax', ['q' => 'Patrick Nderitu']));

        $response->assertOk();
        $response->assertJsonStructure(['results' => [['id', 'name', 'mrn', 'phone', 'national_id', 'gender', 'age', 'dob']]]);
        $this->assertNotEmpty($response->json('results'));
        $this->assertEquals('Patrick Nderitu', $response->json('results.0.name'));

        // Search by partial phone
        $responsePhone = $this->actingAs($this->receptionist)
            ->getJson(route('patients.search_ajax', ['q' => '881122']));
        $responsePhone->assertOk();
        $this->assertEquals('Patrick Nderitu', $responsePhone->json('results.0.name'));
    }
}
