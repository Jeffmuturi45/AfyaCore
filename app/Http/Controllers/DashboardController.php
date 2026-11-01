<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Patient\Patient;
use App\Models\Encounter\Encounter;
use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $branchCount     = Branch::count();
            $userCount       = User::count();
            $patientCount    = Patient::count();
            $todayEncounters = Encounter::whereDate('visit_date', today())->count();
            $branches        = Branch::withCount('users')->orderBy('name')->get();

            return view('dashboard.index', compact(
                'branchCount','userCount','patientCount','todayEncounters','branches'
            ));
        }

        $branchId = $user->branch_id;

        $todayPatients   = Encounter::where('branch_id', $branchId)->whereDate('visit_date', today())->distinct('patient_id')->count();
        $openEncounters  = Encounter::where('branch_id', $branchId)->whereNotIn('status', ['closed','void'])->whereDate('visit_date', today())->count();
        $pendingPayments = Invoice::where('branch_id', $branchId)->whereIn('status', ['issued','partially_paid'])->count();
        $waitingTriage   = Encounter::where('branch_id', $branchId)->where('status', 'registered')->whereDate('visit_date', today())->count();

        return view('dashboard.index', compact(
            'todayPatients','openEncounters','pendingPayments','waitingTriage'
        ));
    }
}