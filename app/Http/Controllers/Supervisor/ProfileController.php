<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:supervisor');
    }

    public function edit()
    {
        return view('dashboard.supervisor.profile', [
            'supervisor' => auth('supervisor')->user(),
        ]);
    }

    public function update(Request $request)
    {
        $supervisor = auth('supervisor')->user();

        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'max:30', 'confirmed'],
        ], [], [
            'phone' => 'رقم الجوال',
            'current_password' => 'كلمة السر الحالية',
            'password' => 'كلمة السر الجديدة',
        ]);

        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $supervisor->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'كلمة السر الحالية غير صحيحة'])
                    ->withInput($request->only('phone'));
            }
            $supervisor->password = bcrypt($request->password);
        }

        $supervisor->phone = $request->phone;
        $supervisor->save();

        return redirect()->back()->with('success', 'تم تحديث بياناتك بنجاح');
    }
}
