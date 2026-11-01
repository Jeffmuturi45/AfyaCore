<?php
namespace App\Http\Controllers\Encounter;

use App\Http\Controllers\Controller;
use App\Models\Encounter\Encounter;
use App\Models\Patient\Patient;
use App\Services\Encounter\EncounterService;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $query = Encounter::with(['patient', 'assignedTo'])
            ->where('branch_id', $branchId)
            ->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->get('date')) {
            $query->whereDate('visit_date', $date);
        } else {
            $query->whereDate('visit_date', today());
        }

        $encounters = $query->paginate(20)->withQueryString();

        return view('encounters.index', compact('encounters'));
    }

    public function create(Request $request)
    {
        $patient = null;
        if ($request->get('patient_id')) {
            $patient = Patient::findOrFail($request->get('patient_id'));
            if (!auth()->user()->isSuperAdmin()) {
                if (!$patient->branchRecord(auth()->user()->branch_id)) {
                    abort(403, 'Patient not registered at your branch.');
                }
            }
        }

        $patients = Patient::where('is_merged', false)
            ->whereHas('branchRecords', fn($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->orderBy('last_name')
            ->get();

        return view('encounters.create', compact('patient', 'patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'      => ['required', 'exists:patients,id'],
            'chief_complaint' => ['nullable', 'string', 'max:500'],
            'is_referral_in'  => ['nullable', 'boolean'],
            'referral_source' => ['nullable', 'string', 'max:150'],
        ]);

        $encounter = EncounterService::create(
            $request->all(),
            auth()->user()->branch_id
        );

        return redirect()->route('encounters.show', $encounter)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Encounter created',
                'message' => $encounter->encounter_number,
            ]);
    }

    public function show(Encounter $encounter)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($encounter->branch_id !== auth()->user()->branch_id) abort(403);
        }

        $encounter->load([
            'patient', 'assignedTo', 'createdBy',
            'statusLog.changedBy', 'triageRecord',
            'clinicalNotes' => fn($q) => $q->where('is_current', true),
            'eyeClinicEncounter.visualAcuity',
            'eyeClinicEncounter.refractionRecords',
            'eyeClinicEncounter.opticalPrescriptions',
        ]);

        $branchRecord = $encounter->patient->branchRecord($encounter->branch_id);

        AuditService::log('encounter.viewed', $encounter);

        return view('encounters.show', compact('encounter', 'branchRecord'));
    }
}