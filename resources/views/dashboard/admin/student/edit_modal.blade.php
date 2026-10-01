{{-- تعديل طالب — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $reopenStudent = old('id') ? \App\Models\Student::find(old('id')) : null;
@endphp

<x-edit-drawer title="{{ __('تعديل بيانات الطالب') }}" :action="route('admin.students.update', 'test')" avatar-removal
    :reopen="$reopenStudent ? [
        'record' => \App\Support\EditRecord::student($reopenStudent),
        'old' => request()->old(),
    ] : null">

    <fieldset class="ed-group">
        <legend>{{ __('الهوية') }}</legend>
        <div class="ed-grid">
            <x-ed.field name="name" label="{{ __('اسم الطالب') }}" required wide />
            <x-ed.field name="university_id" label="{{ __('الرقم الجامعي') }}" required dir="ltr" inputmode="numeric"
                hint="{{ __('10 أرقام تبدأ بـ 130 أو 230') }}" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الأكاديمي') }}</legend>
        <x-ed.field name="specialize_id" label="{{ __('التخصص') }}" required>
            {{-- الموقوفة معروضة خلافاً لنافذة الإضافة: طالبٌ في تخصص موقوف لن
                 تجد القائمة قيمته الحالية، فيُحفظ على أول خيار — نقلٌ صامت --}}
            <select id="ed-specialize_id" name="specialize_id"
                class="form-select {{ old('id') && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                @foreach ($editSpecializes as $specialize)
                    <option value="{{ $specialize->id }}">
                        {{ $specialize->name }}@if ($specialize->isArchived()) ({{ __('موقوف') }})@endif
                    </option>
                @endforeach
            </select>
        </x-ed.field>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('التواصل') }}</legend>
        <div class="ed-grid">
            <x-ed.field name="email" label="{{ __('البريد الإلكتروني') }}" type="email" required dir="ltr" wide />
            <x-ed.field name="phone" label="{{ __('رقم الجوال') }}" dir="ltr" inputmode="tel" hint="{{ __('10 أرقام') }}" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>{{ __('الحساب') }}</legend>
        <x-ed.gender />
        <x-ed.password />
    </fieldset>
</x-edit-drawer>
