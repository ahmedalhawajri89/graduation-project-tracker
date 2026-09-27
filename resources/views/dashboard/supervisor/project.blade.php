@extends('layouts.admin.admin')
@section('title', $project->title)

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">مجموعاتي</x-crumb>
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
            fn ($m) => ! $m->is_done && $m->due_date && $m->due_date->isPast()
        );

        // بعد الاكتمال لا تُطلب مراحل — الدرجة وحدها ما بقي
        if ($project->status === 'accept' && $overdue->count()) {
            $todos[] = ['icon' => 'ti-alert-triangle', 'tone' => 'is-danger', 'n' => $overdue->count(),
                'label' => $overdue->count() === 1 ? 'مرحلة متأخّرة: ' . $overdue->first()->title : 'مراحل فات موعدها',
                'href' => '#milestones'];
        }

        if ($project->status === 'complete' && is_null($project->grade)) {
            $todos[] = ['icon' => 'ti-award', 'tone' => 'is-warn', 'n' => null,
                'label' => 'المشروع مكتمل ولم تُرصد درجته', 'href' => '#grade'];
        }

        if ($unread) {
            $todos[] = ['icon' => 'ti-message-dots', 'tone' => '', 'n' => $unread,
                'label' => $unread === 1 ? 'رسالة من الفريق لم تقرأها' : 'رسائل من الفريق لم تقرأها',
                'href' => route('supervisor.discussion', $project->id)];
        }

        if ($project->status === 'accept') {
            if ($project->milestones->isEmpty()) {
                $todos[] = ['icon' => 'ti-list-details', 'tone' => '', 'n' => null,
                    'label' => 'لا مراحل بعد — الفريق لا يعرف ما التالي', 'href' => '#milestones'];
            }

            if (is_null($project->date_line)) {
                $todos[] = ['icon' => 'ti-calendar-question', 'tone' => '', 'n' => null,
                    'label' => 'لم يُحدَّد موعد التسليم النهائي', 'href' => '#project-facts'];
            } elseif ($daysLeft >= 0 && $daysLeft <= 7) {
                $todos[] = ['icon' => 'ti-alarm', 'tone' => 'is-warn', 'n' => $daysLeft,
                    'label' => $daysLeft === 0 ? 'التسليم النهائي اليوم' : 'يوماً حتى التسليم النهائي',
                    'href' => '#project-facts'];
            }
        }
    @endphp

    <x-page-header title="{{ $project->title }}"
        subtitle="{{ $project->project_type->name }} · {{ $project->semester->name }}">
        <x-slot:actions>
            <x-status-badge :status="$project->status" class="align-self-center" />
            <a href="{{ route('supervisor.discussion', $project->id) }}" class="btn btn-outline-secondary">
                <i class="ti ti-messages me-1" aria-hidden="true"></i>
                النقاش
                @if ($unread)
                    <span class="sidebar-count ms-2">{{ $unread }}</span>
                @endif
            </a>
            @if ($project->status === 'accept' && ! $locked)
                <form action="{{ route('supervisor.project.complete', ['project_id' => $project->id]) }}" method="POST">
                    @csrf
                    <button name="btnAccept" value="accept" class="btn btn-primary"
                        onclick="return confirm('تأكيد اكتمال المشروع؟ سيُشعر الفريق، ويُفتح التقييم.')">
                        <i class="ti ti-circle-check me-1" aria-hidden="true"></i>
                        اكتمال المشروع
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($locked)
        <p class="hint-bar mb-4" role="status">
            <i class="ti ti-archive" aria-hidden="true"></i>
            <span>
                <b>مشروع مؤرشف — رُصدت درجته.</b>
                المراحل والملفات والموعد النهائي للعرض فقط حفاظاً على سلامة السجلّ.
                النقاش يبقى مفتوحاً.
            </span>
        </p>
    @endif

    {{-- ===== ما يحتاجه هذا المشروع ===== --}}
    <section class="todo-panel mb-4">
        <h2 class="todo-title">
            <i class="ti ti-target-arrow" aria-hidden="true"></i>
            ما يحتاجه هذا المشروع
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
                لا شيء ينتظرك هنا — المشروع يسير كما ينبغي.
            </p>
        @endforelse
    </section>

    {{-- حجم الفريق في بطاقته، والملفات في قسمها — الشريط لما لا يُقال في غيره --}}
    @include('dashboard.project._stat-strip', ['project' => $project])

    {{-- ===== التقييم النهائي ===== --}}
    @if ($project->status === 'complete')
        <section class="dist-panel mb-4" id="grade">
            <div class="dist-head">
                <span>التقييم النهائي</span>
                @if (! is_null($project->grade) && ! $project->isGradeLocked())
                    <span class="dist-head-note">مسوّدة — لم تُعتمد بعد</span>
                @endif
            </div>

            @if ($project->isGradeLocked())
                {{-- معتمدة: النموذج يختفي. إبقاؤه معطَّلاً يوحي بأن
                     التعديل ممكن بحيلة، والقفل قرار لا عائق. --}}
                <div class="grade-final m-3">
                    <div class="grade-final-score">
                        <span class="grade-num">{{ $fmtGrade($project->grade) }}</span>
                        <span class="grade-of">من 100</span>
                    </div>
                    <div class="grade-final-body">
                        <p class="grade-final-head">
                            <i class="ti ti-lock-check" aria-hidden="true"></i>
                            الدرجة معتمدة
                            <span class="grade-label">{{ $project->grade_label }}</span>
                        </p>
                        @if ($project->evaluation_note)
                            <p class="grade-note">{{ $project->evaluation_note }}</p>
                        @endif
                        <p class="grade-final-note">
                            اعتُمدت بتاريخ {{ $project->grade_locked_at?->format('Y-m-d') }}.
                            لم يعد بالإمكان تعديلها — راجع مسؤول النظام إن كان فيها خطأ.
                        </p>
                    </div>
                </div>
            @else
                <form action="{{ route('supervisor.project.evaluate', ['project' => $project->id]) }}" method="POST"
                    class="grade-form">
                    @csrf
                    <div class="grade-form-score">
                        <label class="form-label" for="grade">الدرجة من 100</label>
                        <input type="number" id="grade" name="grade" min="0" max="100" step="0.5" required
                            class="form-control @error('grade') is-invalid @enderror"
                            value="{{ old('grade', $project->grade) }}">
                    </div>
                    <div class="grade-form-note">
                        <label class="form-label" for="evaluation_note">ملاحظة ختامية للفريق <small>(اختيارية)</small></label>
                        <input type="text" id="evaluation_note" name="evaluation_note" maxlength="2000"
                            class="form-control" placeholder="ما أحسنوه وما يُحسَّن.."
                            value="{{ old('evaluation_note', $project->evaluation_note) }}">
                    </div>
                    <button type="submit" class="btn btn-primary" data-loading-text="..">
                        {{ is_null($project->grade) ? 'حفظ التقييم' : 'تحديث' }}
                    </button>
                    @error('grade')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </form>

                {{-- الاعتماد فعل منفصل عن الحفظ: الدرجة تُراجَع
                     وتُعدَّل مرّات، ثم تُعتمد مرّة واحدة. --}}
                @if ($project->canLockGrade())
                    <div class="grade-lock-bar m-3">
                        <div>
                            <b>انتهيت من التقييم؟ ({{ $fmtGrade($project->grade) }} — {{ $project->grade_label }})</b>
                            <span>اعتماد الدرجة يقفلها — بعده لا تستطيع تعديلها، ويلزم مسؤول النظام لفكّها.</span>
                        </div>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#lockGradeModal">
                            <i class="ti ti-lock me-1" aria-hidden="true"></i>
                            اعتماد الدرجة
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
                            <h5 class="modal-title" id="lockGradeLabel">اعتماد الدرجة النهائية</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>

                        <form action="{{ route('supervisor.project.grade.lock', ['project' => $project->id]) }}"
                            method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="grade-confirm">
                                    <span class="grade-confirm-n">{{ $fmtGrade($project->grade) }}</span>
                                    <span class="grade-confirm-l">{{ $project->grade_label }} · من 100</span>
                                </div>

                                <ul class="archive-effects">
                                    <li class="is-stop">
                                        <i class="ti ti-circle-x" aria-hidden="true"></i>
                                        لن تستطيع تعديل الدرجة أو ملاحظاتها بعد الاعتماد
                                    </li>
                                    <li class="is-keep">
                                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                                        يُسجَّل الاعتماد باسمك وتاريخه في سجلّ التدقيق
                                    </li>
                                    <li class="is-keep">
                                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                                        مسؤول النظام يستطيع فكّ الاعتماد بسبب مكتوب إن لزم
                                    </li>
                                </ul>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-lock me-1" aria-hidden="true"></i>
                                    اعتماد
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
                    وصف المشروع
                </div>
                {{-- العنوان في رأس الصفحة والمواعيد في الشريط — هنا الوصف وتعديل الموعد --}}
                @if ($project->description)
                    @include('dashboard.project._clamp', ['text' => $project->description])
                @else
                    <p class="proj-desc text-secondary">قُدّم {{ $project->created_at->format('Y-m-d') }} — بلا وصف.</p>
                @endif

                @unless ($locked)
                    <form action="{{ route('supervisor.deadline.update', ['project' => $project->id]) }}"
                        method="POST" class="ctx-form">
                        @csrf
                        <label class="form-label" for="date_line">
                            {{ $project->date_line ? 'تعديل الموعد النهائي' : 'تحديد الموعد النهائي' }}
                        </label>
                        <div class="ctx-form-row">
                            <input type="date" id="date_line" name="date_line" required
                                class="form-control @error('date_line') is-invalid @enderror"
                                value="{{ old('date_line', $project->date_line?->format('Y-m-d')) }}">
                            <button type="submit" class="btn btn-outline-secondary" data-loading-text="..">حفظ</button>
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
