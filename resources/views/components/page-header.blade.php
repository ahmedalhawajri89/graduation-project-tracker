@props(['pretitle' => null, 'title', 'subtitle' => null])

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
            @if ($subtitle)
                <div class="page-subtitle">{{ $subtitle }}</div>
            @endif
        </div>

        @isset($actions)
            {{-- تحت ٥٧٦ تنزل الأزرار إلى سطرها وتلتفّ: ثلاثة أزرار بجانب
                 عنوان طويل تتجاوز ٣٧٥ بكسل --}}
            <div class="col-12 col-sm-auto d-flex flex-wrap align-items-center gap-2 mt-2 mt-sm-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
