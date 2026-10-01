{{-- تعديل مشرف — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $reopenSupervisor = old('id') ? \App\Models\Supervisor::find(old('id')) : null;
@endphp

<x-edit-drawer title="{{ __('تعديل بيانات المشرف') }}" :action="route('admin.supervisors.update', 'test')" avatar-removal
    :reopen="$reopenSupervisor ? [
        'record' => \App\Support\EditRecord::supervisor($reopenSupervisor),
        'old' => request()->old(),
    ] : null">

    <fieldset class="ed-group">
        <legend>{{ __('الهوية') }}</legend>
        <div class="ed-grid">
            <x-ed.field name="name" label="{{ __('اسم المشرف') }}" required wide />
            <x-ed.field name="university_id" label="{{ __('الرقم الجامعي') }}" required dir="ltr" inputmode="numeric" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الإشراف') }}</legend>
        <div class="ed-grid">
            <x-ed.field name="specialize_id" label="{{ __('التخصص') }}" required wide>
                {{-- الموقوفة معروضة: مشرفٌ في تخصص موقوف لن تجد القائمة قيمته فيُنقل صامتاً --}}
                <select id="ed-specialize_id" name="specialize_id"
                    class="form-select {{ old('id') && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                    @foreach ($editSpecializes as $specialize)
                        <option value="{{ $specialize->id }}">
                            {{ $specialize->name }}@if ($specialize->isArchived()) ({{ __('موقوف') }})@endif
                        </option>
                    @endforeach
                </select>
            </x-ed.field>
            <x-ed.field name="max_group" label="{{ __('الحدّ الأقصى للمجموعات') }}" hint="{{ __('عدد المجموعات التي يقبلها في الفصل') }}">
                <div class="ed-stepper">
                    <button type="button" data-ed-step="-1" aria-label="{{ __('إنقاص') }}"><i class="ti ti-minus" aria-hidden="true"></i></button>
                    <input id="ed-max_group" name="max_group" type="number" min="0" max="50" inputmode="numeric"
                        class="form-control {{ old('id') && $errors->has('max_group') ? 'is-invalid' : '' }}">
                    <button type="button" data-ed-step="1" aria-label="{{ __('زيادة') }}"><i class="ti ti-plus" aria-hidden="true"></i></button>
                </div>
            </x-ed.field>
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('التواصل') }}</legend>
        <div class="ed-grid">
            <x-ed.field name="email" label="{{ __('البريد الإلكتروني') }}" type="email" required dir="ltr" wide />
            <x-ed.field name="phone" label="{{ __('رقم الجوال') }}" dir="ltr" inputmode="tel" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الحساب') }}</legend>
        <x-ed.gender />
        <x-ed.password />
    </fieldset>
</x-edit-drawer>
