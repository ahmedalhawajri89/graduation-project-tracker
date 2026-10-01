{{-- إضافة مشرف — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $createFailed = $errors->any() && ! old('id') && ! $errors->has('attachment');
    $createReopen = $createFailed
        ? ['record' => [], 'old' => request()->old()]
        : (session('reopen_create') ? ['record' => [], 'old' => null] : null);
@endphp

<x-edit-drawer id="createDrawer" mode="create" title="{{ __('إضافة مشرف جديد') }}" :noun="__('المشرف')"
    :action="route('admin.supervisors.store')" :reopen="$createReopen">

    <fieldset class="ed-group">
        <legend>{{ __('الهوية') }}</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="name" label="{{ __('اسم المشرف') }}" required wide placeholder="{{ __('د. الاسم الكامل') }}" />
            <x-ed.field mode="create" name="university_id" label="{{ __('الرقم الجامعي') }}" required dir="ltr" inputmode="numeric" hint="{{ __('9 أرقام') }}" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الإشراف') }}</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="specialize_id" label="{{ __('التخصص') }}" required wide>
                <select id="cr-specialize_id" name="specialize_id" required
                    class="form-select {{ $createFailed && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                    <option value="" disabled selected>{{ __('اختر التخصص') }}</option>
                    @foreach ($specializes as $specialize)
                        <option value="{{ $specialize->id }}">{{ $specialize->name }}</option>
                    @endforeach
                </select>
            </x-ed.field>
            <x-ed.field mode="create" name="max_group" label="{{ __('الحدّ الأقصى للمجموعات') }}" hint="{{ __('عدد المجموعات التي يقبلها في الفصل') }}">
                <div class="ed-stepper">
                    <button type="button" data-ed-step="-1" aria-label="{{ __('إنقاص') }}"><i class="ti ti-minus" aria-hidden="true"></i></button>
                    <input id="cr-max_group" name="max_group" type="number" min="0" max="50" inputmode="numeric" data-default="5"
                        class="form-control {{ $createFailed && $errors->has('max_group') ? 'is-invalid' : '' }}">
                    <button type="button" data-ed-step="1" aria-label="{{ __('زيادة') }}"><i class="ti ti-plus" aria-hidden="true"></i></button>
                </div>
            </x-ed.field>
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('التواصل') }}</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="email" label="{{ __('البريد الإلكتروني') }}" type="email" required dir="ltr" wide
                placeholder="name@supervisor.com" />
            <x-ed.field mode="create" name="phone" label="{{ __('رقم الجوال') }}" dir="ltr" inputmode="tel" placeholder="05xxxxxxxx" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الحساب') }}</legend>
        <x-ed.gender mode="create" />
        <x-ed.password mode="create" />
    </fieldset>
</x-edit-drawer>
