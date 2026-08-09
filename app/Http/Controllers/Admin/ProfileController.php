<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function edit()
    {
        return view('dashboard.admin.profile', [
            'admin' => auth('admin')->user(),
        ]);
    }

    public function update(Request $request)
    {
        $admin = auth('admin')->user();

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
            if (! Hash::check($request->current_password, $admin->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'كلمة السر الحالية غير صحيحة'])
                    ->withInput($request->only('phone'));
            }
            $admin->password = bcrypt($request->password);
        }

        $admin->phone = $request->phone;
        $admin->save();

        return redirect()->back()->with('success', 'تم تحديث بياناتك بنجاح');
    }
}
