<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Contracts\Service\Attribute\Required;

class AuthOtpLoginController extends Controller
{

    public function login()
    {
        return view('auth.otpLogin');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'mobile_no' => 'Required|exists:users,mobile_no',
        ]);

        $userOtp = $this->generateOtp($request->mobile_no);
        $userOtp->sendSMS($request->mobile_no);

        return redirect()->route('otp.verification', [ $userOtp->user_id])->with('success', 'OTP has been Send Successfully..');
    }

    public function generateOtp($mobile_no)
    {
        $user = User::where('mobile_no', $mobile_no)->first();
        $userOtp =   UserOtp::where('user_id', $user->id)->latest()->first();
        $now = now();

        if ($userOtp && $now->isBefore($userOtp->expire_at)) {
            return $userOtp;
        }

        return userOtp::create([
            'user_id' => $user->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function verification($user_id)
    {
        return view('auth.OtpVerification')->with([
            'user_id' => $user_id
        ]);
    }


    public function loginWithOtp(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required'
        ]);

        $userOtp = UserOtp::where('user_id', $request->user_id)->where('otp', $request->otp)->first();
        $now = now();
        if (!$userOtp) {
            return redirect()->back()->with('error', 'Your otp is not correct.');
        } elseif ($userOtp && $now->isAfter($userOtp->expire_at)) {
            return redirect()->back()->with('error', 'Your Otp is expried.');
        }

        $user = User::whereId($request->user_id)->first();
        if($user){
            $userOtp->update([
                'expire_at' => now()
            ]);

            Auth::login($user);
            return redirect('/home');
        }

        return redirect()->route('otp.login')->with('error', 'Your Otp is not match...');  
    }
}
