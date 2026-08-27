<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use IAnanta\UserManagement\Models\Admin;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::guard('admin')->user();
        $user->load('roles');

        return view('profile.index', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('admins', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($user->id),
            ],
        ]);

        Admin::where('id', $user->id)->update($validated);

        return redirect()
            ->route('admin.profile')
            ->with(['message' => 'Profile updated successfully', 'type' => 'success']);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()
                ->route('admin.profile')
                ->with(['message' => 'Current password did not match', 'type' => 'error']);
        }

        Admin::where('id', $user->id)->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return redirect()
            ->route('admin.profile')
            ->with(['message' => 'Password changed successfully', 'type' => 'success']);
    }
}
