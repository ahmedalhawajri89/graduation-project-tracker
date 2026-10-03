@extends('layouts.admin.admin')
@section('title', __('خطة المراحل'))

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">{{ __('لوحتي') }}</x-crumb>
    <x-crumb>{{ __('خطة المراحل') }}</x-crumb>
@endsection

@section('content')

    @php
        $groupsCount = $projects->count();
        $activeCount = $projects->where('status', 'accept')->whereNull('grade')->count();
        $suggestions = [__('المقترح'), __('الفصل الأول: المقدمة'), __('الفصل الثاني: الدراسات السابقة'), __('التحليل والتصميم'), __('التنفيذ'), __('العرض النهائي')];
        // \u200E?new=1\u200E: زرّ «مرحلة جديدة» في اللوحة يفتح النموذج مباشرة
        $formOpen = $stages->isEmpty() || $errors->any() || request()->boolean('new');
    @endphp

    <x-page-header title="{{ __('خطة المراحل') }}"
        subtitle="{{ $semester->label }} · {{ $activeCount === 1
            ? __('المرحلة تُعرَّف مرّة فتصل إلى :n مجموعة جارية — وكل مجموعة تقبلها لاحقاً', ['n' => 1])
            : __('المرحلة تُعرَّف مرّة فتصل إلى :n مجموعات جارية — وكل مجموعة تقبلها لاحقاً', ['n' => $activeCount]) }}">
        @if ($stages->isNotEmpty())
            <x-slot:actions>
                <button type="button" class="btn btn-primary" data-plan-add aria-controls="stage-new">
                    <i class="ti ti-plus me-1" aria-hidden="true"></i>
                    {{ __('مرحلة جديدة') }}
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- ===== مرحلة جديدة ===== --}}
    <section class="stage-compose {{ $formOpen ? '' : 'd-none' }}" id="stage-new">
        @if ($stages->isEmpty())
            <div class="stage-intro">
                <span class="stage-intro-icon" aria-hidden="true"><i class="ti ti-route"></i></span>
                <div>
                    <h2>{{ __('ارسم مسار مجموعاتك مرّة واحدة') }}</h2>
                    <p>
                        {{ __('كل مرحلة تضيفها هنا — بموعدها وتعليماتها وقالبها — تظهر في كل مجموعاتك، وتصل كل مجموعة تقبلها لاحقاً. وتعديلها يسري على الجميع، وما أُنجز يبقى كما هو.') }}
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('supervisor.plan.store') }}" method="POST" enctype="multipart/form-data" class="stage-form">
            @csrf
            <div class="stage-form-grid">
                <div class="stage-field is-title">
                    <label class="form-label" for="new-title">{{ __('عنوان المرحلة') }}</label>
                    <input type="text" id="new-title" name="title" required maxlength="150"
                        class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                        placeholder="{{ __('مثال: الفصل الأول — المقدمة ومشكلة البحث') }}">
                    <div class="stage-suggest" aria-label="{{ __('اقتراحات') }}">
                        @foreach ($suggestions as $s)
                            <button type="button" class="stage-suggest-chip" data-suggest="{{ $s }}">{{ $s }}</button>
                        @endforeach
                    </div>
                    @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="stage-field is-date">
                    <label class="form-label" for="new-due">{{ __('الموعد') }}</label>
                    <input type="date" id="new-due" name="due_date" min="{{ now()->toDateString() }}"
                        class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                    @error('due_date') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="stage-field is-wide">
                    <label class="form-label" for="new-instructions">{{ __('تعليمات للطلاب') }} <small>{{ __('(اختيارية)') }}</small></label>
                    <textarea id="new-instructions" name="instructions" rows="2" maxlength="2000" class="form-control"
                        placeholder="{{ __('ما المطلوب في هذه المرحلة، وكيف يُسلَّم') }}">{{ old('instructions') }}</textarea>
                </div>
                <div class="stage-field is-wide">
                    <span class="form-label">{{ __('القالب') }} <small>{{ __('(اختياري — يراه الطلاب داخل المرحلة)') }}</small></span>
                    <label class="file-drop is-compact" for="new-template" data-template-drop>
                        <input type="file" data-max-mb="10" name="template" id="new-template" class="file-drop-input"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">
                        <span class="file-drop-idle" data-idle>
                            <span class="file-drop-icon" aria-hidden="true"><i class="ti ti-file-upload"></i></span>
                            <b>{!! __('أفلت القالب هنا أو :link', ['link' => '<u>' . e(__('اختره')) . '</u>']) !!}</b>
                            <small>{{ __('Word أو PDF أو PowerPoint — حتى 10MB') }}</small>
                        </span>
                        <span class="file-drop-picked d-none" data-picked>
                            <span class="file-type" data-picked-type aria-hidden="true"></span>
                            <span class="file-drop-picked-body"><b data-picked-name></b><small><bdi dir="ltr" data-picked-size></bdi></small></span>
                            <span class="file-drop-change">{{ __('تغيير') }}</span>
                        </span>
                    </label>
                    @error('template') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="stage-form-actions">
                @if ($stages->isNotEmpty())
                    <button type="button" class="btn btn-link text-secondary" data-plan-add>{{ __('إلغاء') }}</button>
                @endif
                <button type="submit" class="btn btn-primary" data-loading-text="{{ __('جارٍ الإضافة..') }}">
                    <i class="ti ti-plus me-1" aria-hidden="true"></i>
                    {{ match (true) {
                        ! $activeCount => __('إضافة إلى الخطة'),
                        $activeCount === 1 => __('إضافة إلى :n مجموعة', ['n' => 1]),
                        default => __('إضافة إلى :n مجموعات', ['n' => $activeCount]),
                    } }}
                </button>
            </div>
        </form>
    </section>

    {{-- ===== المسار ===== --}}
    @if ($stages->isNotEmpty())
        <ol class="stage-line">
            @foreach ($stages as $i => $stage)
                @php
                    $p = $progress[$stage->id];
                    $allDone = $p['total'] > 0 && $p['done'] === $p['total'];
                    $days = $stage->due_date ? (int) now()->startOfDay()->diffInDays($stage->due_date, false) : null;
                    $nodeState = $allDone ? 'is-done' : ($p['late'] ? 'is-late' : '');
                    $pct = $p['total'] ? round($p['done'] / $p['total'] * 100) : 0;
                @endphp
                <li class="stage-item {{ $nodeState }}" id="stage-{{ $stage->id }}">
                    <span class="stage-node" aria-hidden="true">
                        @if ($allDone)
                            <i class="ti ti-check"></i>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>

                    <article class="stage-card">
                        {{-- ===== العرض ===== --}}
                        <div class="stage-view">
                            <header class="stage-head">
                                <div class="stage-title">
                                    <h3>{{ $stage->title }}</h3>
                                    @if ($stage->due_date)
                                        <span class="stage-due {{ $days < 0 ? 'is-past' : ($days <= 7 ? 'is-soon' : '') }}">
                                            <i class="ti ti-calendar-event" aria-hidden="true"></i>
                                            {{ $stage->due_date->translatedFormat('j F') }}
                                            <span>·
                                                @if ($days < 0) {{ __('مضى :n يوماً', ['n' => abs($days)]) }}
                                                @elseif ($days === 0) {{ __('اليوم') }}
                                                @else {{ $days === 1 ? __('بعد :n يوم', ['n' => 1]) : __('بعد :n يوماً', ['n' => $days]) }}
                                                @endif
                                            </span>
                                        </span>
                                    @else
                                        <span class="stage-due is-none">{{ __('بلا موعد') }}</span>
                                    @endif
                                </div>
                                <div class="stage-tools">
                                    <button type="button" class="btn-action" data-stage-edit title="{{ __('تعديل') }}" aria-label="{{ __('تعديل :title', ['title' => $stage->title]) }}">
                                        <i class="ti ti-pencil" aria-hidden="true"></i>
                                    </button>
                                    <form action="{{ route('supervisor.plan.destroy', $stage->id) }}" method="POST"
                                        data-confirm="{{ __('حذف «:title» من الخطة؟ تُحذف من المجموعات التي لم تنجزها، ويبقى ما أُنجز.', ['title' => $stage->title]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action--danger" title="{{ __('حذف') }}" aria-label="{{ __('حذف :title', ['title' => $stage->title]) }}">
                                            <i class="ti ti-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </header>

                            @if ($stage->instructions)
                                <p class="stage-instructions">{{ $stage->instructions }}</p>
                            @endif

                            @if ($stage->hasTemplate())
                                @php [$tc, $tl] = $stage->templateBadge(); @endphp
                                <a href="{{ route('stages.template', $stage->id) }}" class="stage-template">
                                    <span class="file-type {{ $tc }}" aria-hidden="true">{{ $tl }}</span>
                                    <span class="stage-template-body">
                                        <b>{{ $stage->template_name }}</b>
                                        <small>{{ __('قالب المرحلة') }} · <bdi dir="ltr">{{ $stage->templateSizeLabel() }}</bdi></small>
                                    </span>
                                    <i class="ti ti-download" aria-hidden="true"></i>
                                </a>
                            @endif

                            {{-- حال المرحلة في كل مجموعة — من أنجز ومن تأخّر، في لمحة --}}
                            @if ($groupsCount)
                                <div class="stage-groups">
                                    <div class="stage-groups-head">
                                        <span>
                                            {!! __('أنجزتها :done من :total', ['done' => '<b>' . e($p['done']) . '</b>', 'total' => e($p['total'])]) !!}
                                            @if ($p['review'] ?? 0)
                                                · <span class="stage-review-note">{{ __(':n بانتظار مراجعتك', ['n' => $p['review']]) }}</span>
                                            @endif
                                            @if ($p['late'])
                                                · <span class="text-danger">{{ __(':n متأخّرة', ['n' => $p['late']]) }}</span>
                                            @endif
                                        </span>
                                        <span class="stage-bar" aria-hidden="true"><span style="width: {{ $pct }}%"></span></span>
                                    </div>
                                    <div class="stage-pills">
                                        @foreach ($projects as $project)
                                            @php
                                                $state = $p['groups'][$project->id];
                                                $label = ['done' => __('أنجزت'), 'submitted' => __('بانتظار مراجعتك'), 'revision' => __('مطلوب تعديل'), 'late' => __('متأخّرة'), 'open' => __('جارية'), 'missing' => __('لم تصلها')][$state];
                                                // اسم القائد لا عنوان المشروع: العناوين المتشابهة تتطابق حين تُقصّ
                                                $leader = $project->group->first()?->student?->name;
                                                $teamName = $leader ? __('فريق :name', ['name' => implode(' ', array_slice(preg_split('/\s+/u', trim($leader)), 0, 2))]) : \Illuminate\Support\Str::limit($project->title, 22);
                                            @endphp
                                            <a href="{{ route('supervisor.projects.show', $project->id) }}#milestones"
                                                class="stage-pill is-{{ $state }}" title="{{ $project->title }} — {{ $label }}">
                                                <i class="ti {{ ['done' => 'ti-check', 'submitted' => 'ti-inbox', 'revision' => 'ti-pencil', 'late' => 'ti-alert-triangle', 'open' => 'ti-clock', 'missing' => 'ti-minus'][$state] }}" aria-hidden="true"></i>
                                                <span>{{ $teamName }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- ===== التعديل: في البطاقة نفسها ===== --}}
                        <form action="{{ route('supervisor.plan.update', $stage->id) }}" method="POST"
                            enctype="multipart/form-data" class="stage-form stage-edit d-none">
                            @csrf
                            <div class="stage-form-grid">
                                <div class="stage-field is-title">
                                    <label class="form-label" for="t-{{ $stage->id }}">{{ __('عنوان المرحلة') }}</label>
                                    <input type="text" id="t-{{ $stage->id }}" name="title" required maxlength="150"
                                        class="form-control" value="{{ $stage->title }}">
                                </div>
                                <div class="stage-field is-date">
                                    <label class="form-label" for="d-{{ $stage->id }}">{{ __('الموعد') }}</label>
                                    <input type="date" id="d-{{ $stage->id }}" name="due_date" class="form-control"
                                        value="{{ $stage->due_date?->format('Y-m-d') }}">
                                </div>
                                <div class="stage-field is-wide">
                                    <label class="form-label" for="i-{{ $stage->id }}">{{ __('تعليمات للطلاب') }}</label>
                                    <textarea id="i-{{ $stage->id }}" name="instructions" rows="2" maxlength="2000"
                                        class="form-control">{{ $stage->instructions }}</textarea>
                                </div>
                                <div class="stage-field is-wide">
                                    <span class="form-label">{{ __('القالب') }}</span>
                                    <label class="file-drop is-compact" for="f-{{ $stage->id }}" data-template-drop>
                                        <input type="file" data-max-mb="10" name="template" id="f-{{ $stage->id }}" class="file-drop-input"
                                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">
                                        <span class="file-drop-idle" data-idle>
                                            <b>{{ $stage->hasTemplate() ? __('استبدل «:name»', ['name' => $stage->template_name]) : __('أضف قالباً') }} — <u>{{ __('اختر ملفاً') }}</u></b>
                                        </span>
                                        <span class="file-drop-picked d-none" data-picked>
                                            <span class="file-type" data-picked-type aria-hidden="true"></span>
                                            <span class="file-drop-picked-body"><b data-picked-name></b><small><bdi dir="ltr" data-picked-size></bdi></small></span>
                                            <span class="file-drop-change">{{ __('تغيير') }}</span>
                                        </span>
                                    </label>
                                    @if ($stage->hasTemplate())
                                        <label class="form-check mt-2">
                                            <input type="checkbox" name="remove_template" value="1" class="form-check-input">
                                            <span class="form-check-label">{{ __('إزالة القالب الحالي') }}</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            <p class="stage-edit-note">
                                <i class="ti ti-info-circle" aria-hidden="true"></i>
                                {{ __('يسري التعديل على المجموعات التي لم تنجز المرحلة، وما أُنجز يبقى كما هو.') }}
                            </p>
                            <div class="stage-form-actions">
                                <button type="button" class="btn btn-link text-secondary" data-stage-edit>{{ __('إلغاء') }}</button>
                                <button type="submit" class="btn btn-primary" data-loading-text="{{ __('جارٍ الحفظ..') }}">{{ __('حفظ') }}</button>
                            </div>
                        </form>
                    </article>
                </li>
            @endforeach
        </ol>
    @endif

