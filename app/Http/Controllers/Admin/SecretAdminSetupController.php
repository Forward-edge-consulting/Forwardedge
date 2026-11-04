<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SecretAdminSetupController extends Controller
{
    /**
     * Show the secret admin setup form
     */
    public function show()
    {
        $admin = User::where('role', 'admin')->first();

        return view('admin.secret-setup', compact('admin'));
    }

    /**
     * Update the admin credentials
     */
    public function update(Request $request)
    {
        // Validate the secret key first
        $secretKey = config('admin.secret_setup_key');

        if (!$secretKey || $request->input('secret_key') !== $secretKey) {
            return back()->withErrors(['secret_key' => 'Invalid secret key. Access denied.']);
        }

        $validator = Validator::make($request->all(), [
            'secret_key' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get the current admin
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            // No admin exists, create one
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            return back()->with('success', 'Admin account created successfully!');
        }

        // Admin exists, update credentials
        // First, ensure no other admin exists (enforce single admin rule)
        User::where('role', 'admin')
            ->where('id', '!=', $admin->id)
            ->update(['role' => 'user']);

        // Update the admin credentials
        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Admin credentials updated successfully!');
    }

}
