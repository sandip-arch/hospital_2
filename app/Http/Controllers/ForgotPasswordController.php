<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgotPassword'); 
    }
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        
        $otp = rand(100000, 999999);

        // Delete any old OTPs for this email in the existing table
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Save the new OTP securely
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp, // We are hijacking the 'token' column to store our OTP
            'created_at' => Carbon::now()
        ]);

        // Send the actual email using Laravel's Mail facade
        Mail::raw("Your Apex Horizon password reset code is: {$otp}. This code will expire in 10 minutes.", function ($message) use ($email) {
            $message->to($email)
                    ->subject('Password Reset OTP - Apex Horizon');
        });

        // Redirect to the OTP typing screen and pass the email along securely
        return redirect()->route('password.otp.form')->with('reset_email', $email);
    }
    // 3. Show the screen where the user types the 6-digit code
    public function showOtpForm()
    {
        // Prevent users from accessing this page directly without an active session
        if (!session('reset_email') && !session()->hasOldInput('email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Please request a new reset code.']);
        }
        
        return view('auth.verifyOtp');
    }

    // 4. Check if the code they typed matches the database
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
        ]);

        // Find the record in the database
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', $request->otp)
                    ->first();

        // If the code is wrong
        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid OTP code. Please check your email and try again.'])->withInput(['email' => $request->email]);
        }

        // Check if the code is older than 10 minutes
        $createdAt = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 10) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('password.request')->withErrors(['email' => 'Your OTP has expired (10 minute limit). Please request a new one.']);
        }

        // The code is correct and valid! Send them to the final password reset screen.
        return redirect()->route('password.reset.form')->with('reset_email', $request->email);
    }

    // 5. Show the final screen to type the new password
    public function showResetForm()
    {
        // Block users who try to type the URL directly without verifying an OTP first
        if (!session('reset_email') && !session()->hasOldInput('email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Please verify your OTP first.']);
        }
        
        return view('auth.resetPassword');
    }

    // 6. Securely hash and save the new password
    public function updatePassword(Request $request)
    {
        // Ensure passwords match and are at least 8 characters
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Find the user and update their password with secure hashing
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the temporary OTP from the database to keep it clean
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Send them back to the login page with a green success message
        return redirect()->route('login')->with('success', 'Password reset successfully! You can now log in.');
    }
}