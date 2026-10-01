@extends('layouts.admin.admin')
@section('title', __('الملف الشخصي'))

@section('crumbs')
    <x-crumb>{{ __('الملف الشخصي') }}</x-crumb>
@endsection

@section('content')

    <x-profile-page :user="$student" :role="__('طالب')" :action="route('student.profile.update')"
        :editable="['phone']"
        :avatar-store="route('student.profile.avatar.store')"
        :avatar-destroy="route('student.profile.avatar.destroy')"
        :facts="[
            __('الرقم الجامعي') => $student->university_id,
            __('التخصص') => $student->specialize->name ?: '—',
            __('الجنس') => __('site.' . $student->gender),
        ]" />

@endsection
