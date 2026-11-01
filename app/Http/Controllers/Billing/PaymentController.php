<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\PaymentStatusLog;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['invoice', 'patient', 'receivedBy'])
            ->where('branch_id', auth()->user()->branch_id)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('billing.payments', compact('payments'));
    }

    public function show(Payment $payment)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($payment->branch_id !== auth()->user()->branch_id) abort(403);
        }

        $payment->load(['invoice.items', 'invoice.patient', 'patient', 'branch', 'receivedBy', 'statusLogs']);

        return view('billing.payment', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($payment->branch_id !== auth()->user()->branch_id) abort(403);
        }

        $payment->load(['invoice.items', 'invoice.patient', 'patient', 'branch', 'receivedBy']);

        return view('billing.receipt', compact('payment'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        if (!auth()->user()->isSuperAdmin()) {
            if ($invoice->branch_id !== auth()->user()->branch_id) abort(403);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,mpesa,card,insurance,waiver'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'notes'          => ['nullable', 'string'],
        ]);

        if ($data['amount'] > $invoice->balance_due) {
            return back()->withErrors([
                'amount' => 'Amount exceeds balance due of KSh ' . number_format($invoice->balance_due, 2),
            ]);
        }

        $payment = null;

        DB::transaction(function () use ($data, $invoice, &$payment) {
            $reference      = auth()->user()->branch->code . '-PAY-' . strtoupper(Str::random(8));
            $idempotencyKey = Str::uuid()->toString();

            $payment = Payment::create([
                'payment_reference' => $reference,
                'branch_id'         => $invoice->branch_id,
                'invoice_id'        => $invoice->id,
                'patient_id'        => $invoice->patient_id,
                'payment_method'    => $data['payment_method'],
                'amount'            => $data['amount'],
                'status'            => 'confirmed',
                'idempotency_key'   => $idempotencyKey,
                'notes'             => $data['notes'] ?? null,
                'received_by'       => auth()->id(),
                'confirmed_at'      => now(),
            ]);

            PaymentStatusLog::create([
                'payment_id'  => $payment->id,
                'from_status' => null,
                'to_status'   => 'confirmed',
                'actor'       => auth()->user()->name,
                'changed_at'  => now(),
            ]);

            $newPaid    = $invoice->amount_paid + $data['amount'];
            $newBalance = $invoice->total_amount - $newPaid;
            $newStatus  = $newBalance <= 0 ? 'paid' : 'partially_paid';

            $invoice->update([
                'amount_paid' => $newPaid,
                'balance_due' => max(0, $newBalance),
                'status'      => $newStatus,
                'paid_at'     => $newStatus === 'paid' ? now() : null,
            ]);

            if ($newStatus === 'paid' && $invoice->encounter_id) {
                $encounter = $invoice->encounter;
                if ($encounter && $encounter->status === 'billed') {
                    \App\Services\Encounter\EncounterService::transition($encounter, 'closed', auth()->user());
                }
            }

            AuditService::log('payment.confirmed', $payment, [], [
                'amount'  => $data['amount'],
                'method'  => $data['payment_method'],
                'invoice' => $invoice->invoice_number,
            ]);
        });

        return redirect()->route('payments.receipt', $payment)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Payment recorded',
                'message' => 'KSh ' . number_format($data['amount'], 2) . ' received.',
            ]);
    }

    public function destroy(Request $request, Payment $payment)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isBranchAdmin()) {
            abort(403);
        }

        if (!auth()->user()->isSuperAdmin() && $payment->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

        if ($payment->status === 'reversed') {
            return back()->withErrors(['payment' => 'This payment has already been reversed.']);
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        DB::transaction(function () use ($request, $payment) {
            $invoice = $payment->invoice;

            if ($invoice) {
                $newPaid    = max(0, $invoice->amount_paid - $payment->amount);
                $newBalance = $invoice->total_amount - $newPaid;
                $newStatus  = $newPaid <= 0 ? 'issued' : 'partially_paid';

                $invoice->update([
                    'amount_paid' => $newPaid,
                    'balance_due' => $newBalance,
                    'status'      => $newStatus,
                    'paid_at'     => null,
                ]);
            }

            $payment->update(['status' => 'reversed']);

            PaymentStatusLog::create([
                'payment_id'  => $payment->id,
                'from_status' => 'confirmed',
                'to_status'   => 'reversed',
                'actor'       => auth()->user()->name,
                'changed_at'  => now(),
            ]);

            AuditService::log('payment.reversed', $payment, [], [
                'amount' => $payment->amount,
                'reason' => $request->reason,
            ]);
        });

        return redirect()->route('billing.show', $payment->invoice_id)
            ->with('toast', [
                'type'    => 'warning',
                'title'   => 'Payment reversed',
                'message' => 'KSh ' . number_format($payment->amount, 2) . ' has been reversed.',
            ]);
    }
}