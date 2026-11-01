<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Encounter\Encounter;
use App\Models\Inventory\StockItem;
use App\Models\Patient\Patient;
use App\Services\Audit\AuditService;
use App\Services\Encounter\EncounterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // ── List ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $invoices = Invoice::with(['patient', 'encounter'])
            ->where('branch_id', $branchId)
            ->when($request->get('status'), fn($q, $s) => $q->where('status', $s))
            ->when(
                $request->get('date'),
                fn($q, $d) => $q->whereDate('created_at', $d),
                fn($q)     => $q->whereDate('created_at', today())
            )
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('billing.index', compact('invoices'));
    }

    // ── Encounter-linked invoice ──────────────────────────────────────────────

    public function createFromEncounter(Encounter $encounter)
    {
        $this->authorizeBranchAccess($encounter->branch_id);

        // Redirect if a live invoice already exists for this encounter
        $existing = Invoice::where('encounter_id', $encounter->id)
            ->whereNotIn('status', ['reversed'])
            ->first();

        if ($existing) {
            return redirect()->route('billing.show', $existing);
        }

        $branchId   = auth()->user()->branch_id;
        $stockItems = StockItem::where('is_active', true)
            ->whereHas('branchStock', fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        return view('billing.create-encounter', compact('encounter', 'stockItems'));
    }

    // ── POS / Walk-in ─────────────────────────────────────────────────────────

    public function pos()
    {
        $branchId = auth()->user()->branch_id;

        $stockItems = StockItem::with([
                'branchStock' => fn($q) => $q->where('branch_id', $branchId),
            ])
            ->where('is_active', true)
            ->whereHas('branchStock', fn($q) =>
                $q->where('branch_id', $branchId)
                  ->where('quantity_on_hand', '>', 0)
            )
            ->orderBy('name')
            ->get();

        $patients = Patient::whereHas('branchRecords', fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('last_name')
            ->get();

        return view('billing.pos', compact('stockItems', 'patients'));
    }

    // ── Store (shared by POS + encounter billing) ─────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'               => ['required', 'exists:patients,id'],
            'encounter_id'             => ['nullable', 'exists:encounters,id'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.description'      => ['required', 'string', 'max:500'],
            'items.*.item_type'        => ['required', 'in:consultation,drug,optical,procedure,service'],
            'items.*.stock_item_id'    => ['nullable', 'exists:stock_items,id'],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'items.*.unit_price'       => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $invoice = DB::transaction(function () use ($data) {
            $user     = auth()->user();
            $branchId = $user->branch_id;
            $branch   = $user->branch;

            // ── Invoice number ─────────────────────────────────────────────
            // pg_try_advisory_xact_lock takes a bigint key scoped to this
            // transaction. We hash branch+date so concurrent requests for
            // different branches never block each other, but two requests for
            // the same branch on the same day queue up safely.
            $lockKey = crc32('invoice_seq_' . $branchId . '_' . now()->format('Ymd'));
            DB::statement("SELECT pg_advisory_xact_lock({$lockKey})");

            $count  = Invoice::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->count();

            $number = sprintf(
                '%s-INV-%s-%s',
                $branch->code,
                now()->format('Ymd'),
                str_pad($count + 1, 4, '0', STR_PAD_LEFT)
            );

            // ── Build line items ───────────────────────────────────────────
            $subtotal  = 0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $disc      = (float) ($item['discount_percent'] ?? 0);
                $qty       = (int)   $item['quantity'];
                $price     = (float) $item['unit_price'];
                $lineTotal = round($price * $qty * (1 - $disc / 100), 2);
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'description'      => $item['description'],
                    'item_type'        => $item['item_type'],
                    'stock_item_id'    => $item['stock_item_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'line_total'       => $lineTotal,
                    'insurance_covers' => 0,
                    'patient_pays'     => $lineTotal,
                ];
            }

            // ── Create invoice ─────────────────────────────────────────────
            $invoice = Invoice::create([
                'invoice_number' => $number,
                'branch_id'      => $branchId,
                'encounter_id'   => $data['encounter_id'] ?? null,
                'patient_id'     => $data['patient_id'],
                'status'         => 'issued',
                'subtotal'       => $subtotal,
                'total_amount'   => $subtotal,
                'balance_due'    => $subtotal,
                'patient_amount' => $subtotal,
                'created_by'     => $user->id,
                'issued_at'      => now(),
            ]);

            // ── Create line items ──────────────────────────────────────────
            $invoice->items()->createMany($lineItems);

            // ── Advance encounter status if linked ─────────────────────────
            if (!empty($data['encounter_id'])) {
                $encounter = Encounter::find($data['encounter_id']);

                if ($encounter && $encounter->status === 'consultation_complete') {
                    EncounterService::transition($encounter, 'billed', $user);
                }
            }

            AuditService::log('invoice.created', $invoice, [], [
                'number' => $invoice->invoice_number,
                'total'  => $subtotal,
            ]);

            return $invoice;
        });

        return redirect()
            ->route('billing.show', $invoice)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Invoice created',
                'message' => $invoice->invoice_number,
            ]);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Invoice $invoice)
    {
        $this->authorizeBranchAccess($invoice->branch_id);

        $invoice->load(['items.stockItem', 'payments', 'patient', 'encounter']);

        AuditService::log('invoice.viewed', $invoice);

        return view('billing.show', compact('invoice'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Abort 403 if the authenticated user doesn't belong to the given branch,
     * unless they're a super-admin.
     */
    private function authorizeBranchAccess(int $branchId): void
    {
        if (!auth()->user()->isSuperAdmin() && auth()->user()->branch_id !== $branchId) {
            abort(403);
        }
    }
}