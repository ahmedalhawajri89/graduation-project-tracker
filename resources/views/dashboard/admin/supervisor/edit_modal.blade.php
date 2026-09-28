{{-- تعديل مشرف — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $reopenSupervisor = old('id') ? \App\Models\Supervisor::find(old('id')) : null;
@endphp

<x-edit-drawer title="تعديل بيانات المشرف" :action="route('admin.supervisors.update', 'test')" avatar-removal
    :reopen="$reopenSupervisor ? [
        'record' => \App\Support\EditRecord::supervisor($reopenSupervisor),
        'old' => request()->old(),
    ] : null">

    <fieldset class="ed-group">
        <legend>الهوية</legend>
        <div class="ed-grid">
            <x-ed.field name="name" label="اسم المشرف" required wide />
            <x-ed.field name="university_id" label="الرقم الجامعي" required dir="ltr" inputmode="numeric" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الإشراف</legend>
        <div class="ed-grid">
            <x-ed.field name="specialize_id" label="التخصص" required wide>
                {{-- الموقوفة معروضة: مشرفٌ في تخصص موقوف لن تجد القائمة قيمته فيُنقل صامتاً --}}
                <select id="ed-specialize_id" name="specialize_id"
                    class="form-select {{ old('id') && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                    @foreach ($editSpecializes as $specialize)
                        <option value="{{ $specialize->id }}">
                            {{ $specialize->name }}@if ($specialize->isArchived()) (موقوف)@endif
                        </option>
                    @endforeach
                </select>
            </x-ed.field>
            <x-ed.field name="max_group" label="الحدّ الأقصى للمجموعات" hint="عدد المجموعات التي يقبلها في الفصل">
                <div class="ed-stepper">
                    <button type="button" data-ed-step="-1" aria-label="إنقاص"><i class="ti ti-minus" aria-hidden="true"></i></button>
                    <input id="ed-max_group" name="max_group" type="number" min="0" max="50" inputmode="numeric"
                        class="form-control {{ old('id') && $errors->has('max_group') ? 'is-invalid' : '' }}">
                    <button type="button" data-ed-step="1" aria-label="زيادة"><i class="ti ti-plus" aria-hidden="true"></i></button>
                </div>
            </x-ed.field>
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>التواصل</legend>
        <div class="ed-grid">
            <x-ed.field name="email" label="البريد الإلكتروني" type="email" required dir="ltr" wide />
            <x-ed.field name="phone" label="رقم الجوال" dir="ltr" inputmode="tel" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الحساب</legend>
        <x-ed.gender />
        <x-ed.password />
    </fieldset>
</x-edit-drawer>
