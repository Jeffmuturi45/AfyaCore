<?php

namespace Tests\Feature\Clinical;

use App\Models\Branch;
use App\Models\Encounter\Encounter;
use App\Models\User;
use App\Services\Patient\PatientService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CrossBranchSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $nyeriOptom;

    protected User $hospitalNurse;

    protected User $superAdmin;

    protected Branch $nyeriBranch;

    protected Branch $hospitalBranch;

    protected Encounter $hospitalEncounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nyeriBranch = Branch::where('code', 'NYR')->first();
        $this->hospitalBranch = Branch::where('code', 'HOS')->first();

        $this->nyeriOptom = User::where('email', 'optometrist.nyr@afyacore.test')->first();
        $this->hospitalNurse = User::where('email', 'nurse.hos@afyacore.test')->first();
        $this->superAdmin = User::where('email', 'admin@afyacore.test')->first();

        $receptionistHos = User::where('email', 'reception.hos@afyacore.test')->first();
        $patientService = app(PatientService::class);
        $patient = $patientService->registerPatient([
            'first_name' => 'Mercy',
            'last_name' => 'Chepkirui',
            'date_of_birth' => '1998-04-12',
            'gender' => 'female',
            'phone_primary' => '0799887766',
        ], $this->hospitalBranch, $receptionistHos);

        $this->hospitalEncounter = Encounter::create([
            'branch_id' => $this->hospitalBranch->id,
            'patient_id' => $patient->id,
            'encounter_type' => 'opd',
            'encounter_number' => 'HOS-20261031-7701',
            'status' => Encounter::STATUS_REGISTERED,
            'created_by' => $receptionistHos->id,
            'visit_date' => now()->toDateString(),
            'arrival_time' => now(),
        ]);
    }

    /**
     * AC-20: Staff from another branch cannot access foreign branch encounters.
     */
    public function test_staff_from_another_branch_cannot_view_foreign_encounter(): void
    {
        // Optometrist from NYR tries to access HOS encounter
        $response = $this->actingAs($this->nyeriOptom)
            ->get(route('encounters.show', $this->hospitalEncounter));

        $response->assertStatus(403);
    }

    /**
     * AC-20: Cross-branch triage attempt is denied.
     */
    public function test_staff_from_another_branch_cannot_triage_foreign_encounter(): void
    {
        $response = $this->actingAs($this->nyeriOptom)
            ->get(route('triage.create', $this->hospitalEncounter));

        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->nyeriOptom)
            ->post(route('triage.store', $this->hospitalEncounter), [
                'temperature_c' => 37.0,
            ]);

        $postResponse->assertStatus(403);
    }

    /**
     * AC-20: Cross-branch consultation attempt is denied.
     */
    public function test_staff_from_another_branch_cannot_consult_foreign_encounter(): void
    {
        $response = $this->actingAs($this->nyeriOptom)
            ->get(route('consultations.show', $this->hospitalEncounter));

        $response->assertStatus(403);

        $draftResponse = $this->actingAs($this->nyeriOptom)
            ->post(route('consultations.draft', $this->hospitalEncounter), [
                'diagnosis' => 'Unauthorized entry',
            ]);

        $draftResponse->assertStatus(403);
    }

    /**
     * AC-20: Super administrator can access encounters across all branches.
     */
    public function test_super_admin_can_access_encounters_across_all_branches(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('encounters.show', $this->hospitalEncounter));

        $response->assertOk();
        $response->assertSee('HOS-20261031-7701');
    }
}
