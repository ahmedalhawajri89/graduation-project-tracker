<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function edit()
    {
        return view('dashboard.student.profile', [
            'student' => auth('student')->user(),
        ]);
    }

    public function update(Request $request)
    {
        $student = auth('student')->user();

        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'max:30', 'confirmed'],
        ], [], [
            'phone' => 'رقم الجوال',
            'current_password' => 'كلمة السر الحالية',
            'password' => 'كلمة السر الجديدة',
        ]);

        // عند تغيير كلمة السر يجب التحقق من الحالية أولاً
        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $student->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'كلمة السر الحالية غير صحيحة'])
                    ->withInput($request->only('phone'));
            }
            $student->password = bcrypt($request->password);
        }

        $student->phone = $request->phone;
        $student->save();

        return redirect()->back()->with('success', 'تم تحديث بياناتك بنجاح');
    }
}
