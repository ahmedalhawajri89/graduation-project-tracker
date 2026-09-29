@extends('layouts.admin.admin')
@section('title', "أنواع المشاريع — {$specialize->name}")

@section('crumbs')
    <x-crumb :href="route('admin.specialize.index')">التخصصات</x-crumb>
    <x-crumb>{{ $specialize->name }}</x-crumb>
@endsection

@section('content')

    @php
        $inUse = $types->where('projects_count', '>', 0)->count();
        $count = $types->count();
        $subtitle = 'تخصص ' . $specialize->name;
        if ($count) {
            $subtitle .= ' · ' . $count . ' ' . ($count == 1 ? 'نوع' : ($count == 2 ? 'نوعان' : 'أنواع'));
        }
    @endphp

    <x-page-header title="أنواع المشاريع" :subtitle="$subtitle">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة نوع مشروع
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ التنقّل بين التخصصات ═══ --}}
    @if ($specializes->count() > 1)
        <nav class="pt-tabs mb-3" aria-label="التخصصات">
            @foreach ($specializes as $s)
                <a href="{{ route('admin.specialize.projects.index', $s->id) }}"
                    class="pt-tab {{ $s->id === $specialize->id ? 'is-active' : '' }}"
                    @if ($s->id === $specialize->id) aria-current="page" @endif>
                    {{ $s->name }}
                    <span class="pt-tab-n {{ $s->projects_count ? '' : 'is-zero' }}">{{ $s->projects_count }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    {{-- نوع المشروع ليس تسمية: حدّاه هما ما يقبل النظام به فريقاً أو
         يرفضه عند التسجيل. الشرح هنا لأن الحقلين في النافذة وحدهما
         لا يقولان أثرهما. --}}
    <div class="hint-bar mb-3">
        <i class="ti ti-info-circle" aria-hidden="true"></i>
        <span>
            حدّا الفريق يُطبَّقان عند تسجيل الطالب لمشروعه: فريق خارج المدى يُرفض.
            وتعديلهما لا يمسّ الفرق المسجَّلة سابقاً — وتظهر هنا إن صارت خارجهما.
        </span>
    </div>

    @php
        // مقياس واحد لكل البطاقات فتُقارَن بالعين: أكبر حدّ أو أكبر فريق فعلي، ولا أقلّ من 5
        $scale = max(5, (int) $types->max('max'), (int) $types->map(fn ($t) => $t->sizes ? max(array_keys($t->sizes)) : 0)->max());
    @endphp

    @if ($types->count())
        <div class="pt-grid">
            @foreach ($types as $type)
                @php
                    $teams = array_sum($type->sizes);
                    $peak = max(1, $type->sizes ? max($type->sizes) : 1);
                    // العدد والمعدود متوافقان: «فريق واحد، فريقان، 5 فرق، 14 فريقاً»
                    $teamsLabel = fn ($n) => match (true) { $n === 1 => 'فريق واحد', $n === 2 => 'فريقان', $n <= 10 => $n . ' فرق', default => $n . ' فريقاً' };
                @endphp
                <article class="pt-card {{ $type->outside_count ? 'has-outside' : '' }}">
                    <header class="pt-head">
                        <h3>{{ $type->name }}</h3>
                        <div class="dropdown">
                            <button type="button" class="btn-action" data-bs-toggle="dropdown" aria-expanded="false"
                                title="إجراءات" aria-label="إجراءات {{ $type->name }}">
                                <i class="ti ti-dots" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="{{ $type->id }}" data-name="{{ $type->name }}" data-min="{{ $type->min }}"
                                    data-max="{{ $type->max }}" data-sizes="{{ json_encode((object) $type->sizes) }}">
                                    <i class="ti ti-pencil me-2" aria-hidden="true"></i> تعديل الاسم والحدود
                                </a>
                                @if ($type->projects_count)
                                    <a href="{{ route('admin.groups.index', ['type' => $type->id]) }}" class="dropdown-item">
                                        <i class="ti ti-users-group me-2" aria-hidden="true"></i> عرض مجموعاته
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <span class="dropdown-item disabled"
                                        title="يستعمله {{ $type->projects_count }} مشروعاً — ستفقد نوعها وحدود فريقها">
                                        <i class="ti ti-lock me-2" aria-hidden="true"></i> لا يُحذف — مستعمَل
                                    </span>
                                @else
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item text-danger btn-delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal" data-id="{{ $type->id }}" data-name="{{ $type->name }}">
                                        <i class="ti ti-trash me-2" aria-hidden="true"></i> حذف
                                    </a>
                                @endif
                            </div>
                        </div>
                    </header>

                    {{-- المدى بنظرة: النقاط داخل الحدّين ممتلئة --}}
                    <div class="pt-range">
                        <span class="pt-dots" aria-hidden="true">
                            @for ($i = 1; $i <= $scale; $i++)
                                <i class="{{ $i >= $type->min && $i <= $type->max ? 'is-in' : '' }}"></i>
                            @endfor
                        </span>
                        <span class="pt-range-text">
                            الفريق من <b>{{ $type->min }}</b> إلى <b>{{ $type->max }}</b>
                            {{ $type->max == 2 ? 'عضوين' : ($type->max > 2 ? 'أعضاء' : 'عضو') }}
                        </span>
                    </div>

                    {{-- الفرق الفعلية: ما يُبنى عليه تعديل الحدّين --}}
                    <div class="pt-sizes">
                        <div class="pt-sizes-head">
                            <span>أحجام الفرق الفعلية</span>
                            <span>{{ $teamsLabel($teams) }}</span>
                        </div>
                        @if ($teams)
                            <div class="pt-hist" role="img"
                                aria-label="{{ collect($type->sizes)->map(fn ($n, $size) => $n . ' بحجم ' . $size)->implode('، ') }}">
                                @for ($i = 1; $i <= $scale; $i++)
                                    @php $n = $type->sizes[$i] ?? 0; $out = $n && ($i < $type->min || $i > $type->max); @endphp
                                    <span class="pt-col {{ $out ? 'is-out' : '' }} {{ $n ? '' : 'is-empty' }}"
                                        title="{{ $n }} {{ $n === 1 ? 'فريق' : 'فرق' }} بحجم {{ $i }}">
                                        <span class="pt-col-n">{{ $n ?: '' }}</span>
                                        <span class="pt-col-bar" style="height: {{ $n ? max(8, round($n / $peak * 100)) : 0 }}%"></span>
                                        <span class="pt-col-x">{{ $i }}</span>
                                    </span>
                                @endfor
                            </div>
                        @else
                            <p class="pt-none">لم يُسجَّل عليه فريق بعد.</p>
                        @endif
                    </div>

                    @if ($type->outside_count)
                        <p class="pt-outside">
                            <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                            {{ $teamsLabel($type->outside_count) }} خارج الحدود الحالية —
                            {{ $type->outside_count <= 2 ? 'سُجّل قبل تعديلها، ولا يُرفض الآن.' : 'سُجّلت قبل تعديلها، ولا تُرفض الآن.' }}
                        </p>
                    @endif

                    <footer class="pt-foot">
                        @if ($type->projects_count)
                            <a href="{{ route('admin.groups.index', ['type' => $type->id]) }}">
                                <b>{{ $type->current_count }}</b> جارٍ هذا الفصل ·
                                <b>{{ $type->projects_count }}</b> في كل الفصول
                            </a>
                        @else
                            <span>لم يُستعمل بعد — يمكن حذفه</span>
                        @endif
                    </footer>
                </article>
            @endforeach

            <button type="button" class="pt-add btn-create" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="ti ti-plus" aria-hidden="true"></i>
                <b>إضافة نوع مشروع</b>
                <span>لتخصص {{ $specialize->name }}</span>
            </button>
        </div>
    @else
        {{-- هذه ليست قائمة فارغة عادية: بلا نوع واحد، لا يستطيع أي طالب
             في هذا التخصص تسجيل مشروع إطلاقاً --}}
        <div class="card">
            <x-empty-state icon="ti-shape" title="لا أنواع مشاريع في هذا التخصص"
                text="لا يستطيع طلاب «{{ $specialize->name }}» تسجيل مشروع حتى يوجد نوع واحد على الأقل: نموذج التسجيل يطلب النوع، ويتحقّق من حجم الفريق بحدّيه."
                class="py-6">
                <x-slot:action>
                    <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="ti ti-plus me-1" aria-hidden="true"></i>
                        إضافة أول نوع
                    </button>
                </x-slot:action>
            </x-empty-state>
        </div>
    @endif

    @include('dashboard.admin.setting.specialize.project.create_modal')
    @include('dashboard.admin.setting.specialize.project.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'نوع المشروع',
        'delete_controller_name' => 'admin.specialize.projects',
        'delete_note' => 'لا يمكن حذف نوع يستعمله مشروع قائم.',
    ])

