<?php
namespace App\Http\Controllers\Encounter;

use App\Http\Controllers\Controller;
use App\Models\Encounter\Encounter;
use App\Models\Encounter\ClinicalNote;
use App\Models\Inventory\StockItem;
use App\Services\Encounter\EncounterService;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function index()
    {
        $branchId   = auth()->user()->branch_id;
        $encounters = Encounter::with('patient')
            ->where('branch_id', $branchId)
            ->where('encounter_type', 'opd')
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['in_queue', 'in_progress'])
            ->whereDate('visit_date', today())
            ->orderBy('arrival_time')
            ->paginate(20);

        return view('consultations.index', compact('encounters'));
    }

    public function edit(Encounter $encounter)
    {
        $this->authorize($encounter);

        if ($encounter->status === 'in_queue') {
            EncounterService::transition($encounter, 'in_progress', auth()->user());
        }

        $encounter->load([
            'patient', 'triageRecord', 'statusLog.changedBy',
            'clinicalNotes' => fn($q) => $q->where('is_current', true),
        ]);

        $note         = $encounter->currentNote();
        $branchRecord = $encounter->patient->branchRecord($encounter->branch_id);

        // Stock for prescription tab
        $stockItems = StockItem::with(['branchStock' => fn($q) => $q->where('branch_id', $encounter->branch_id)])
            ->where('is_active', true)
            ->whereHas('branchStock', fn($q) => $q->where('branch_id', $encounter->branch_id))
            ->whereHas('category', fn($q) => $q->where('type', 'drug'))
            ->orderBy('name')
            ->get();

        AuditService::log('consultation.opened', $encounter);

        return view('consultations.edit', compact('encounter', 'note', 'branchRecord', 'stockItems'));
    }

    public function update(Request $request, Encounter $encounter)
    {
        $this->authorize($encounter);

        $data = $request->validate([
            'section'                => ['required', 'string'],
            // Patient notes
            'presenting_complaints'  => ['nullable', 'string'],
            'signs'                  => ['nullable', 'string'],
            'history_of_illness'     => ['nullable', 'string'],
            'past_medical_history'   => ['nullable', 'string'],
            'past_surgical_history'  => ['nullable', 'string'],
            'family_history'         => ['nullable', 'string'],
            'social_history'         => ['nullable', 'string'],
            'drug_history'           => ['nullable', 'string'],
            'examination_findings'   => ['nullable', 'string'],
            'diagnosis'              => ['nullable', 'string'],
            'diagnosis_codes'        => ['nullable', 'string'],
            'treatment_plan'         => ['nullable', 'string'],
            'follow_up_instructions' => ['nullable', 'string'],
            'finalize'               => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $encounter, $request) {
            $existing = $encounter->currentNote();
            if ($existing && $existing->is_finalized) {
                abort(403, 'Finalized notes cannot be overwritten.');
            }

            $noteData = collect($data)->except(['section', 'finalize'])->toArray();

            if ($existing) {
                $existing->update(array_merge($noteData, ['authored_by' => auth()->id()]));
                $note = $existing;
            } else {
                $note = ClinicalNote::create(array_merge($noteData, [
                    'encounter_id' => $encounter->id,
                    'version'      => 1,
                    'is_current'   => true,
                    'authored_by'  => auth()->id(),
                ]));
            }

            if ($request->boolean('finalize')) {
                $note->update(['is_finalized' => true, 'finalized_at' => now()]);
                EncounterService::transition($encounter, 'consultation_complete', auth()->user());
                AuditService::log('note.finalized', $encounter);
            } else {
                AuditService::log('note.saved', $encounter);
            }
        });

        if ($request->boolean('finalize')) {
            return redirect()->route('billing.create-encounter', $encounter)
                ->with('toast', ['type' => 'success', 'title' => 'Consultation complete', 'message' => 'Proceed to billing.']);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Notes saved', 'message' => ucfirst($data['section']) . ' section saved.']);
    }

    private function authorize(Encounter $encounter): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($encounter->branch_id !== auth()->user()->branch_id) abort(403);
            if ($encounter->assigned_to !== auth()->id() && !auth()->user()->isBranchAdmin()) {
                abort(403, 'This patient is not assigned to you.');
            }
        }
        if ($encounter->encounter_type !== 'opd') abort(404);
    }

    public function storePrescription(Request $request, Encounter $encounter)
{
    $this->authorize($encounter);

    $data = $request->validate([
        'items'                    => ['required', 'array', 'min:1'],
        'items.*.stock_item_id'    => ['required', 'exists:stock_items,id'],
        'items.*.quantity'         => ['required', 'integer', 'min:1'],
        'items.*.frequency'        => ['required', 'string'],
        'items.*.duration'         => ['nullable', 'string'],
        'items.*.route'            => ['nullable', 'string'],
        'items.*.instructions'     => ['nullable', 'string'],
    ]);

    DB::transaction(function () use ($data, $encounter) {
        $prescription = \App\Models\Pharmacy\Prescription::create([
            'encounter_id'  => $encounter->id,
            'prescribed_by' => auth()->id(),
            'status'        => 'pending',
        ]);

        foreach ($data['items'] as $item) {
            $drug = \App\Models\Inventory\StockItem::find($item['stock_item_id']);
            \App\Models\Pharmacy\PrescriptionItem::create([
                'prescription_id'    => $prescription->id,
                'stock_item_id'      => $item['stock_item_id'],
                'drug_name'          => $drug->name,
                'dose'               => '1 ' . $drug->unit_of_measure,
                'route'              => $item['route'] ?? 'Oral',
                'frequency'          => $item['frequency'],
                'duration'           => $item['duration'] ?? null,
                'quantity_prescribed'=> $item['quantity'],
                'instructions'       => $item['instructions'] ?? null,
                'status'             => 'pending',
            ]);
        }

        AuditService::log('prescription.written', $encounter);
    });

    return back()->with('toast', ['type' => 'success', 'title' => 'Prescription issued']);
}
}