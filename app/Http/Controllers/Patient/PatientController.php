<?php
namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientBranchRecord;
use App\Services\Audit\AuditService;
use App\Services\Patient\DuplicateDetectionService;
use App\Services\Patient\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->isSuperAdmin()
            ? null
            : auth()->user()->branch_id;

        $query = Patient::with(['branchRecords' => function ($q) use ($branchId) {
                if ($branchId) $q->where('branch_id', $branchId);
            }])
            ->where('is_merged', false);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhere('national_id', 'LIKE', "%{$search}%")
                  ->orWhere('phone_primary', 'LIKE', "%{$search}%")
                  ->orWhere('global_patient_id', 'LIKE', "%{$search}%");
            });
        }

        // Branch scope — non-super-admins see only their branch patients
        if ($branchId) {
            $query->whereHas('branchRecords', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $patients = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        AuditService::log('patient.list_viewed');

        return view('patients.index', compact('patients'));
    }

    public function create(Request $request)
    {
        return view('patients.create');
    }

    public function store(StorePatientRequest $request)
    {
        $data     = $request->validated();
        $user     = auth()->user();
        $branch   = $user->branch;

        // Duplicate check — unless staff confirmed
        if (!$request->boolean('confirm_duplicate')) {
            $duplicates = DuplicateDetectionService::findPotentialDuplicates($data);
            if ($duplicates->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('duplicates', $duplicates)
                    ->with('toast', [
                        'type'    => 'warning',
                        'title'   => 'Possible duplicates found',
                        'message' => 'Review the existing records below before registering.',
                    ]);
            }
        }

        $patient = PatientService::register($data, $branch->id, $branch->code);

        return redirect()->route('patients.show', $patient)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Patient registered',
                'message' => "MRN: {$patient->mrn($branch->id)}",
            ]);
    }

    public function show(Patient $patient)
    {
        $branchId = auth()->user()->branch_id;

        // Branch scope check
        if (!auth()->user()->isSuperAdmin()) {
            $record = $patient->branchRecord($branchId);
            if (!$record) abort(403, 'This patient record does not belong to your branch.');
        }

        $branchRecord = $patient->branchRecord($branchId);
        $encounters   = \App\Models\Encounter\Encounter::where('patient_id', $patient->id)
            ->where('branch_id', $branchId)
            ->orderByDesc('visit_date')
            ->limit(10)
            ->get();

        AuditService::log('patient.viewed', $patient);

        return view('patients.show', compact('patient', 'branchRecord', 'encounters'));
    }

    public function edit(Patient $patient)
    {
        if (!auth()->user()->isSuperAdmin()) {
            $record = $patient->branchRecord(auth()->user()->branch_id);
            if (!$record) abort(403);
        }
        return view('patients.edit', compact('patient'));
    }

    public function update(StorePatientRequest $request, Patient $patient)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if (!$patient->branchRecord(auth()->user()->branch_id)) abort(403);
        }

        $old = $patient->only([
            'first_name','last_name','phone_primary','national_id',
        ]);

        $patient->update($request->validated());

        AuditService::log('patient.updated', $patient, $old, $request->validated());

        return redirect()->route('patients.show', $patient)
            ->with('toast', ['type' => 'success', 'title' => 'Patient updated']);
    }
}