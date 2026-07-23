<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(): View
    {
        $passwordPolicy = [
            'min_length' => SystemSetting::getValue('password_min_length', 8),
            'require_special' => SystemSetting::getValue('password_require_special', true, 'boolean'),
            'require_numbers' => SystemSetting::getValue('password_require_numbers', true, 'boolean'),
            'require_uppercase' => SystemSetting::getValue('password_require_uppercase', true, 'boolean'),
            'expiry_days' => SystemSetting::getValue('password_expiry_days', 90),
        ];

        $mfaSettings = [
            'forced' => SystemSetting::getValue('mfa_required', false, 'boolean'),
        ];

        $sessions = DB::table('sessions')
            ->join('administrators', 'sessions.user_id', '=', 'administrators.id')
            ->select('sessions.id', 'sessions.user_id', 'sessions.ip_address', 'sessions.user_agent', 'sessions.last_activity', DB::raw("administrators.first_name || ' ' || administrators.last_name as name"))
            ->orderBy('sessions.last_activity', 'desc')
            ->get()
            ->map(function ($s) {
                $s->last_activity_humans = $s->last_activity ? now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp($s->last_activity)) . ' min ago' : 'N/A';
                $s->user_agent_short = strlen($s->user_agent ?? '') > 80 ? substr($s->user_agent, 0, 80) . '...' : ($s->user_agent ?? 'Unknown');
                return $s;
            });

        $failedLogins = DB::table('failed_login_attempts')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $ipAccess = [
            'whitelist' => SystemSetting::getValue('ip_whitelist', '', 'json'),
            'blacklist' => SystemSetting::getValue('ip_blacklist', '', 'json'),
            'enabled' => SystemSetting::getValue('ip_access_control_enabled', false, 'boolean'),
        ];

        if (is_string($ipAccess['whitelist'])) $ipAccess['whitelist'] = [];
        if (is_string($ipAccess['blacklist'])) $ipAccess['blacklist'] = [];

        return view('admin.security.index', compact('passwordPolicy', 'mfaSettings', 'sessions', 'failedLogins', 'ipAccess'));
    }

    private function persistTab(Request $request): void
    {
        if ($request->has('_tab')) {
            session(['active_tab' => $request->input('_tab')]);
        }
    }

    public function updatePasswordPolicy(Request $request): RedirectResponse
    {
        $this->persistTab($request);

        $validated = $request->validate([
            'min_length' => 'required|integer|min:4|max:100',
            'require_special' => 'boolean',
            'require_numbers' => 'boolean',
            'require_uppercase' => 'boolean',
            'expiry_days' => 'required|integer|min:0|max:365',
        ]);

        foreach ($validated as $key => $value) {
            $type = is_bool($value) ? 'boolean' : 'integer';
            SystemSetting::setValue("password_{$key}", $value, $type, 'security');
        }

        return redirect()->route('admin.security.index')->with('success', 'Password policy updated.');
    }

    public function updateMfa(Request $request): RedirectResponse
    {
        $this->persistTab($request);

        $validated = $request->validate([
            'forced' => 'boolean',
        ]);

        SystemSetting::setValue('mfa_required', $validated['forced'] ?? false, 'boolean', 'security');

        return redirect()->route('admin.security.index')->with('success', 'MFA settings updated.');
    }

    public function terminateSession(string $sessionId): RedirectResponse
    {
        DB::table('sessions')->where('id', $sessionId)->delete();

        return redirect()->route('admin.security.index')->with('success', 'Session terminated.');
    }

    public function updateIpAccess(Request $request): RedirectResponse
    {
        $this->persistTab($request);

        $validated = $request->validate([
            'enabled' => 'boolean',
            'whitelist' => 'nullable|string',
            'blacklist' => 'nullable|string',
        ]);

        $whitelist = array_filter(array_map('trim', explode("\n", $validated['whitelist'] ?? '')));
        $blacklist = array_filter(array_map('trim', explode("\n", $validated['blacklist'] ?? '')));

        SystemSetting::setValue('ip_access_control_enabled', $validated['enabled'] ?? false, 'boolean', 'security');
        SystemSetting::setValue('ip_whitelist', $whitelist, 'json', 'security');
        SystemSetting::setValue('ip_blacklist', $blacklist, 'json', 'security');

        return redirect()->route('admin.security.index')->with('success', 'IP access control updated.');
    }
}
