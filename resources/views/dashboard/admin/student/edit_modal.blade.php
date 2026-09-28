{{-- تعديل طالب — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $reopenStudent = old('id') ? \App\Models\Student::find(old('id')) : null;
@endphp

<x-edit-drawer title="تعديل بيانات الطالب" :action="route('admin.students.update', 'test')" avatar-removal
    :reopen="$reopenStudent ? [
        'record' => \App\Support\EditRecord::student($reopenStudent),
        'old' => request()->old(),
    ] : null">

    <fieldset class="ed-group">
        <legend>الهوية</legend>
        <div class="ed-grid">
            <x-ed.field name="name" label="اسم الطالب" required wide />
            <x-ed.field name="university_id" label="الرقم الجامعي" required dir="ltr" inputmode="numeric"
                hint="10 أرقام تبدأ بـ 130 أو 230" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الأكاديمي</legend>
        <x-ed.field name="specialize_id" label="التخصص" required>
            {{-- الموقوفة معروضة خلافاً لنافذة الإضافة: طالبٌ في تخصص موقوف لن
                 تجد القائمة قيمته الحالية، فيُحفظ على أول خيار — نقلٌ صامت --}}
            <select id="ed-specialize_id" name="specialize_id"
                class="form-select {{ old('id') && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                @foreach ($editSpecializes as $specialize)
                    <option value="{{ $specialize->id }}">
                        {{ $specialize->name }}@if ($specialize->isArchived()) (موقوف)@endif
                    </option>
                @endforeach
            </select>
        </x-ed.field>
    </fieldset>

    <fieldset class="ed-group">
        <legend>التواصل</legend>
        <div class="ed-grid">
            <x-ed.field name="email" label="البريد الإلكتروني" type="email" required dir="ltr" wide />
            <x-ed.field name="phone" label="رقم الجوال" dir="ltr" inputmode="tel" hint="10 أرقام" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الحساب</legend>
        <x-ed.gender />
        <x-ed.password />
    </fieldset>
</x-edit-drawer>
