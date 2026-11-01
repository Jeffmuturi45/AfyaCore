<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.settings', compact('branches'));
    }

    public function updatePaystack(Request $request)
    {
        $data = $request->validate([
            'paystack_public_key'  => ['required', 'string'],
            'paystack_secret_key'  => ['required', 'string'],
            'paystack_webhook_url' => ['nullable', 'url'],
        ]);

        // Store in .env-style config — use encrypted settings table or config
        \DB::table('branches')->where('is_active', true)->each(function ($branch) use ($data) {
            \DB::table('branches')->where('id', $branch->id)->update([
                'settings' => json_encode(array_merge(
                    json_decode($branch->settings ?? '{}', true),
                    [
                        'paystack_public_key'  => $data['paystack_public_key'],
                        'paystack_secret_key'  => encrypt($data['paystack_secret_key']),
                        'paystack_webhook_url' => $data['paystack_webhook_url'] ?? '',
                    ]
                )),
            ]);
        });

        AuditService::log('settings.paystack_updated');

        return redirect()->route('admin.settings')
            ->with('toast', ['type' => 'success', 'title' => 'Paystack settings saved']);
    }
}