<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchModule;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users')->orderBy('name')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'code'        => ['required', 'string', 'max:10', 'unique:branches,code', 'regex:/^[A-Z0-9]+$/'],
            'branch_type' => ['required', 'in:opd,eye_clinic,both'],
            'address'     => ['nullable', 'string'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'email'       => ['nullable', 'email'],
        ]);

        $branch = Branch::create(array_merge($data, ['is_active' => true]));

        // Auto-assign modules based on type
        $modules = match($data['branch_type']) {
            'opd'       => ['opd', 'pharmacy', 'pos', 'insurance'],
            'eye_clinic'=> ['eye_clinic', 'optical_pos', 'insurance'],
            'both'      => ['opd', 'eye_clinic', 'pharmacy', 'optical_pos', 'pos', 'insurance'],
        };

        foreach ($modules as $module) {
            BranchModule::create([
                'branch_id'    => $branch->id,
                'module'       => $module,
                'is_active'    => true,
                'activated_at' => now(),
                'activated_by' => auth()->id(),
            ]);
        }

        // Create MRN sequence
        \DB::table('mrn_sequences')->insert([
            'branch_id'     => $branch->id,
            'last_sequence' => 0,
            'updated_at'    => now(),
        ]);

        AuditService::log('branch.created', $branch, [], $data);

        return redirect()->route('admin.branches.index')
            ->with('toast', ['type' => 'success', 'title' => 'Branch created', 'message' => $branch->name]);
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'address'   => ['nullable', 'string'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'email'     => ['nullable', 'email'],
            'is_active' => ['boolean'],
        ]);

        $old = $branch->only(['name', 'address', 'phone', 'is_active']);
        $branch->update($data);
        AuditService::log('branch.updated', $branch, $old, $data);

        return redirect()->route('admin.branches.index')
            ->with('toast', ['type' => 'success', 'title' => 'Branch updated']);
    }
}