@extends('layouts.admin.admin')
@section('title', 'تفاصيل المشروع')

@section('crumbs')
    <x-crumb :href="route('admin.groups.index')">المجموعات</x-crumb>
    <x-crumb>{{ $project->title }}</x-crumb>
@endsection

{{--
    صفحة المجموعة عند الإدارة — قراءة ورقابة.

    كانت ألواحاً متساوية الوزن متراصّة: شريط مسار وأربع خلايا، ثم التقييم،
    ثم البيانات والفريق، ثم المراحل والملفات والنقاش في آخر صفحة طويلة. وكانت
    تتناقض: مشروع مقيَّم بخطوة «التقييم» جارية و«المتبقّي ١٢ يوماً».
    الآن: رأس مضغوط، وشريط انتباه حين يوجد ما يستدعيه، ثم عمودان — العمل في
    تبويبات، والخلاصة (الإنجاز، التقييم، الفريق، التفاصيل) في جانب ثابت.
--}}

@section('content')

    @php
        $graded = ! is_null($project->grade);
        $rejected = $project->status === 'reject';
        // الأقسام التشغيلية لا معنى لها قبل القبول — العمل لم يبدأ
        $started = in_array($project->status, ['accept', 'complete'], true);
        $sem = $project->semester?->parts();

        $progress = $project->progress;
        $msTotal = $project->milestones->count();
        $msDone = $project->milestones->where('is_done', true)->count();

        // مسار المشروع: الخطوة الجارية بالرقم، وما قبلها منجز — والمقيَّم كلّه منجز
        $steps = [
            ['label' => 'تقديم الطلب', 'meta' => $project->created_at?->format('Y-m-d')],
            ['label' => 'موافقة المشرف', 'meta' => null],
            ['label' => 'التنفيذ والمتابعة', 'meta' => null],
            ['label' => 'التقييم', 'meta' => $project->evaluated_at?->format('Y-m-d')],
        ];
        $current = match ($project->status) {
            'request' => 2,
            'accept' => 3,
            'complete' => $graded ? 5 : 4,
            default => 0,
        };

        // سطر الوقت تحت الحلقة — بحسب الحالة، لا «متبقٍّ» لمشروع انتهى
        $daysLeft = $project->days_left;
        [$timeText, $timeTone] = match (true) {
            $rejected => ['رفضه المشرف', 'is-danger'],
            $project->status === 'request' => ['بانتظار ردّ المشرف', ''],
            $project->status === 'complete' && $graded => ['اكتمل وقُيّم ' . $project->evaluated_at?->format('Y-m-d'), 'is-success'],
            $project->status === 'complete' => ['اكتمل — بانتظار التقييم', ''],
            is_null($daysLeft) => ['لم يُحدَّد موعد نهائي', ''],
            $daysLeft < 0 => ['تأخّر ' . abs($daysLeft) . ' يوماً عن الموعد', 'is-danger'],
            $daysLeft === 0 => ['الموعد النهائي اليوم', 'is-warn'],
            default => ['المتبقّي ' . $daysLeft . ' يوماً للموعد النهائي', ''],
        };

        $tabs = $started ? [
            'milestones' => ['المراحل', 'ti-list-check', $msTotal ? $msDone . '/' . $msTotal : null],
            'files' => ['الملفات', 'ti-files', $project->files->count() ?: null],
            'discussion' => ['النقاش مع المشرف', 'ti-messages', $project->comments->count() ?: null],
            'history' => ['سجلّ المشروع', 'ti-history', null],
        ] : [];
    @endphp

    <x-page-header title="{{ $project->title }}">
        <x-slot:actions>
            <a href="{{ route('admin.groups.edit', $project->id) }}" class="btn btn-outline-primary">
                <i class="ti ti-pencil me-1" aria-hidden="true"></i>
                تعديل المجموعة
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ هوية المشروع في سطر — كانت عنواناً فرعياً باسم الفصل خاماً ═══ --}}
    <div class="pg-meta mb-4">
        <x-status-badge :status="$project->status" />
        <span class="pg-chip">
            <i class="ti ti-category" aria-hidden="true"></i>
            {{ $project->project_type->name ?? 'بلا نوع' }}
        </span>
        @if ($sem)
            <span class="pg-chip">
                <i class="ti ti-calendar" aria-hidden="true"></i>
                {{ $sem['term'] }}@if ($sem['year'])<span class="pg-chip-sep">·</span><bdi dir="ltr">{{ $sem['year'] }}</bdi>@endif
            </span>
        @endif
        @if ($project->supervisor->name)
            <span class="pg-chip is-person">
                <x-avatar :user="$project->supervisor" class="ctx-avatar pg-chip-avatar" />
                {{ $project->supervisor->name }}
            </span>
        @endif
    </div>

    {{-- ═══ يحتاج انتباهاً — بتعريفات «متابعة الفرق» نفسها، ولا شيء للسليم ═══ --}}
    @if (! empty($issues))
        {{-- لوح واحد بخلايا متجاورة — لا شرائط مكدّسة بقدر عدد المشكلات --}}
        <section class="pg-alerts mb-4" aria-labelledby="pg-alerts-title">
            <h2 class="pg-alerts-title" id="pg-alerts-title">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                يحتاج انتباهاً
            </h2>
            <ul class="pg-alerts-list">
                @foreach ($issues as $key => [$title, $text, $icon])
                    <li class="pg-alert">
                        <i class="ti {{ $icon }}" aria-hidden="true"></i>
                        <span>
                            <b>{{ $title }}</b>
                            <small>{{ $text }}</small>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="pg-layout">
        {{-- ═══════════ العمود الرئيسي ═══════════ --}}
        <div class="pg-main">
            @if (! $started)
                {{-- قبل القبول لا مراحل ولا ملفات — لوح واحد يقول السبب --}}
                <section class="stage-note">
                    <i class="ti {{ $rejected ? 'ti-circle-x' : 'ti-hourglass-high' }}" aria-hidden="true"></i>
                    <div>
                        <b>{{ $rejected ? 'المشروع مرفوض' : 'المشروع بانتظار رد المشرف' }}</b>
                        <p>
                            المراحل والملفات والنقاش تبدأ بعد اعتماد المشرف للمشروع —
                            ولهذا لا يوجد منها شيء الآن.
                        </p>
                    </div>
                </section>

                @if ($history->count())
                    <div class="dist-panel mt-4">
                        <div class="dist-head">
                            <span>سجلّ المشروع</span>
                            <a href="{{ route('admin.audit.index') }}">السجلّ كاملاً</a>
                        </div>
                        <div class="audit-list">
                            @foreach ($history as $log)
                                <x-audit-row :log="$log" dated />
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                {{-- تبويبات: بلا JavaScript تظهر اللوحات كلّها متتالية --}}
                <div class="pg-tabs" data-tabs>
                    <div class="pg-tablist" role="tablist" aria-label="أقسام المشروع">
                        @foreach ($tabs as $key => [$label, $icon, $count])
                            <button type="button" role="tab" id="tab-btn-{{ $key }}" aria-controls="tab-{{ $key }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-tab="{{ $key }}">
                                <i class="ti {{ $icon }}" aria-hidden="true"></i>
                                {{ $label }}
                                @if ($count)
                                    <span class="pg-count">{{ $count }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <section class="pg-panel" id="tab-milestones" role="tabpanel" aria-labelledby="tab-btn-milestones">
                        @include('dashboard.project._milestones', ['project' => $project])
                    </section>

                    <section class="pg-panel" id="tab-files" role="tabpanel" aria-labelledby="tab-btn-files">
                        <div class="dist-panel">
                            <div class="dist-head">
                                <span>ملفات المشروع</span>
                                <span class="dist-head-note">{{ $project->files->count() }} ملفاً</span>
                            </div>
                            @forelse ($project->files as $file)
                                <div class="file-row">
                                    <i class="ti ti-file-text file-icon" aria-hidden="true"></i>
                                    <span class="file-body">
                                        <span class="file-name">{{ $file->title }}</span>
                                        <span class="file-meta">
                                            {{ $file->human_size }}
                                            · {{ $file->uploader_type === \App\Models\Supervisor::class ? 'المشرف' : ($file->uploader->name ?? 'طالب') }}
                                            · {{ $file->created_at->format('Y-m-d') }}
                                        </span>
                                    </span>
                                    <a href="{{ route('files.download', ['file' => $file->id]) }}"
                                        class="btn-action" title="تنزيل" aria-label="تنزيل {{ $file->title }}">
                                        <i class="ti ti-download" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @empty
                                <x-empty-state icon="ti-files" title="لا ملفات بعد"
                                    text="ما يرفعه الفريق أو المشرف من ملفات يظهر هنا." class="is-inline" />
                            @endforelse
                        </div>
                    </section>

                    <section class="pg-panel" id="tab-discussion" role="tabpanel" aria-labelledby="tab-btn-discussion">
                        <div class="dist-panel">
                            <div class="dist-head">
                                <span>النقاش بين الفريق والمشرف</span>
                                {{-- نقاش الفريق الداخلي لا يصل الإدارة — يُقال صراحةً --}}
                                <span class="dist-head-note">عرض فقط · نقاش الفريق الخاص لا يظهر هنا</span>
                            </div>
                            @forelse ($project->comments as $comment)
                                <div class="cmt-row">
                                    <span class="ctx-avatar {{ $comment->is_supervisor ? 'is-supervisor' : '' }}">
                                        {{ mb_substr(str_replace('د.', '', $comment->author->name ?? '؟'), 0, 2) }}
                                    </span>
                                    <div class="cmt-body">
                                        <div class="cmt-head">
                                            <b>{{ $comment->author->name ?? 'مستخدم' }}</b>
                                            <span class="cmt-role">{{ $comment->is_supervisor ? 'مشرف' : 'طالب' }}</span>
                                            <span class="cmt-time">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="cmt-text">{{ $comment->body }}</p>
                                    </div>
                                </div>
                            @empty
                                <x-empty-state icon="ti-messages" title="لا رسائل بعد"
                                    text="لم يتبادل الفريق والمشرف رسائل في هذا المشروع." class="is-inline" />
                            @endforelse
                        </div>
                    </section>

                    <section class="pg-panel" id="tab-history" role="tabpanel" aria-labelledby="tab-btn-history">
                        {{-- من غيّر الدرجة ومتى، ومن فكّ اعتمادها ولماذا — مع سياق المشروع --}}
                        <div class="dist-panel">
                            <div class="dist-head">
                                <span>سجلّ المشروع</span>
                                <a href="{{ route('admin.audit.index') }}">السجلّ كاملاً</a>
                            </div>
                            @if ($history->count())
                                <div class="audit-list">
                                    @foreach ($history as $log)
                                        <x-audit-row :log="$log" dated />
                                    @endforeach
                                </div>
                            @else
                                <x-empty-state icon="ti-history" title="لا أحداث مسجّلة"
                                    text="الدرجات والتسليمات والأدوار وحذف المراحل تُسجَّل هنا." class="is-inline" />
                            @endif
                        </div>
                    </section>
                </div>
            @endif
        </div>

        {{-- ═══════════ العمود الجانبي ═══════════ --}}
        <aside class="pg-side">
            {{-- ═══ الإنجاز والمسار ═══ --}}
            <section class="pg-card is-summary">
                @if ($started)
                    <div class="pg-progress">
                        <span class="pct-ring is-xl {{ is_null($progress) ? 'is-empty' : '' }}" style="--p: {{ $progress ?? 0 }}">
                            <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" /><circle cx="20" cy="20" r="17" pathLength="100" /></svg>
                            <b>{{ is_null($progress) ? '—' : $progress . '%' }}</b>
                        </span>
                        <div>
                            <span class="pg-progress-title">نسبة الإنجاز</span>
                            <span class="pg-progress-sub">
                                {{ $msTotal ? $msDone . ' من ' . $msTotal . ' مراحل معتمدة' : 'لم تُضَف مراحل بعد' }}
                            </span>
                        </div>
                    </div>
                @endif

                <p class="pg-time {{ $timeTone }}">
                    <i class="ti {{ $timeTone === 'is-success' ? 'ti-circle-check' : ($timeTone === 'is-danger' ? 'ti-alert-circle' : 'ti-clock') }}" aria-hidden="true"></i>
                    {{ $timeText }}
                </p>

                @unless ($rejected)
                    <ol class="pg-steps">
                        @foreach ($steps as $i => $step)
                            @php $n = $i + 1; @endphp
                            <li class="{{ $n < $current ? 'is-done' : ($n === $current ? 'is-current' : '') }}">
                                <span class="pg-step-node" aria-hidden="true">
                                    @if ($n < $current)<i class="ti ti-check"></i>@endif
                                </span>
                                <span class="pg-step-label">{{ $step['label'] }}</span>
                                @if ($step['meta'] && $n < $current)
                                    <span class="pg-step-meta">{{ $step['meta'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endunless
            </section>

            {{-- ═══ التقييم ═══ --}}
            @if ($graded)
                <section class="pg-card pg-grade is-summary">
                    <div class="pg-grade-top">
                        <span class="pg-grade-num">
                            {{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}<small>/100</small>
                        </span>
                        <span class="pg-grade-label">{{ $project->grade_label }}</span>
                        @if ($project->isGradeLocked())
                            <span class="grade-locked">
                                <i class="ti ti-lock-check" aria-hidden="true"></i>
                                معتمدة
                            </span>
                        @endif
                    </div>
                    @if ($project->evaluation_note)
                        <p class="pg-grade-note">{{ $project->evaluation_note }}</p>
                    @endif
                    <div class="pg-grade-meta">
                        قُيّم {{ $project->evaluated_at?->format('Y-m-d') }}
                        @if ($project->graded_by && $project->grader->name)
                            · {{ $project->grader->name }}
                        @endif
                        @if ($project->isGradeLocked())
                            · اعتُمد {{ $project->grade_locked_at?->format('Y-m-d') }}
                        @endif
                    </div>
                    @if ($project->isGradeLocked())
                        {{-- مخرج الأدمن حين تُعتمد درجة خطأً — بسبب مكتوب --}}
                        <button type="button" class="btn btn-ghost-secondary btn-sm pg-grade-unlock"
                            data-bs-toggle="modal" data-bs-target="#unlockGradeModal">
                            <i class="ti ti-lock-open me-1" aria-hidden="true"></i>
                            فكّ الاعتماد
                        </button>
                    @endif
                </section>
            @endif

            {{-- ═══ الفريق ═══ --}}
            <section class="pg-card">
                <h2 class="pg-card-title">
                    الفريق
                    <span>{{ $project->group->count() }} أعضاء</span>
                </h2>
                <ul class="pg-team">
                    @foreach ($project->group as $group)
                        <li>
                            <x-avatar :user="$group->student" class="ctx-avatar" />
                            <span class="pg-member">
                                <span class="pg-member-name">
                                    {{ $group->student?->name ?? 'طالب محذوف' }}
                                    @if ($group->type === 'leader')
                                        <span class="ctx-tag">قائد</span>
                                    @endif
                                </span>
                                <span class="pg-member-meta">
                                    <bdi dir="ltr">{{ $group->student?->university_id ?? '—' }}</bdi>
                                    · {{ $group->student?->specialize->name ?? '—' }}
                                </span>
                                @if ($started)
                                    {{-- غياب الدور يُذكر هادئاً في مشروع جارٍ، ولا يُذكر في المؤرشف --}}
                                    @if ($group->roles->isNotEmpty())
                                        <span class="role-chips">
                                            @foreach ($group->roles as $role)
                                                @php $meta = $role->meta(); @endphp
                                                <span class="role-chip" style="--h: {{ $meta['hue'] }}">
                                                    <i class="ti {{ $meta['icon'] }}" aria-hidden="true"></i>{{ $meta['label'] }}
                                                </span>
                                            @endforeach
                                        </span>
                                    @elseif (! $graded)
                                        <span class="role-chips"><span class="ctx-tag is-neutral">بلا دور</span></span>
                                    @endif
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- ═══ التفاصيل ═══ --}}
            <section class="pg-card">
                <h2 class="pg-card-title">التفاصيل</h2>
                <dl class="pg-dl">
                    <dt>المشرف</dt>
                    <dd>
                        {{ $project->supervisor->name ?: '—' }}
                        @if ($project->supervisor->specialize->name ?? null)
                            <small>{{ $project->supervisor->specialize->name }}</small>
                        @endif
                    </dd>
                    <dt>التقديم</dt>
                    <dd>{{ $project->created_at->format('Y-m-d') }}</dd>
                    <dt>الموعد النهائي</dt>
                    <dd>{{ $project->date_line ? $project->date_line->format('Y-m-d') : 'لم يُحدَّد' }}</dd>
                </dl>
                @if ($project->description)
                    @if (mb_strlen($project->description) > 180)
                        <details class="pg-desc">
                            <summary>
                                <span class="pg-desc-label">وصف المشروع</span>
                                <span class="pg-desc-preview">{{ \Illuminate\Support\Str::limit($project->description, 140) }}</span>
                                <span class="pg-desc-more">عرض الكل</span>
                            </summary>
                            <p>{{ $project->description }}</p>
                        </details>
                    @else
                        <div class="pg-desc">
                            <span class="pg-desc-label">وصف المشروع</span>
                            <p>{{ $project->description }}</p>
                        </div>
                    @endif
                @endif
            </section>
        </aside>
    </div>

    @if ($graded && $project->isGradeLocked())
        <div class="modal fade" id="unlockGradeModal" tabindex="-1" aria-labelledby="unlockGradeLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unlockGradeLabel">فكّ اعتماد الدرجة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>

                    <form action="{{ route('admin.groups.grade.unlock', $project->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-secondary mb-3">
                                سيستطيع المشرف تعديل الدرجة ثانيةً. الدرجة الحالية
                                <b>{{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}</b>
                                تبقى كما هي حتى يغيّرها.
                            </p>

                            <div class="mb-2">
                                <label class="form-label required" for="unlock-reason">سبب فكّ الاعتماد</label>
                                <textarea id="unlock-reason" name="reason" rows="3" required minlength="10"
                                    maxlength="500" class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="مثال: خطأ في احتساب درجة المناقشة، اعتُمدت قبل رفع التقرير النهائي…">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- السبب هو ما يُقرأ عند التنازع، لا حقل شكلي --}}
                            <div class="form-hint">
                                يُحفظ في سجلّ التدقيق باسمك وتاريخه، ويظهر في تاريخ هذا المشروع.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-lock-open me-1" aria-hidden="true"></i>
                                فكّ الاعتماد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('js')
    <script>
        // إعادة فتح نافذة فكّ الاعتماد عند فشل التحقّق — وإلا عاد
        // الأدمن إلى صفحة تبدو كأن شيئاً لم يحدث ورسالة الخطأ مخفيّة
        @if ($errors->has('reason'))
            new bootstrap.Modal(document.getElementById('unlockGradeModal')).show();
        @endif

        // التبويبات: المختار في الرابط (\u200E#tab-files\u200E) فيعمل الرجوع والمشاركة،
        // والأسهم تتنقّل بينها. بلا هذا السكربت تظهر اللوحات كلّها.
        (function () {
            var root = document.querySelector('[data-tabs]');
            if (!root) return;
            var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
            root.classList.add('is-enhanced');

            function show(key, focus) {
                var found = tabs.some(function (t) { return t.dataset.tab === key; });
                if (!found) key = tabs[0].dataset.tab;
                tabs.forEach(function (t) {
                    var on = t.dataset.tab === key;
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    t.tabIndex = on ? 0 : -1;
                    document.getElementById('tab-' + t.dataset.tab).hidden = !on;
                    if (on && focus) t.focus();
                });
            }

            tabs.forEach(function (t, i) {
                t.addEventListener('click', function () {
                    show(t.dataset.tab);
                    history.replaceState(null, '', '#tab-' + t.dataset.tab);
                });
                t.addEventListener('keydown', function (e) {
                    // RTL: السهم الأيسر إلى التالي
                    var step = { ArrowLeft: 1, ArrowRight: -1, Home: -i, End: tabs.length - 1 - i }[e.key];
                    if (step === undefined) return;
                    e.preventDefault();
                    var next = tabs[(i + step + tabs.length) % tabs.length];
                    show(next.dataset.tab, true);
                    history.replaceState(null, '', '#tab-' + next.dataset.tab);
                });
            });

            // روابط قديمة إلى \u200E#milestone-12\u200E تفتح تبويب المراحل
            function fromHash() {
                var hash = location.hash.replace('#', '');
                show(hash.indexOf('tab-') === 0 ? hash.slice(4) : 'milestones');
            }
            // الرجوع في المتصفّح يغيّر الرابط بلا تحميل — التبويب يتبعه
            window.addEventListener('hashchange', fromHash);
            fromHash();
        })();
    </script>
@endpush
