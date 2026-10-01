{{-- إضافة مسؤول — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $createFailed = $errors->any() && ! old('id');
    $createReopen = $createFailed
        ? ['record' => [], 'old' => request()->old()]
        : (session('reopen_create') ? ['record' => [], 'old' => null] : null);
@endphp

<x-edit-drawer id="createDrawer" mode="create" title="{{ __('إضافة مسؤول جديد') }}" :noun="__('المسؤول')"
    :action="route('admin.administrators.store')" :reopen="$createReopen">

    {{-- صلاحية كاملة على النظام — يُقال قبل أن يُملأ شيء --}}
    <p class="ed-warning">
        <i class="ti ti-shield-lock" aria-hidden="true"></i>
        {{ __('المسؤول يرى كل البيانات ويعدّلها، وتُسجَّل إضافته في سجلّ التدقيق.') }}
    </p>

    <fieldset class="ed-group">
        <legend>{{ __('الهوية') }}</legend>
        <x-ed.field mode="create" name="name" label="{{ __('الاسم') }}" required />
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('التواصل') }}</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="email" label="{{ __('البريد الإلكتروني') }}" type="email" required dir="ltr" wide />
            <x-ed.field mode="create" name="phone" label="{{ __('رقم الجوال') }}" required dir="ltr" inputmode="tel" placeholder="05xxxxxxxx" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الحساب') }}</legend>
        <x-ed.gender mode="create" />
        <x-ed.password mode="create" confirm />
    </fieldset>
</x-edit-drawer>
