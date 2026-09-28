{{-- تعديل مسؤول — درج جانبي، انظر \u200Ex-edit-drawer\u200E --}}
@php
    $reopenAdmin = old('id') ? \App\Models\Admin::find(old('id')) : null;
@endphp

<x-edit-drawer title="تعديل بيانات المسؤول" :action="route('admin.administrators.update', 'test')"
    :reopen="$reopenAdmin ? [
        'record' => \App\Support\EditRecord::admin($reopenAdmin),
        'old' => request()->old(),
    ] : null">

    <fieldset class="ed-group">
        <legend>الهوية</legend>
        <x-ed.field name="name" label="الاسم" required />
    </fieldset>

    <fieldset class="ed-group">
        <legend>التواصل</legend>
        <div class="ed-grid">
            <x-ed.field name="email" label="البريد الإلكتروني" type="email" required dir="ltr" wide />
            <x-ed.field name="phone" label="رقم الجوال" required dir="ltr" inputmode="tel" />
        </div>
    </fieldset>

    <fieldset class="ed-group">
        <legend>الحساب</legend>
        <x-ed.gender />
        {{-- قاعدة المسؤول تطلب التأكيد: يُملأ من الحقل نفسه، والإظهار يغني عن كتابتها مرّتين --}}
        <x-ed.password confirm />
    </fieldset>
</x-edit-drawer>
