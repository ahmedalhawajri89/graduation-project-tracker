@extends('layouts.admin.admin')
@section('title', __('الملف الشخصي'))

@section('crumbs')
    <x-crumb>{{ __('الملف الشخصي') }}</x-crumb>
@endsection

@section('content')

    <x-profile-page :user="$supervisor" :role="__('مشرف أكاديمي')" :action="route('supervisor.profile.update')"
        :editable="['phone']"
        :avatar-store="route('supervisor.profile.avatar.store')"
        :avatar-destroy="route('supervisor.profile.avatar.destroy')"
        :facts="[
            __('الرقم الجامعي') => $supervisor->university_id,
            __('التخصص') => $supervisor->specialize->name ?: '—',
            __('الحد الأقصى للمجموعات') => $supervisor->max_group,
            __('الجنس') => __('site.' . $supervisor->gender),
        ]" />

@endsection
