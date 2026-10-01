@extends('layouts.admin.admin')
@section('title', __('الملف الشخصي'))

@section('crumbs')
    <x-crumb>{{ __('الملف الشخصي') }}</x-crumb>
@endsection

@section('content')

    <x-profile-page :user="$admin" :role="__('مسؤول النظام')" :action="route('admin.profile.update')"
        :editable="['name', 'email', 'phone', 'gender']"
        :avatar-store="route('admin.profile.avatar.store')"
        :avatar-destroy="route('admin.profile.avatar.destroy')"
        :notice="$isOnlyAdmin
            ? __('أنت حساب المسؤول <strong>الوحيد</strong> في النظام. إن فُقدت كلمة مرورك تعذّر الدخول إلى لوحة التحكم — لا يوجد استرجاع. يُنصح بإضافة حساب ثانٍ من :link.', ['link' => '<a href=&quot;' . route('admin.administrators.index') . '&quot;>' . __('مسؤولي النظام') . '</a>'])
            : null" />

@endsection
