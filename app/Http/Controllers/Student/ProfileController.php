<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\UpdatesOwnProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    use UpdatesOwnProfile;

    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /**
     * الاسم والبريد والرقم الجامعي والتخصص تصدرها الجامعة، ولا
     * يُعدّلها صاحبها من ملفه — يبقى له جواله وكلمة سرّه.
     */
    protected function editableFields(): array
    {
        return ['phone'];
    }

    protected function profileUser()
    {
        return auth('student')->user();
    }

    public function edit()
    {
        return view('dashboard.student.profile', [
            'student' => $this->profileUser()->loadMissing('specialize'),
        ]);
    }

    public function update(ProfileRequest $request)
    {
        return $this->saveProfile($request);
    }
}
