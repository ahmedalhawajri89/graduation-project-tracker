{{-- إضافة مشرف — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $createFailed = $errors->any() && ! old('id') && ! $errors->has('attachment');
    $createReopen = $createFailed
        ? ['record' => [], 'old' => request()->old()]
        : (session('reopen_create') ? ['record' => [], 'old' => null] : null);
@endphp

<x-edit-drawer id="createDrawer" mode="create" title="إضافة مشرف جديد" noun="المشرف"
    :action="route('admin.supervisors.store')" :reopen="$createReopen">

    <fieldset class="ed-group">
        <legend>الهوية</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="name" label="اسم المشرف" required wide placeholder="د. الاسم الكامل" />
            <x-ed.field mode="create" name="university_id" label="الرقم الجامعي" required dir="ltr" inputmode="numeric" hint="9 أرقام" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الإشراف</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="specialize_id" label="التخصص" required wide>
                <select id="cr-specialize_id" name="specialize_id" required
                    class="form-select {{ $createFailed && $errors->has('specialize_id') ? 'is-invalid' : '' }}">
                    <option value="" disabled selected>اختر التخصص</option>
                    @foreach ($specializes as $specialize)
                        <option value="{{ $specialize->id }}">{{ $specialize->name }}</option>
                    @endforeach
                </select>
            </x-ed.field>
            <x-ed.field mode="create" name="max_group" label="الحدّ الأقصى للمجموعات" hint="عدد المجموعات التي يقبلها في الفصل">
                <div class="ed-stepper">
                    <button type="button" data-ed-step="-1" aria-label="إنقاص"><i class="ti ti-minus" aria-hidden="true"></i></button>
                    <input id="cr-max_group" name="max_group" type="number" min="0" max="50" inputmode="numeric" data-default="5"
                        class="form-control {{ $createFailed && $errors->has('max_group') ? 'is-invalid' : '' }}">
                    <button type="button" data-ed-step="1" aria-label="زيادة"><i class="ti ti-plus" aria-hidden="true"></i></button>
                </div>
            </x-ed.field>
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>التواصل</legend>
        <div class="ed-grid">
            <x-ed.field mode="create" name="email" label="البريد الإلكتروني" type="email" required dir="ltr" wide
                placeholder="name@supervisor.com" />
            <x-ed.field mode="create" name="phone" label="رقم الجوال" dir="ltr" inputmode="tel" placeholder="05xxxxxxxx" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الحساب</legend>
        <x-ed.gender mode="create" />
        <x-ed.password mode="create" />
    </fieldset>
</x-edit-drawer>
