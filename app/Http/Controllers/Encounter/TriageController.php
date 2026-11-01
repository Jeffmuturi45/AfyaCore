<?php
namespace App\Http\Controllers\Encounter;

use App\Http\Controllers\Controller;
use App\Models\Encounter\Encounter;
use App\Models\Encounter\TriageRecord;
use App\Models\User;
use App\Services\Encounter\EncounterService;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TriageController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        // Patients waiting for triage
        $waiting = Encounter::with('patient')
            ->where('branch_id', $branchId)
            ->where('status', 'registered')
            ->whereDate('visit_date', today())
            ->orderBy('arrival_time')
            ->get();

        // Available doctors at this branch with queue counts
        $doctors = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereIn('role', ['branch_admin', 'optometrist'])
            ->withCount(['encounters as queue_count' => function ($q) {
                $q->whereIn('status', ['in_queue', 'in_progress'])
                  ->whereDate('visit_date', today());
            }])
            ->orderBy('name')
            ->get();

        return view('triage.index', compact('waiting', 'doctors'));
    }

    public function create(Encounter $encounter)
    {
        $this->authorizeNurse($encounter);

        $branchId     = auth()->user()->branch_id;
        $branchRecord = $encounter->patient->branchRecord($branchId);

        // Available doctors
        $doctors = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereIn('role', ['branch_admin', 'optometrist'])
            ->withCount(['encounters as queue_count' => function ($q) {
                $q->whereIn('status', ['in_queue', 'in_progress'])
                  ->whereDate('visit_date', today());
            }])
            ->orderBy('queue_count')
            ->get();

        return view('triage.create', compact('encounter', 'branchRecord', 'doctors'));
    }

    public function store(Request $request, Encounter $encounter)
    {
        $this->authorizeNurse($encounter);

        $data = $request->validate([
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'bp_systolic'   => ['nullable', 'integer', 'min:50', 'max:300'],
            'bp_diastolic'  => ['nullable', 'integer', 'min:30', 'max:200'],
            'pulse_bpm'     => ['nullable', 'integer', 'min:20', 'max:300'],
            'spo2_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'weight_kg'     => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'height_cm'     => ['nullable', 'numeric', 'min:20', 'max:250'],
            'muac_cm'       => ['nullable', 'numeric'],
            'notes'         => ['nullable', 'string'],
            'assigned_to'   => ['required', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($data, $encounter) {
            // BMI
            if (!empty($data['weight_kg']) && !empty($data['height_cm'])) {
                $h = $data['height_cm'] / 100;
                $data['bmi'] = round($data['weight_kg'] / ($h * $h), 1);
            }

            // For eye clinic — no triage vitals needed, just assign
            if ($encounter->encounter_type === 'opd') {
                TriageRecord::create([
                    'encounter_id'  => $encounter->id,
                    'branch_id'     => $encounter->branch_id,
                    'temperature_c' => $data['temperature_c'] ?? null,
                    'bp_systolic'   => $data['bp_systolic'] ?? null,
                    'bp_diastolic'  => $data['bp_diastolic'] ?? null,
                    'pulse_bpm'     => $data['pulse_bpm'] ?? null,
                    'spo2_percent'  => $data['spo2_percent'] ?? null,
                    'weight_kg'     => $data['weight_kg'] ?? null,
                    'height_cm'     => $data['height_cm'] ?? null,
                    'bmi'           => $data['bmi'] ?? null,
                    'muac_cm'       => $data['muac_cm'] ?? null,
                    'recorded_by'   => auth()->id(),
                    'recorded_at'   => now(),
                    'notes'         => $data['notes'] ?? null,
                ]);
                EncounterService::transition($encounter, 'triaged', auth()->user());
            }

            // Assign doctor and move to queue
            $encounter->update(['assigned_to' => $data['assigned_to']]);
            EncounterService::transition($encounter, 'in_queue', auth()->user());

            AuditService::log('triage.recorded_and_assigned', $encounter, [], [
                'assigned_to' => $data['assigned_to'],
            ]);
        });

        return redirect()->route('triage.index')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Patient triaged and assigned',
                'message' => $encounter->patient->full_name . ' added to queue.',
            ]);
    }

    private function authorizeNurse(Encounter $encounter): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($encounter->branch_id !== auth()->user()->branch_id) abort(403);
            if (!in_array(auth()->user()->role, ['nurse', 'branch_admin', 'super_admin'])) abort(403);
        }
    }
}