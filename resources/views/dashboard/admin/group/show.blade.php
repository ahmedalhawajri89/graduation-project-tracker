@extends('layouts.admin.admin')
@section('title', 'تفاصيل المشروع')

@section('crumbs')
    <x-crumb :href="route('admin.groups.index')">المجموعات</x-crumb>
    <x-crumb>{{ $project->title }}</x-crumb>
@endsection

@section('content')

    @php
        $daysLeft = $project->days_left;
        $graded = ! is_null($project->grade);

        // موضع المشروع على مساره — الصفحة كانت تعرض شارة حالة فقط، فلا
        // يُعرف ما مضى وما بقي
        $stages = [
            ['key' => 'submit',   'label' => 'تقديم الطلب',      'meta' => $project->created_at?->format('Y-m-d')],
            ['key' => 'approve',  'label' => 'موافقة المشرف',    'meta' => null],
            ['key' => 'execute',  'label' => 'التنفيذ والمتابعة', 'meta' => null],
            ['key' => 'evaluate', 'label' => 'التقييم',          'meta' => $project->evaluated_at?->format('Y-m-d')],
        ];
        $reached = match ($project->status) {
            'request'  => 1,
            'accept'   => 2,
            'complete' => $graded ? 4 : 3,
            default    => 0,
        };
        $rejected = $project->status === 'reject';

        // الأقسام التشغيلية لا معنى لها قبل القبول — العمل لم يبدأ
        $started = in_array($project->status, ['accept', 'complete'], true);
    @endphp

    <x-page-header title="{{ $project->title }}"
        subtitle="{{ $project->project_type->name }} · {{ $project->semester->name }}">
        <x-slot:actions>
            <a href="{{ route('admin.groups.edit', $project->id) }}" class="btn btn-outline-primary">
                <i class="ti ti-pencil me-1" aria-hidden="true"></i>
                تعديل المجموعة
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ المسار والوقائع في لوح واحد ═══
         كانت أربع بطاقات مؤشرات بارتفاع كبير لأربعة أرقام أحدها فارغ،
         بأيقونات بنفسجية وسماوية خارج اللوحة. --}}
    <section class="proj-panel mb-4">
        <div class="proj-rail {{ $rejected ? 'is-rejected' : '' }}">
            @if ($rejected)
                <div class="proj-rejected">
                    <i class="ti ti-circle-x" aria-hidden="true"></i>
                    <span>
                        <b>رُفض هذا المشروع</b>
                        لم يُعتمد من المشرف، فلا مراحل ولا ملفات له.
                    </span>
                </div>
            @else
                <ol class="rail-steps">
                    @foreach ($stages as $i => $stage)
                        @php $n = $i + 1; @endphp
                        <li class="rail-step {{ $n < $reached ? 'is-done' : ($n === $reached ? 'is-current' : '') }}">
                            <span class="rail-node" aria-hidden="true">
                                @if ($n < $reached)
                                    <i class="ti ti-check"></i>
                                @endif
                            </span>
                            <span class="rail-label">{{ $stage['label'] }}</span>
                            @if ($stage['meta'])
                                <span class="rail-meta">{{ $stage['meta'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        <div class="proj-facts">
            <div class="fact-cell">
                <span class="fact-label">الحالة</span>
                <span class="fact-value"><x-status-badge :status="$project->status" /></span>
            </div>
            <div class="fact-cell">
                <span class="fact-label">أعضاء الفريق</span>
                <span class="fact-value">{{ $project->group->count() }}</span>
            </div>
            @if ($started)
                <div class="fact-cell">
                    <span class="fact-label">نسبة الإنجاز</span>
                    <span class="fact-value">{{ is_null($project->progress) ? '—' : $project->progress . '%' }}</span>
                </div>
                {{-- كانت التسمية «الموعد النهائي» والقيمة «٨١ يوم» —
                     التسمية تعد بتاريخ والقيمة تعطي مدّة. التاريخ نفسه
                     معروض في لوح البيانات، وما يهمّ هنا هو ما تبقّى. --}}
                <div class="fact-cell">
                    <span class="fact-label">المتبقّي للتسليم</span>
                    <span class="fact-value {{ ! is_null($daysLeft) && $daysLeft < 0 ? 'is-late' : '' }}">
                        @if (is_null($daysLeft))
                            لم يُحدَّد
                        @elseif ($daysLeft < 0)
                            تأخّر {{ abs($daysLeft) }} يوماً
                        @elseif ($daysLeft === 0)
                            اليوم
                        @else
                            {{ $daysLeft }} يوماً
                        @endif
                    </span>
                </div>
            @else
                <div class="fact-cell">
                    <span class="fact-label">تاريخ التقديم</span>
                    <span class="fact-value">{{ $project->created_at->format('Y-m-d') }}</span>
                </div>
            @endif
        </div>
    </section>

    {{-- ═══ التقييم ═══ --}}
    @if ($graded)
        {{-- كانت بطاقة بتدرّج بنفسجي مكتوب في style مباشرة --}}
        <section class="grade-panel mb-4">
            <div class="grade-score">
                <span class="grade-num">{{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}</span>
                <span class="grade-of">من 100</span>
            </div>
            <div class="grade-body">
                <div class="grade-head">
                    التقييم النهائي
                    <span class="grade-label">{{ $project->grade_label }}</span>
                    @if ($project->isGradeLocked())
                        <span class="grade-locked">
                            <i class="ti ti-lock-check" aria-hidden="true"></i>
                            معتمدة
                        </span>
                    @endif
                </div>
                @if ($project->evaluation_note)
                    <p class="grade-note">{{ $project->evaluation_note }}</p>
                @endif
                <div class="grade-date">
                    قُيّم بتاريخ {{ $project->evaluated_at?->format('Y-m-d') }}
                    @if ($project->graded_by)
                        · بواسطة {{ $project->grader->name }}
                    @endif
                    @if ($project->isGradeLocked())
                        · اعتُمد {{ $project->grade_locked_at?->format('Y-m-d') }}
                    @endif
                </div>
            </div>

            @if ($project->isGradeLocked())
                {{-- مخرج الأدمن حين تُعتمد درجة خطأً — بسبب مكتوب --}}
                <button type="button" class="btn btn-ghost-secondary btn-sm grade-unlock-btn"
                    data-bs-toggle="modal" data-bs-target="#unlockGradeModal">
                    <i class="ti ti-lock-open me-1" aria-hidden="true"></i>
                    فكّ الاعتماد
                </button>
            @endif
        </section>

        @if ($project->isGradeLocked())
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

                                {{-- السبب هو ما يُقرأ عند التنازع، لا حقل
                                     شكلي — فيُقال ذلك صراحةً --}}
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
    @endif

    <div class="dist-grid mb-4">
        {{-- ═══ بيانات المشروع ═══ --}}
        <div class="dist-panel">
            <div class="dist-head"><span>بيانات المشروع</span></div>
            <dl class="proj-dl">
                <dt>المشرف</dt>
                <dd>{{ $project->supervisor->name ?: '—' }}</dd>
                <dt>تخصص المشرف</dt>
                <dd>{{ $project->supervisor->specialize->name ?? '—' }}</dd>
                <dt>تاريخ التقديم</dt>
                <dd>{{ $project->created_at->format('Y-m-d') }}</dd>
                <dt>الموعد النهائي</dt>
                <dd>{{ $project->date_line ? $project->date_line->format('Y-m-d') : 'لم يُحدَّد' }}</dd>
            </dl>
            @if ($project->description)
                <div class="proj-desc">
                    <span class="proj-desc-label">وصف المشروع</span>
                    <p>{{ $project->description }}</p>
                </div>
            @endif
        </div>

        {{-- ═══ الفريق ═══
             كان جدولاً بأربعة رؤوس لصفّين — الرؤوس أثقل من البيانات --}}
        <div class="dist-panel">
            <div class="dist-head">
                <span>فريق المشروع</span>
                <span class="dist-head-note">{{ $project->group->count() }} أعضاء</span>
            </div>
            @foreach ($project->group as $group)
                <div class="ctx-person">
                    <x-avatar :user="$group->student" class="ctx-avatar" />
                    <span class="ctx-person-body">
                        <span class="ctx-person-name">
                            {{ $group->student?->name ?? 'طالب محذوف' }}
                            @if ($group->type === 'leader')
                                <span class="ctx-tag">قائد</span>
                            @endif
                        </span>
                        <span class="ctx-person-meta">
                            <span dir="ltr">{{ $group->student?->university_id ?? '—' }}</span>
                            · {{ $group->student?->specialize->name ?? '—' }}
                        </span>
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ الأقسام التشغيلية ═══ --}}
    @if (! $started)
        {{-- كانت ثلاثة صناديق فارغة تقول «لا يوجد» بلا تفسير. السبب
             واضح من الحالة: العمل لم يبدأ. لوح واحد يقوله. --}}
        <section class="stage-note">
            <i class="ti ti-hourglass-high" aria-hidden="true"></i>
            <div>
                <b>
                    @if ($rejected)
                        المشروع مرفوض
                    @else
                        المشروع بانتظار رد المشرف
                    @endif
                </b>
                <p>
                    المراحل والملفات والنقاش تبدأ بعد اعتماد المشرف للمشروع —
                    ولهذا لا يوجد منها شيء الآن.
                </p>
            </div>
        </section>
    @else
        <div class="dist-grid mb-4">
            {{-- ═══ المراحل ═══ --}}
            <div class="dist-panel">
                <div class="dist-head">
                    <span>مراحل المشروع</span>
                    @if (! is_null($project->progress))
                        <span class="dist-head-note">{{ $project->progress }}% منجز</span>
                    @endif
                </div>

                @if ($project->milestones->count())
                    @if (! is_null($project->progress))
                        <div class="ms-progress">
                            <span style="width: {{ $project->progress }}%"></span>
                        </div>
                    @endif
                    @foreach ($project->milestones as $milestone)
                        <div class="ms-row {{ $milestone->is_done ? 'is-done' : '' }}">
                            <span class="ms-check" aria-hidden="true">
                                @if ($milestone->is_done)<i class="ti ti-check"></i>@endif
                            </span>
                            <span class="ms-body">
                                <span class="ms-title">{{ $milestone->title }}</span>
                                @if ($milestone->due_date)
                                    <span class="ms-due">الاستحقاق: {{ $milestone->due_date->format('Y-m-d') }}</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                @else
                    <p class="dist-empty">لم يضِف المشرف مراحل لهذا المشروع بعد.</p>
                @endif
            </div>

            {{-- ═══ الملفات ═══ --}}
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
                    <p class="dist-empty">لم يُرفع أي ملف بعد.</p>
                @endforelse
            </div>
        </div>

        {{-- ═══ النقاش ═══ --}}
        <div class="dist-panel">
            <div class="dist-head">
                <span>نقاش المشروع</span>
                <span class="dist-head-note">{{ $project->comments->count() }} تعليق · عرض فقط</span>
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
                <p class="dist-empty">لا توجد تعليقات بين الفريق والمشرف.</p>
            @endforelse
        </div>
    @endif

    {{-- ═══ تاريخ هذا المشروع ═══
         السجلّ حيث يُحتاج إليه، لا في صفحة بعيدة وحدها: من غيّر الدرجة
         ومتى، ومن فكّ اعتمادها ولماذا — يُقرأ هنا مع بقية سياق المشروع. --}}
    @php
        $history = $project->auditLogs()->limit(10)->get();
    @endphp

    @if ($history->count())
        <div class="dist-panel mt-4">
            <div class="dist-head">
                <span>تاريخ هذا المشروع</span>
                <a href="{{ route('admin.audit.index') }}">السجلّ كاملاً</a>
            </div>

            <div class="audit-list">
                @foreach ($history as $log)
                    <x-audit-row :log="$log" />
                @endforeach
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
    </script>
@endpush
