@extends('layouts.admin.admin')
@section('title', 'خطة المراحل')

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">لوحتي</x-crumb>
    <x-crumb>خطة المراحل</x-crumb>
@endsection

@section('content')

    @php
        $groupsCount = $projects->count();
        $activeCount = $projects->where('status', 'accept')->whereNull('grade')->count();
        $suggestions = ['المقترح', 'الفصل الأول: المقدمة', 'الفصل الثاني: الدراسات السابقة', 'التحليل والتصميم', 'التنفيذ', 'العرض النهائي'];
        // \u200E?new=1\u200E: زرّ «مرحلة جديدة» في اللوحة يفتح النموذج مباشرة
        $formOpen = $stages->isEmpty() || $errors->any() || request()->boolean('new');
    @endphp

    <x-page-header title="خطة المراحل"
        subtitle="{{ $semester->label }} · المرحلة تُعرَّف مرّة فتصل إلى {{ $activeCount }} {{ $activeCount === 1 ? 'مجموعة' : 'مجموعات' }} جارية — وكل مجموعة تقبلها لاحقاً">
        @if ($stages->isNotEmpty())
            <x-slot:actions>
                <button type="button" class="btn btn-primary" data-plan-add aria-controls="stage-new">
                    <i class="ti ti-plus me-1" aria-hidden="true"></i>
                    مرحلة جديدة
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
                    <h2>ارسم مسار مجموعاتك مرّة واحدة</h2>
                    <p>
                        كل مرحلة تضيفها هنا — بموعدها وتعليماتها وقالبها — تظهر في كل مجموعاتك، وتصل كل مجموعة
                        تقبلها لاحقاً. وتعديلها يسري على الجميع، وما أُنجز يبقى كما هو.
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('supervisor.plan.store') }}" method="POST" enctype="multipart/form-data" class="stage-form">
            @csrf
            <div class="stage-form-grid">
                <div class="stage-field is-title">
                    <label class="form-label" for="new-title">عنوان المرحلة</label>
                    <input type="text" id="new-title" name="title" required maxlength="150"
                        class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                        placeholder="مثال: الفصل الأول — المقدمة ومشكلة البحث">
                    <div class="stage-suggest" aria-label="اقتراحات">
                        @foreach ($suggestions as $s)
                            <button type="button" class="stage-suggest-chip" data-suggest="{{ $s }}">{{ $s }}</button>
                        @endforeach
                    </div>
                    @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="stage-field is-date">
                    <label class="form-label" for="new-due">الموعد</label>
                    <input type="date" id="new-due" name="due_date" min="{{ now()->toDateString() }}"
                        class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                    @error('due_date') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="stage-field is-wide">
                    <label class="form-label" for="new-instructions">تعليمات للطلاب <small>(اختيارية)</small></label>
                    <textarea id="new-instructions" name="instructions" rows="2" maxlength="2000" class="form-control"
                        placeholder="ما المطلوب في هذه المرحلة، وكيف يُسلَّم">{{ old('instructions') }}</textarea>
                </div>
                <div class="stage-field is-wide">
                    <span class="form-label">القالب <small>(اختياري — يراه الطلاب داخل المرحلة)</small></span>
                    <label class="file-drop is-compact" for="new-template" data-template-drop>
                        <input type="file" name="template" id="new-template" class="file-drop-input"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">
                        <span class="file-drop-idle" data-idle>
                            <span class="file-drop-icon" aria-hidden="true"><i class="ti ti-file-upload"></i></span>
                            <b>أفلت القالب هنا أو <u>اختره</u></b>
                            <small>Word أو PDF أو PowerPoint — حتى 10MB</small>
                        </span>
                        <span class="file-drop-picked d-none" data-picked>
                            <span class="file-type" data-picked-type aria-hidden="true"></span>
                            <span class="file-drop-picked-body"><b data-picked-name></b><small><bdi dir="ltr" data-picked-size></bdi></small></span>
                            <span class="file-drop-change">تغيير</span>
                        </span>
                    </label>
                    @error('template') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="stage-form-actions">
                @if ($stages->isNotEmpty())
                    <button type="button" class="btn btn-link text-secondary" data-plan-add>إلغاء</button>
                @endif
                <button type="submit" class="btn btn-primary" data-loading-text="جارٍ الإضافة..">
                    <i class="ti ti-plus me-1" aria-hidden="true"></i>
                    إضافة إلى {{ $activeCount ? $activeCount . ' ' . ($activeCount === 1 ? 'مجموعة' : 'مجموعات') : 'الخطة' }}
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
                                                @if ($days < 0) مضى {{ abs($days) }} يوماً
                                                @elseif ($days === 0) اليوم
                                                @else بعد {{ $days }} {{ $days === 1 ? 'يوم' : 'يوماً' }}
                                                @endif
                                            </span>
                                        </span>
                                    @else
                                        <span class="stage-due is-none">بلا موعد</span>
                                    @endif
                                </div>
                                <div class="stage-tools">
                                    <button type="button" class="btn-action" data-stage-edit title="تعديل" aria-label="تعديل {{ $stage->title }}">
                                        <i class="ti ti-pencil" aria-hidden="true"></i>
                                    </button>
                                    <form action="{{ route('supervisor.plan.destroy', $stage->id) }}" method="POST"
                                        onsubmit="return confirm({{ Js::from('حذف «' . $stage->title . '» من الخطة؟ تُحذف من المجموعات التي لم تنجزها، ويبقى ما أُنجز.') }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action--danger" title="حذف" aria-label="حذف {{ $stage->title }}">
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
                                        <small>قالب المرحلة · <bdi dir="ltr">{{ $stage->templateSizeLabel() }}</bdi></small>
                                    </span>
                                    <i class="ti ti-download" aria-hidden="true"></i>
                                </a>
                            @endif

                            {{-- حال المرحلة في كل مجموعة — من أنجز ومن تأخّر، في لمحة --}}
                            @if ($groupsCount)
                                <div class="stage-groups">
                                    <div class="stage-groups-head">
                                        <span>
                                            أنجزتها <b>{{ $p['done'] }}</b> من {{ $p['total'] }}
                                            @if ($p['review'] ?? 0)
                                                · <span class="stage-review-note">{{ $p['review'] }} بانتظار مراجعتك</span>
                                            @endif
                                            @if ($p['late'])
                                                · <span class="text-danger">{{ $p['late'] }} متأخّرة</span>
                                            @endif
                                        </span>
                                        <span class="stage-bar" aria-hidden="true"><span style="width: {{ $pct }}%"></span></span>
                                    </div>
                                    <div class="stage-pills">
                                        @foreach ($projects as $project)
                                            @php
                                                $state = $p['groups'][$project->id];
                                                $label = ['done' => 'أنجزت', 'submitted' => 'بانتظار مراجعتك', 'revision' => 'مطلوب تعديل', 'late' => 'متأخّرة', 'open' => 'جارية', 'missing' => 'لم تصلها'][$state];
                                                // اسم القائد لا عنوان المشروع: العناوين المتشابهة تتطابق حين تُقصّ
                                                $leader = $project->group->first()?->student?->name;
                                                $teamName = $leader ? 'فريق ' . implode(' ', array_slice(preg_split('/\s+/u', trim($leader)), 0, 2)) : \Illuminate\Support\Str::limit($project->title, 22);
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
                                    <label class="form-label" for="t-{{ $stage->id }}">عنوان المرحلة</label>
                                    <input type="text" id="t-{{ $stage->id }}" name="title" required maxlength="150"
                                        class="form-control" value="{{ $stage->title }}">
                                </div>
                                <div class="stage-field is-date">
                                    <label class="form-label" for="d-{{ $stage->id }}">الموعد</label>
                                    <input type="date" id="d-{{ $stage->id }}" name="due_date" class="form-control"
                                        value="{{ $stage->due_date?->format('Y-m-d') }}">
                                </div>
                                <div class="stage-field is-wide">
                                    <label class="form-label" for="i-{{ $stage->id }}">تعليمات للطلاب</label>
                                    <textarea id="i-{{ $stage->id }}" name="instructions" rows="2" maxlength="2000"
                                        class="form-control">{{ $stage->instructions }}</textarea>
                                </div>
                                <div class="stage-field is-wide">
                                    <span class="form-label">القالب</span>
                                    <label class="file-drop is-compact" for="f-{{ $stage->id }}" data-template-drop>
                                        <input type="file" name="template" id="f-{{ $stage->id }}" class="file-drop-input"
                                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">
                                        <span class="file-drop-idle" data-idle>
                                            <b>{{ $stage->hasTemplate() ? 'استبدل «' . $stage->template_name . '»' : 'أضف قالباً' }} — <u>اختر ملفاً</u></b>
                                        </span>
                                        <span class="file-drop-picked d-none" data-picked>
                                            <span class="file-type" data-picked-type aria-hidden="true"></span>
                                            <span class="file-drop-picked-body"><b data-picked-name></b><small><bdi dir="ltr" data-picked-size></bdi></small></span>
                                            <span class="file-drop-change">تغيير</span>
                                        </span>
                                    </label>
                                    @if ($stage->hasTemplate())
                                        <label class="form-check mt-2">
                                            <input type="checkbox" name="remove_template" value="1" class="form-check-input">
                                            <span class="form-check-label">إزالة القالب الحالي</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            <p class="stage-edit-note">
                                <i class="ti ti-info-circle" aria-hidden="true"></i>
                                يسري التعديل على المجموعات التي لم تنجز المرحلة، وما أُنجز يبقى كما هو.
                            </p>
                            <div class="stage-form-actions">
                                <button type="button" class="btn btn-link text-secondary" data-stage-edit>إلغاء</button>
                                <button type="submit" class="btn btn-primary" data-loading-text="جارٍ الحفظ..">حفظ</button>
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
