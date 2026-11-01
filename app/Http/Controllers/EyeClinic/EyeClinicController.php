<?php
namespace App\Http\Controllers\EyeClinic;

use App\Http\Controllers\Controller;
use App\Models\Encounter\Encounter;
use App\Models\EyeClinic\EyeClinicEncounter;
use App\Models\EyeClinic\VisualAcuityRecord;
use App\Models\EyeClinic\RefractionRecord;
use App\Models\EyeClinic\OpticalPrescription;
use App\Services\Audit\AuditService;
use App\Services\Encounter\EncounterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EyeClinicController extends Controller
{
    public function index()
    {
        $branchId   = auth()->user()->branch_id;
        $encounters = Encounter::with('patient')
            ->where('branch_id', $branchId)
            ->where('encounter_type', 'eye_clinic')
            ->whereNotIn('status', ['closed', 'void'])
            ->whereDate('visit_date', today())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('eye-clinic.index', compact('encounters'));
    }

    public function edit(Encounter $encounter)
    {
        $this->authorizeEncounter($encounter);

        $eyeEnc = $encounter->eyeClinicEncounter;
        $encounter->load(['patient', 'statusLog.changedBy']);
        $branchRecord = $encounter->patient->branchRecord($encounter->branch_id);

        return view('eye-clinic.notes', compact('encounter', 'eyeEnc', 'branchRecord'));
    }

    public function update(Request $request, Encounter $encounter)
{
    $this->authorizeEncounter($encounter);

    $data = $request->validate([
        'chief_complaint'      => ['nullable', 'string'],
        'history'              => ['nullable', 'string'],
        'general_health_notes' => ['nullable', 'string'],
        'va'                   => ['nullable', 'array'],
        'va.*.eye'             => ['required_with:va', 'in:RE,LE,BE'],
        'va.*.unaided_distance'=> ['nullable', 'string', 'max:20'],
        'va.*.unaided_near'    => ['nullable', 'string', 'max:20'],
        'va.*.aided_distance'  => ['nullable', 'string', 'max:20'],
        'va.*.aided_near'      => ['nullable', 'string', 'max:20'],
        'va.*.pinhole'         => ['nullable', 'string', 'max:20'],
        'rx'                   => ['nullable', 'array'],
        'rx.*.eye'             => ['required_with:rx', 'in:RE,LE'],
        'rx.*.sphere'          => ['nullable', 'numeric'],
        'rx.*.cylinder'        => ['nullable', 'numeric'],
        'rx.*.axis'            => ['nullable', 'integer', 'min:0', 'max:180'],
        'rx.*.addition'        => ['nullable', 'numeric'],
        'prescription_type'    => ['nullable', 'in:distance,near,bifocal,progressive,safety'],
        're_sphere'            => ['nullable', 'numeric'],
        're_cylinder'          => ['nullable', 'numeric'],
        're_axis'              => ['nullable', 'integer'],
        're_addition'          => ['nullable', 'numeric'],
        'le_sphere'            => ['nullable', 'numeric'],
        'le_cylinder'          => ['nullable', 'numeric'],
        'le_axis'              => ['nullable', 'integer'],
        'le_addition'          => ['nullable', 'numeric'],
        'pd_distance'          => ['nullable', 'numeric'],
        'pd_near'              => ['nullable', 'numeric'],
        'frame_recommendation' => ['nullable', 'string'],
        'lens_recommendation'  => ['nullable', 'string'],
        'prescription_notes'   => ['nullable', 'string'],
        'finalize'             => ['nullable', 'boolean'],
    ]);

    DB::transaction(function () use ($data, $encounter, $request) {

        // Upsert and FRESH to get real id
        EyeClinicEncounter::updateOrCreate(
            ['encounter_id' => $encounter->id],
            [
                'branch_id'            => $encounter->branch_id,
                'optometrist_id'       => auth()->id(),
                'chief_complaint'      => $data['chief_complaint'] ?? null,
                'history'              => $data['history'] ?? null,
                'general_health_notes' => $data['general_health_notes'] ?? null,
            ]
        );

        // Re-fetch fresh with real id
        $eyeEnc = EyeClinicEncounter::where('encounter_id', $encounter->id)->firstOrFail();

        // Visual acuity
        if (!empty($data['va'])) {
            VisualAcuityRecord::where('eye_encounter_id', $eyeEnc->id)->delete();
            foreach ($data['va'] as $va) {
                if (empty($va['eye'])) continue;
                VisualAcuityRecord::create([
                    'eye_encounter_id' => $eyeEnc->id,
                    'eye'              => $va['eye'],
                    'unaided_distance' => $va['unaided_distance'] ?? null,
                    'unaided_near'     => $va['unaided_near'] ?? null,
                    'aided_distance'   => $va['aided_distance'] ?? null,
                    'aided_near'       => $va['aided_near'] ?? null,
                    'pinhole'          => $va['pinhole'] ?? null,
                    'recorded_by'      => auth()->id(),
                    'recorded_at'      => now(),
                ]);
            }
        }

        // Refraction
        if (!empty($data['rx'])) {
            RefractionRecord::where('eye_encounter_id', $eyeEnc->id)->delete();
            foreach ($data['rx'] as $rx) {
                if (empty($rx['eye'])) continue;
                RefractionRecord::create([
                    'eye_encounter_id' => $eyeEnc->id,
                    'eye'              => $rx['eye'],
                    'sphere'           => $rx['sphere'] ?? null,
                    'cylinder'         => $rx['cylinder'] ?? null,
                    'axis'             => $rx['axis'] ?? null,
                    'addition'         => $rx['addition'] ?? null,
                    'recorded_by'      => auth()->id(),
                    'recorded_at'      => now(),
                ]);
            }
        }

        // Optical prescription
        if (!empty($data['prescription_type'])) {
            OpticalPrescription::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'eye_encounter_id'     => $eyeEnc->id,
                    'prescribed_by'        => auth()->id(),
                    'prescription_type'    => $data['prescription_type'],
                    're_sphere'            => $data['re_sphere'] ?? null,
                    're_cylinder'          => $data['re_cylinder'] ?? null,
                    're_axis'              => $data['re_axis'] ?? null,
                    're_addition'          => $data['re_addition'] ?? null,
                    'le_sphere'            => $data['le_sphere'] ?? null,
                    'le_cylinder'          => $data['le_cylinder'] ?? null,
                    'le_axis'              => $data['le_axis'] ?? null,
                    'le_addition'          => $data['le_addition'] ?? null,
                    'pd_distance'          => $data['pd_distance'] ?? null,
                    'pd_near'              => $data['pd_near'] ?? null,
                    'frame_recommendation' => $data['frame_recommendation'] ?? null,
                    'lens_recommendation'  => $data['lens_recommendation'] ?? null,
                    'notes'                => $data['prescription_notes'] ?? null,
                ]
            );
        }

        // Finalize
        if ($request->boolean('finalize')) {
            $eyeEnc->update([
                'is_finalized' => true,
                'finalized_at' => now(),
            ]);
            EncounterService::transition($encounter, 'consultation_complete', auth()->user());
        }

        AuditService::log('eye_clinic.notes_saved', $encounter);
    });

    $msg = $request->boolean('finalize') ? 'Notes finalized.' : 'Notes saved.';

    return redirect()->route('encounters.show', $encounter)
        ->with('toast', ['type' => 'success', 'title' => $msg]);
}

    private function authorizeEncounter(Encounter $encounter): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($encounter->branch_id !== auth()->user()->branch_id) abort(403);
        }
        if ($encounter->encounter_type !== 'eye_clinic') abort(404);
    }
}