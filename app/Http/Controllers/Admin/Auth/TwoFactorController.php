<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class TwoFactorController extends Controller
{
    public function show2FaVerifyForm()
    {
        $marker = session('admin_2fa');
        if (!authAdmin()->google2fa_status || (is_array($marker)
            && ($marker['user_id'] ?? null) == authAdmin()->id
            && ($marker['session_id'] ?? null) === session()->getId())) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.2fa');
    }

    public function verify2fa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp_code' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey(authAdmin()->google2fa_secret, $request->otp_code);
        if ($valid == false) {
            toastr()->error(translate('Invalid OTP code'));
            return back();
        }

        session()->put('admin_2fa', [
            'guard' => 'admin',
            'user_id' => authAdmin()->id,
            'session_id' => session()->getId(),
            'verified_at' => now()->toIso8601String(),
        ]);
        return redirect()->route('admin.dashboard');
    }
}
