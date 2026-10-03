@extends('layouts.admin.admin')
@section('title', $project->title)

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">{{ __('مجموعاتي') }}</x-crumb>
    <x-crumb>{{ $project->title }}</x-crumb>
@endsection

@section('content')

    @php
        $daysLeft = $project->days_left;
        $locked = $project->is_locked;
        $fmtGrade = fn ($g) => rtrim(rtrim(number_format($g, 2), '0'), '.');

        // «ما يحتاجه هذا المشروع» — لوح المشرف يرسل إلى هنا، فالصفحة
        // تقول لماذا بدل أن تعرض كل الأقسام بالوزن نفسه
        $todos = [];

        $overdue = $project->milestones->filter(
            fn ($m) => $m->isLate()
        );

        // بعد الاكتمال لا تُطلب مراحل — الدرجة وحدها ما بقي
        if ($project->status === 'accept' && $overdue->count()) {
            $todos[] = ['icon' => 'ti-alert-triangle', 'tone' => 'is-danger', 'n' => $overdue->count(),
                'label' => $overdue->count() === 1 ? __('مرحلة متأخّرة: :title', ['title' => $overdue->first()->title]) : __('مراحل فات موعدها'),
                'href' => '#milestones'];
        }

        // المناقشة المجدولة أو المنتهية: الدرجة تُرصد من لجنتها لا من هنا
        $committee = \App\Support\DefenseGrading::defenseFor($project);

        if ($project->status === 'complete' && is_null($project->grade)) {
            $todos[] = $committee
                ? ['icon' => 'ti-presentation', 'tone' => 'is-warn', 'n' => null,
                    'label' => $committee->starts_at->isFuture()
                        ? __('المناقشة :day · :time — الدرجة من اللجنة', ['day' => $committee->starts_at->translatedFormat('l j F'), 'time' => $committee->starts_at->format('H:i')])
                        : __('نوقش المشروع — ارصد درجتك في اللجنة'),
                    'href' => route('supervisor.defenses.show', $committee->id) . '#grade']
                : ['icon' => 'ti-award', 'tone' => 'is-warn', 'n' => null,
                    'label' => __('المشروع مكتمل ولم تُرصد درجته'), 'href' => '#grade'];
        }

        if ($unread) {
            $todos[] = ['icon' => 'ti-message-dots', 'tone' => '', 'n' => $unread,
                'label' => $unread === 1 ? __('رسالة من الفريق لم تقرأها') : __('رسائل من الفريق لم تقرأها'),
                'href' => route('supervisor.discussion', $project->id)];
        }

        if ($project->status === 'accept') {
            if ($project->milestones->isEmpty()) {
                $todos[] = ['icon' => 'ti-list-details', 'tone' => '', 'n' => null,
                    'label' => __('لا مراحل بعد — الفريق لا يعرف ما التالي'), 'href' => '#milestones'];
            }

            if (is_null($project->date_line)) {
                $todos[] = ['icon' => 'ti-calendar-question', 'tone' => '', 'n' => null,
                    'label' => __('لم يُحدَّد موعد التسليم النهائي'), 'href' => '#project-facts'];
            } elseif ($daysLeft >= 0 && $daysLeft <= 7) {
                $todos[] = ['icon' => 'ti-alarm', 'tone' => 'is-warn', 'n' => $daysLeft,
                    'label' => $daysLeft === 0 ? __('التسليم النهائي اليوم') : __('يوماً حتى التسليم النهائي'),
                    'href' => '#project-facts'];
            }
        }
    @endphp

    <x-page-header title="{{ $project->title }}"
        subtitle="{{ $project->project_type->name }} · {{ $project->semester->label }}">
        <x-slot:actions>
            <x-status-badge :status="$project->status" class="align-self-center" />
            <a href="{{ route('supervisor.discussion', $project->id) }}" class="btn btn-outline-secondary">
                <i class="ti ti-messages me-1" aria-hidden="true"></i>
                {{ __('النقاش') }}
                @if ($unread)
                    <span class="sidebar-count ms-2">{{ $unread }}</span>
                @endif
            </a>
            @if ($project->status === 'accept' && ! $locked)
                <form action="{{ route('supervisor.project.complete', ['project_id' => $project->id]) }}" method="POST">
                    @csrf
                    <button name="btnAccept" value="accept" class="btn btn-primary"
                        data-confirm-tone="primary" data-confirm-title="{{ __('اكتمال المشروع') }}" data-confirm-ok="{{ __('تأكيد الاكتمال') }}"
                        data-confirm="{{ __('تأكيد اكتمال المشروع؟ سيُشعر الفريق، ويُفتح التقييم.') }}">
                        <i class="ti ti-circle-check me-1" aria-hidden="true"></i>
                        {{ __('اكتمال المشروع') }}
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($locked)
        <p class="hint-bar mb-4" role="status">
            <i class="ti ti-archive" aria-hidden="true"></i>
            <span>
                <b>{{ __('مشروع مؤرشف — رُصدت درجته.') }}</b>
                {{ __('المراحل والملفات والموعد النهائي للعرض فقط حفاظاً على سلامة السجلّ.') }}
                {{ __('النقاش يبقى مفتوحاً.') }}
            </span>
        </p>
    @endif

    {{-- ===== ما يحتاجه هذا المشروع ===== --}}
    <section class="todo-panel mb-4">
        <h2 class="todo-title">
            <i class="ti ti-target-arrow" aria-hidden="true"></i>
            {{ __('ما يحتاجه هذا المشروع') }}
        </h2>
        @forelse ($todos as $todo)
            <a href="{{ $todo['href'] }}" class="todo-row {{ $todo['tone'] }}">
                <i class="ti {{ $todo['icon'] }}" aria-hidden="true"></i>
                @if (! is_null($todo['n']))
                    <span class="todo-n">{{ $todo['n'] }}</span>
                @endif
                <span class="todo-label">{{ $todo['label'] }}</span>
                <i class="ti ti-chevron-left todo-go" aria-hidden="true"></i>
            </a>
        @empty
            <p class="todo-clear">
                <i class="ti ti-circle-check" aria-hidden="true"></i>
                {{ __('لا شيء ينتظرك هنا — المشروع يسير كما ينبغي.') }}
            </p>
        @endforelse
    </section>

    {{-- حجم الفريق في بطاقته، والملفات في قسمها — الشريط لما لا يُقال في غيره --}}
    @include('dashboard.project._stat-strip', ['project' => $project])

    {{-- ===== التقييم النهائي ===== --}}
    @if ($project->status === 'complete')
        <section class="dist-panel mb-4" id="grade">
            <div class="dist-head">
                <span>{{ __('التقييم النهائي') }}</span>
                @if (! is_null($project->grade) && ! $project->isGradeLocked())
                    <span class="dist-head-note">{{ __('مسوّدة — لم تُعتمد بعد') }}</span>
                @endif
            </div>

            @if ($project->isGradeLocked())
                {{-- معتمدة: النموذج يختفي. إبقاؤه معطَّلاً يوحي بأن
                     التعديل ممكن بحيلة، والقفل قرار لا عائق. --}}
                <div class="grade-final m-3">
                    <div class="grade-final-score">
                        <span class="grade-num">{{ $fmtGrade($project->grade) }}</span>
                        <span class="grade-of">{{ __('من 100') }}</span>
                    </div>
                    <div class="grade-final-body">
                        <p class="grade-final-head">
                            <i class="ti ti-lock-check" aria-hidden="true"></i>
                            {{ __('الدرجة معتمدة') }}
                            <span class="grade-label">{{ $project->grade_label }}</span>
                        </p>
                        @if ($project->evaluation_note)
                            <p class="grade-note">{{ $project->evaluation_note }}</p>
                        @endif
                        <p class="grade-final-note">
                            {{ __('اعتُمدت بتاريخ :date.', ['date' => $project->grade_locked_at?->format('Y-m-d')]) }}
                            {{ __('لم يعد بالإمكان تعديلها — راجع مسؤول النظام إن كان فيها خطأ.') }}
                        </p>
                    </div>
                </div>
            @else
                @if ($committee)
                    {{-- الدرجة متوسط درجات لجنة المناقشة، تُرصد من صفحتها --}}
                    <div class="dsv-committee-note">
                        <i class="ti ti-presentation" aria-hidden="true"></i>
                        <span>
                            {{ __('تُرصد الدرجة من لجنة المناقشة — متوسط درجتك ودرجة الممتحن.') }}
                            @if (! is_null($project->grade))
                                {!! __('الحالية :grade (:label).', ['grade' => '<b>' . e($fmtGrade($project->grade)) . '</b>', 'label' => e($project->grade_label)]) !!}
                            @endif
                        </span>
                        <a href="{{ route('supervisor.defenses.show', $committee->id) }}#grade" class="btn btn-primary btn-sm">{{ __('صفحة المناقشة') }}</a>
                    </div>
                @else
                <form action="{{ route('supervisor.project.evaluate', ['project' => $project->id]) }}" method="POST"
                    class="grade-form">
                    @csrf
                    <div class="grade-form-score">
                        <label class="form-label" for="grade">{{ __('الدرجة من 100') }}</label>
                        <input type="number" id="grade" name="grade" min="0" max="100" step="0.5" required
                            class="form-control @error('grade') is-invalid @enderror"
                            value="{{ old('grade', $project->grade) }}">
                    </div>
                    <div class="grade-form-note">
                        <label class="form-label" for="evaluation_note">{{ __('ملاحظة ختامية للفريق') }} <small>{{ __('(اختيارية)') }}</small></label>
                        <input type="text" id="evaluation_note" name="evaluation_note" maxlength="2000"
                            class="form-control" placeholder="{{ __('ما أحسنوه وما يُحسَّن..') }}"
                            value="{{ old('evaluation_note', $project->evaluation_note) }}">
                    </div>
                    <button type="submit" class="btn btn-primary" data-loading-text="..">
                        {{ is_null($project->grade) ? __('حفظ التقييم') : __('تحديث') }}
                    </button>
                    @error('grade')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </form>
                @endif

                {{-- الاعتماد فعل منفصل عن الحفظ: الدرجة تُراجَع
                     وتُعدَّل مرّات، ثم تُعتمد مرّة واحدة. --}}
                @if ($project->canLockGrade())
                    <div class="grade-lock-bar m-3">
                        <div>
                            <b>{{ __('انتهيت من التقييم؟ (:grade — :label)', ['grade' => $fmtGrade($project->grade), 'label' => $project->grade_label]) }}</b>
                            <span>{{ __('اعتماد الدرجة يقفلها — بعده لا تستطيع تعديلها، ويلزم مسؤول النظام لفكّها.') }}</span>
                        </div>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#lockGradeModal">
                            <i class="ti ti-lock me-1" aria-hidden="true"></i>
                            {{ __('اعتماد الدرجة') }}
                        </button>
                    </div>
                @endif
            @endif
        </section>

        {{-- تأكيد الاعتماد --}}
        @if ($project->canLockGrade())
            <div class="modal fade" id="lockGradeModal" tabindex="-1" aria-labelledby="lockGradeLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="lockGradeLabel">{{ __('اعتماد الدرجة النهائية') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
                        </div>

                        <form action="{{ route('supervisor.project.grade.lock', ['project' => $project->id]) }}"
                            method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="grade-confirm">
                                    <span class="grade-confirm-n">{{ $fmtGrade($project->grade) }}</span>
                                    <span class="grade-confirm-l">{{ $project->grade_label }} · {{ __('من 100') }}</span>
                                </div>

                                <ul class="archive-effects">
                                    <li class="is-stop">
                                        <i class="ti ti-circle-x" aria-hidden="true"></i>
                                        {{ __('لن تستطيع تعديل الدرجة أو ملاحظاتها بعد الاعتماد') }}
                                    </li>
                                    <li class="is-keep">
                                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                                        {{ __('يُسجَّل الاعتماد باسمك وتاريخه في سجلّ التدقيق') }}
                                    </li>
                                    <li class="is-keep">
                                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                                        {{ __('مسؤول النظام يستطيع فكّ الاعتماد بسبب مكتوب إن لزم') }}
                                    </li>
                                </ul>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-lock me-1" aria-hidden="true"></i>
                                    {{ __('اعتماد') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- عمودان: المتن ما يُعمل عليه، والجانب ما يُرجَع إليه --}}
    <div class="work-grid">

        <div class="work-main">
            @include('dashboard.project._milestones', ['project' => $project, 'editable' => ! $locked])
            @include('dashboard.project._files', ['project' => $project, 'role' => 'supervisor'])
        </div>

        <aside class="work-side">
            {{-- بيانات المشروع + الموعد النهائي --}}
            <div class="ctx-card" id="project-facts">
                <div class="ctx-head">
                    <i class="ti ti-briefcase" aria-hidden="true"></i>
                    {{ __('وصف المشروع') }}
                </div>
                {{-- العنوان في رأس الصفحة والمواعيد في الشريط — هنا الوصف وتعديل الموعد --}}
                @if ($project->description)
                    @include('dashboard.project._clamp', ['text' => $project->description])
                @else
                    <p class="proj-desc text-secondary">{{ __('قُدّم :date — بلا وصف.', ['date' => $project->created_at->format('Y-m-d')]) }}</p>
                @endif

                @unless ($locked)
                    <form action="{{ route('supervisor.deadline.update', ['project' => $project->id]) }}"
                        method="POST" class="ctx-form">
                        @csrf
                        <label class="form-label" for="date_line">
                            {{ $project->date_line ? __('تعديل الموعد النهائي') : __('تحديد الموعد النهائي') }}
                        </label>
                        <div class="ctx-form-row">
                            <input type="date" id="date_line" name="date_line" required
                                class="form-control @error('date_line') is-invalid @enderror"
                                value="{{ old('date_line', $project->date_line?->format('Y-m-d')) }}">
                            <button type="submit" class="btn btn-outline-secondary" data-loading-text="..">{{ __('حفظ') }}</button>
                        </div>
                        @error('date_line')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </form>
                @endunless
            </div>

            @include('dashboard.project._team-card', ['project' => $project, 'role' => 'supervisor'])
        </aside>
    </div>

@endsection
