@extends('layouts.admin.admin')
@section('title', 'الملف الشخصي')

@section('crumbs')
    <x-crumb>الملف الشخصي</x-crumb>
@endsection

@section('content')

    <x-profile-page :user="$supervisor" role="مشرف أكاديمي" :action="route('supervisor.profile.update')"
        :editable="['phone']"
        :avatar-store="route('supervisor.profile.avatar.store')"
        :avatar-destroy="route('supervisor.profile.avatar.destroy')"
        :facts="[
            'الرقم الجامعي' => $supervisor->university_id,
            'التخصص' => $supervisor->specialize->name ?: '—',
            'الحد الأقصى للمجموعات' => $supervisor->max_group,
            'الجنس' => __('site.' . $supervisor->gender),
        ]" />

@endsection
