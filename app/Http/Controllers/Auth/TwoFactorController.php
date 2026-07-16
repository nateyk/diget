<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class TwoFactorController extends Controller
{
    public function show2FaVerifyForm()
    {
        $marker = session('user_2fa');
        if (!authUser()->google2fa_status || (is_array($marker)
            && ($marker['user_id'] ?? null) == authUser()->id
            && ($marker['session_id'] ?? null) === session()->getId())) {
            return redirect()->route('home');
        }
        return theme_view('auth.2fa');
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
        $valid = $google2fa->verifyKey(authUser()->google2fa_secret, $request->otp_code);
        if ($valid == false) {
            toastr()->error(translate('Invalid OTP code'));
            return back();
        }

        session()->put('user_2fa', [
            'guard' => 'web',
            'user_id' => authUser()->id,
            'session_id' => session()->getId(),
            'verified_at' => now()->toIso8601String(),
        ]);
        return redirect()->route('home');
    }
}
