@extends('layouts.admin.admin')
@section('title', 'الملف الشخصي')

@section('crumbs')
    <x-crumb>الملف الشخصي</x-crumb>
@endsection

@section('content')

    <x-profile-page :user="$student" role="طالب" :action="route('student.profile.update')"
        :editable="['phone']"
        :avatar-store="route('student.profile.avatar.store')"
        :avatar-destroy="route('student.profile.avatar.destroy')"
        :facts="[
            'الرقم الجامعي' => $student->university_id,
            'التخصص' => $student->specialize->name ?: '—',
            'الجنس' => __('site.' . $student->gender),
        ]" />

@endsection