@endsection

@push('js')
    <script>
        (function () {
            var compose = document.getElementById('stage-new');

            // «مرحلة جديدة» يفتح البطاقة ويضع المؤشّر في العنوان
            document.querySelectorAll('[data-plan-add]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var open = compose.classList.toggle('d-none') === false;
                    if (open) {
                        compose.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        document.getElementById('new-title').focus();
                    }
                });
            });

            // الاقتراحات تملأ العنوان — تُعدَّل ولا تُكتب من الصفر
            document.querySelectorAll('[data-suggest]').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var input = document.getElementById('new-title');
                    input.value = chip.dataset.suggest;
                    input.focus();
                });
            });

            // التعديل في البطاقة نفسها
            document.querySelectorAll('[data-stage-edit]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('.stage-card');
                    card.querySelector('.stage-view').classList.toggle('d-none');
                    card.querySelector('.stage-edit').classList.toggle('d-none');
                    card.classList.toggle('is-editing');
                });
            });

            // منطقة القالب: الاختيار أو الإفلات يعرض الملف بشارته
            var TYPES = { pdf: ['is-pdf', 'PDF'], doc: ['is-doc', 'DOC'], docx: ['is-doc', 'DOC'], ppt: ['is-ppt', 'PPT'], pptx: ['is-ppt', 'PPT'], xls: ['is-xls', 'XLS'], xlsx: ['is-xls', 'XLS'], png: ['is-img', 'IMG'], jpg: ['is-img', 'IMG'], jpeg: ['is-img', 'IMG'] };
            function size(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : (b >= 1024 ? Math.round(b / 1024) + ' KB' : b + ' B'); }

            document.querySelectorAll('[data-template-drop]').forEach(function (zone) {
                var input = zone.querySelector('input[type=file]');
                function show() {
                    var f = input.files && input.files[0];
                    zone.querySelector('[data-idle]').classList.toggle('d-none', !!f);
                    zone.querySelector('[data-picked]').classList.toggle('d-none', !f);
                    zone.classList.toggle('has-file', !!f);
                    if (!f) return;
                    var ext = (f.name.split('.').pop() || '').toLowerCase();
                    var t = TYPES[ext] || ['is-zip', ext.toUpperCase()];
                    var badge = zone.querySelector('[data-picked-type]');
                    badge.className = 'file-type ' + t[0];
                    badge.textContent = t[1];
                    zone.querySelector('[data-picked-name]').textContent = f.name;
                    zone.querySelector('[data-picked-size]').textContent = size(f.size);
                }
                input.addEventListener('change', show);
                ['dragenter', 'dragover'].forEach(function (ev) {
                    zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-over'); });
                });
                ['dragleave', 'drop'].forEach(function (ev) {
                    zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-over'); });
                });
                zone.addEventListener('drop', function (e) {
                    if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; show(); }
                });
            });
        })();
    </script>
@endpush