@endsection

@push('js')
    <script>
        // إعادة فتح النافذة عند فشل التحقّق — كانت تأتي ضمن تضمين
        // \u200Edatatables_style_script\u200E وقد زال مع الجدول
        @if ($errors->any() && old('submit') == 'create')
            new bootstrap.Modal(document.getElementById('createModal')).show();
        @endif
        @if ($errors->any() && old('submit') == 'update')
            new bootstrap.Modal(document.getElementById('editModal')).show();
        @endif

        // حدّا الفريق في النافذتين: عدّاد − / +، ونقاط تتحدّث حيّاً، وفي التعديل
        // عدد الفرق القائمة التي ستصير خارج الحدود الجديدة — يُقال قبل الحفظ
        (function () {
            var SCALE = {{ $scale }};
            document.querySelectorAll('[data-range-form]').forEach(function (form) {
                var min = form.querySelector('[name="min"]');
                var max = form.querySelector('[name="max"]');
                var dots = form.querySelector('[data-range-dots]');
                var text = form.querySelector('[data-range-text]');
                var warn = form.querySelector('[data-range-warn]');
                var submit = form.querySelector('[type="submit"]');

                function render() {
                    var lo = parseInt(min.value, 10) || 0, hi = parseInt(max.value, 10) || 0;
                    var n = Math.max(SCALE, hi);
                    dots.innerHTML = '';
                    for (var i = 1; i <= n; i++) {
                        var d = document.createElement('i');
                        if (i >= lo && i <= hi) d.className = 'is-in';
                        dots.appendChild(d);
                    }
                    var bad = !lo || !hi || lo > hi;
                    text.textContent = bad ? 'الحدّ الأدنى يجب ألا يتجاوز الأعلى' : 'الفريق من ' + lo + ' إلى ' + hi + (hi === 2 ? ' عضوين' : hi > 2 ? ' أعضاء' : ' عضو');
                    text.classList.toggle('is-bad', bad);
                    submit.disabled = bad;

                    if (warn) {
                        var sizes = JSON.parse(form.dataset.sizes || '{}'), out = 0;
                        Object.keys(sizes).forEach(function (s) { s = +s; if (s < lo || s > hi) out += sizes[s]; });
                        warn.hidden = !out || bad;
                        warn.querySelector('b').textContent = out === 1 ? 'فريق قائم واحد' : out === 2 ? 'فريقان قائمان' : out + ' فرق قائمة';
                        warn.querySelector('[data-range-warn-text]').textContent = (out <= 2 ? 'سيصير' : 'ستصير')
                            + ' خارج الحدود الجديدة. لن ' + (out <= 2 ? 'يُرفض' : 'تُرفض') + ' — التحقّق عند التسجيل وحده.';
                    }
                }

                form.querySelectorAll('[data-step]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var input = form.querySelector('[name="' + btn.dataset.field + '"]');
                        input.value = Math.max(1, Math.min(20, (parseInt(input.value, 10) || 0) + parseInt(btn.dataset.step, 10)));
                        render();
                    });
                });
                [min, max].forEach(function (i) { i.addEventListener('input', render); });
                form.renderRange = render;
                render();
            });

            // زرّ التعديل يحمل أحجام فرق نوعه — تُمرَّر للتحذير
            $('body').on('click', '.btn-edit', function () {
                var form = document.querySelector('#editModal [data-range-form]');
                form.dataset.sizes = JSON.stringify($(this).data('sizes') || {});
                setTimeout(function () { form.renderRange(); }, 0);
            });
        })();

        // قادم من بطاقة تخصص ينقصه نوع: تُفتح نافذة الإضافة مباشرةً
        if (window.location.hash === '#add') {
            new bootstrap.Modal(document.getElementById('createModal')).show();
        }
    </script>
@endpush
