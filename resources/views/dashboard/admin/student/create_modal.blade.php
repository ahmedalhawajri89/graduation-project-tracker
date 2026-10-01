{{-- إضافة طالب — درج جانبي، انظر \u200Ex-edit-drawer\u200E.
     يُعاد فتحه بعد فشل الإضافة (الأخطاء بلا \u200Eold('id')\u200E ولا ملف استيراد)،
     وبعد «حفظ وإضافة آخر» فارغاً للتالي --}}
@php
    $createFailed = $errors->any() && ! old('id') && ! $errors->has('attachment');
    $createReopen = $createFailed
        ? ['record' => [], 'old' => request()->old()]
        : (session('reopen_create') ? ['record' => [], 'old' => null] : null);
@endphp

<x-edit-drawer id="createDrawer" mode="create" title="{{ __('إضافة طالب جديد') }}" :noun="__('الطالب')"
    :action="route('admin.students.store')" :reopen="$createReopen">

    <fieldset class="ed-group">
        <legend>{{ __('الهوية') }}</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="name" label="{{ __('اسم الطالب') }}" required wide placeholder="{{ __('الاسم الرباعي') }}" />
            <x-ed.field mode="create" name="university_id" label="{{ __('الرقم الجامعي') }}" required dir="ltr" inputmode="numeric"
                hint="{{ __('10 أرقام تبدأ بـ 130 أو 230') }}" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الأكاديمي') }}</legend>
        <x-ed.field mode="create" name="specialize_id" label="{{ __('التخصص') }}" required>
            {{-- النشطة وحدها: لا يُسجَّل أحد جديد في تخصص موقوف --}}
            <select id="cr-specialize_id" name="specialize_id" required
                class="form-select {{ $createFailed && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                <option value="" disabled selected>{{ __('اختر التخصص') }}</option>
                @foreach ($specializes as $specialize)
                    <option value="{{ $specialize->id }}">{{ $specialize->name }}</option>
                @endforeach
            </select>
        </x-ed.field>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('التواصل') }}</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="email" label="{{ __('البريد الإلكتروني') }}" type="email" required dir="ltr" wide
                placeholder="name@student.com" />
            <x-ed.field mode="create" name="phone" label="{{ __('رقم الجوال') }}" dir="ltr" inputmode="tel" hint="{{ __('10 أرقام') }}" placeholder="05xxxxxxxx" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الحساب') }}</legend>
        <x-ed.gender mode="create" />
        <x-ed.password mode="create" />
    </fieldset>
</x-edit-drawer>
