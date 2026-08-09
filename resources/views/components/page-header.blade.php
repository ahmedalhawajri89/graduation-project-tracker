@props(['pretitle' => null, 'title'])

{{--
    ترويسة صفحة موحّدة.
    الاستخدام:
    <x-page-header pretitle="إدارة البيانات" title="بيانات الطلاب">
        <x-slot:actions> ... أزرار ... </x-slot:actions>
    </x-page-header>
--}}

<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            @if ($pretitle)
                <div class="page-pretitle">{{ $pretitle }}</div>
            @endif
            <h2 class="page-title">{{ $title }}</h2>
        </div>

        @isset($actions)
            <div class="col-auto d-flex gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
