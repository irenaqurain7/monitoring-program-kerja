<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Campus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $authenticated = Auth::attempt([
            'username' => $data['username'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        $user = Auth::user();
        $campusMatches = $authenticated && (
            ($user->isSystemAdmin() && $data['campus_id'] === 'system_admin')
            || (! $user->isSystemAdmin()
                && ctype_digit($data['campus_id'])
                && $user->assignedCampusId() !== null
                && $user->assignedCampusId() === (int) $data['campus_id'])
        );

        if (! $campusMatches) {
            if ($authenticated) {
                Auth::logout();
            }

            return back()
                ->withErrors(['username' => 'Nama pengguna, kata sandi, atau pilihan kampus tidak sesuai.'])
                ->onlyInput('username', 'campus_id');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda berhasil keluar.');
    }
}
