@extends('layouts.admin.admin')
@section('title', 'الصفحة الرئيسية')

@section('content')

    @php
        $activeProject = null;
        if ($student->groups->count() > 0 && $student->groups->first()->project->status != 'reject') {
            $activeProject = $student->groups->first()->project;
        }
        $lastRejected = null;
        if (!$activeProject && $student->groups->count() > 0 && $student->groups->first()->project->status == 'reject') {
            $lastRejected = $student->groups->first()->project;
        }
        $lastNotification = auth()->user()->notifications->first();
        $statusMap = config('statuses.map');
    @endphp

    {{-- ===== ترحيب + ترويسة ===== --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">لوحة الطالب — الفصل: {{ $semester->name }}</div>
                <h2 class="page-title">أهلاً، {{ $student->name }} 👋</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('student.profile.edit') }}" class="btn btn-outline-primary">
                    <i class="ti ti-user-edit me-1"></i>
                    تعديل الملف الشخصي
                </a>
            </div>
        </div>
    </div>

    {{-- ===== مؤشرات سريعة ===== --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    @php
                        $st = $activeProject->status ?? ($lastRejected ? 'reject' : null);
                        $stConf = $st ? ($statusMap[$st] ?? config('statuses.fallback')) : null;
                    @endphp
                    <span class="avatar avatar-lg {{ $stConf['badge'] ?? 'bg-secondary-lt' }} rounded-3 me-3">
                        <i class="ti {{ $stConf['icon'] ?? 'ti-file-off' }} fs-2"></i>
                    </span>
                    <div>
                        <div class="h3 mb-0">
                            @if ($st)
                                <x-status-badge :status="$st" />
                            @else
                                لا يوجد مشروع
                            @endif
                        </div>
                        <div class="text-secondary">حالة المشروع</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-blue-lt text-blue rounded-3 me-3">
                        <i class="ti ti-users-group fs-2"></i>
                    </span>
                    <div>
                        <div class="h1 mb-0 lh-1">{{ $activeProject ? $activeProject->group->count() : 0 }}</div>
                        <div class="text-secondary mt-1">أعضاء الفريق</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-purple-lt text-purple rounded-3 me-3">
                        <i class="ti ti-progress-check fs-2"></i>
                    </span>
                    <div>
                        <div class="h1 mb-0 lh-1">
                            {{ $activeProject && !is_null($activeProject->progress) ? $activeProject->progress . '%' : '—' }}
                        </div>
                        <div class="text-secondary mt-1">نسبة الإنجاز</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            @php
                $daysLeft = $activeProject ? $activeProject->days_left : null;
                $deadlineTone = is_null($daysLeft) ? 'cyan' : ($daysLeft < 0 ? 'red' : ($daysLeft <= 7 ? 'red' : ($daysLeft <= 14 ? 'yellow' : 'cyan')));
            @endphp
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-{{ $deadlineTone }}-lt text-{{ $deadlineTone }} rounded-3 me-3">
                        <i class="ti ti-alarm fs-2"></i>
                    </span>
                    <div>
                        <div class="h3 mb-0">
                            @if (is_null($daysLeft))
                                لم يُحدَّد بعد
                            @elseif ($daysLeft < 0)
                                انقضى منذ {{ abs($daysLeft) }} يوم
                            @elseif ($daysLeft == 0)
                                اليوم!
                            @else
                                متبقي {{ $daysLeft }} يوم
                            @endif
                        </div>
                        <div class="text-secondary">
                            الموعد النهائي
                            @if ($activeProject && $activeProject->date_line)
                                ({{ $activeProject->date_line->format('Y-m-d') }})
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($activeProject)

        {{-- ===== الخط الزمني لحالة المشروع ===== --}}
        <div class="card mb-4">
            <div class="card-body">
                @php
                    $flow = ['request' => 'تقديم الطلب', 'accept' => 'موافقة المشرف', 'complete' => 'اكتمال المشروع'];
                    $order = array_keys($flow);
                    $currentIdx = array_search($activeProject->status, $order);
                    if ($currentIdx === false) { $currentIdx = 0; }
                @endphp
                <div class="steps steps-counter">
                    @foreach ($flow as $key => $label)
                        @php $idx = array_search($key, $order); @endphp
                        <span class="step-item {{ $idx < $currentIdx ? '' : ($idx == $currentIdx ? 'active' : '') }}"
                            @if($idx > $currentIdx) style="opacity:.5" @endif>
                            {{ $label }}
                            @if ($idx < $currentIdx || ($idx == $currentIdx && $key == 'complete'))
                                <i class="ti ti-check text-green ms-1"></i>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== التقييم النهائي ===== --}}
        @if ($activeProject->status === 'complete' && !is_null($activeProject->grade))
            <div class="card mb-4" style="background: linear-gradient(135deg, var(--ds-brand-50), #f5f3ff); border: 1px solid var(--ds-brand-200);">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <span class="avatar avatar-lg bg-primary text-white rounded-3"
                        style="background: var(--ds-gradient-135) !important;">
                        <i class="ti ti-award fs-2"></i>
                    </span>
                    <div class="me-auto">
                        <h3 class="mb-1">التقييم النهائي: {{ rtrim(rtrim(number_format($activeProject->grade, 2), '0'), '.') }} / 100
                            <span class="badge bg-purple-lt text-purple ms-2">{{ $activeProject->grade_label }}</span>
                        </h3>
                        @if ($activeProject->evaluation_note)
                            <div class="text-secondary">ملاحظات المشرف: {{ $activeProject->evaluation_note }}</div>
                        @endif
                        <div class="small text-secondary mt-1">
                            قُيّم بتاريخ {{ $activeProject->evaluated_at?->format('Y-m-d') }} 🎓 مبارك لكم!
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row row-deck row-cards mb-4">
            {{-- ===== بيانات المشروع ===== --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-briefcase me-2"></i>
                            مشروع التخرج
                        </h3>
                        <div class="card-actions">
                            <x-status-badge :status="$activeProject->status" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="datagrid mb-3">
                            <div class="datagrid-item">
                                <div class="datagrid-title">عنوان المشروع</div>
                                <div class="datagrid-content">{{ $activeProject->title }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">نوع المشروع</div>
                                <div class="datagrid-content">{{ $activeProject->project_type->name }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">تاريخ التقديم</div>
                                <div class="datagrid-content">{{ $activeProject->created_at->format('Y-m-d') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">الموعد النهائي</div>
                                <div class="datagrid-content">
                                    {{ $activeProject->date_line ? $activeProject->date_line->format('Y-m-d') : 'لم يُحدَّد بعد' }}
                                </div>
                            </div>
                        </div>
                        @if ($activeProject->description)
                            <div class="datagrid-title mb-1">وصف المشروع</div>
                            <p class="mb-0 text-secondary">{{ $activeProject->description }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== بطاقة المشرف ===== --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user-star me-2"></i>
                            مشرف المشروع
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar avatar-lg bg-primary-lt text-primary rounded-3 me-3">
                                {{ mb_substr(str_replace('د.', '', $activeProject->supervisor->name), 0, 2) }}
                            </span>
                            <div>
                                <div class="fw-bold">{{ $activeProject->supervisor->name }}</div>
                                <div class="text-secondary small">مشرف أكاديمي</div>
                            </div>
                        </div>
                        @if ($activeProject->supervisor->email)
                            <a href="mailto:{{ $activeProject->supervisor->email }}"
                                class="btn btn-outline-primary w-100 mb-2">
                                <i class="ti ti-mail me-1"></i>
                                {{ $activeProject->supervisor->email }}
                            </a>
                        @endif
                        @if ($activeProject->supervisor->phone)
                            <a href="tel:{{ $activeProject->supervisor->phone }}" class="btn btn-outline-primary w-100">
                                <i class="ti ti-phone me-1"></i>
                                <span dir="ltr">{{ $activeProject->supervisor->phone }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards mb-4">
            {{-- ===== مراحل المشروع ===== --}}
            <div class="col-lg-6" id="milestones" style="scroll-margin-top: 90px;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-list-check me-2"></i>
                            مراحل المشروع
                        </h3>
                        @if (!is_null($activeProject->progress))
                            <div class="card-actions text-secondary small">
                                {{ $activeProject->milestones->where('is_done', true)->count() }}
                                / {{ $activeProject->milestones->count() }} منجزة
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($activeProject->milestones->count() === 0)
                            <div class="empty py-4">
                                <div class="empty-icon"><i class="ti ti-list-details fs-1"></i></div>
                                <p class="empty-subtitle text-secondary">
                                    لم يضِف المشرف مراحل للمشروع بعد.
                                </p>
                            </div>
                        @else
                            <div class="progress mb-3" style="height: 10px">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ $activeProject->progress }}%"
                                    aria-valuenow="{{ $activeProject->progress }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            {{-- الخط الزمني للمراحل --}}
                            <div class="ds-timeline">
                                @foreach ($activeProject->milestones as $milestone)
                                    @php
                                        $overdue = !$milestone->is_done && $milestone->due_date && $milestone->due_date->isPast();
                                        $state = $milestone->is_done ? 'done' : ($overdue ? 'overdue' : 'pending');
                                    @endphp
                                    <div class="ds-timeline-item {{ $state }}">
                                        <span class="ds-timeline-dot" aria-hidden="true">
                                            <i class="ti {{ $milestone->is_done ? 'ti-check' : ($overdue ? 'ti-alert-triangle' : 'ti-clock') }}"></i>
                                        </span>
                                        <div class="ds-timeline-body">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <span class="{{ $milestone->is_done ? 'text-decoration-line-through text-secondary' : 'fw-bold' }}">
                                                    {{ $milestone->title }}
                                                </span>
                                                @if ($overdue)
                                                    <span class="badge bg-red-lt text-red">متأخرة</span>
                                                @elseif ($milestone->is_done)
                                                    <span class="badge bg-green-lt text-green">منجزة</span>
                                                @endif
                                            </div>
                                            @if ($milestone->due_date)
                                                <div class="small text-secondary mt-1">
                                                    <i class="ti ti-calendar-due me-1"></i>
                                                    الاستحقاق: {{ $milestone->due_date->format('Y-m-d') }}
                                                    @if ($milestone->is_done && $milestone->done_at)
                                                        · أُنجزت في {{ $milestone->done_at->format('Y-m-d') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== ملفات المشروع ===== --}}
            <div class="col-lg-6" id="files" style="scroll-margin-top: 90px;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-files me-2"></i>
                            ملفات المشروع
                        </h3>
                    </div>
                    <div class="card-body">
                        {{-- نموذج الرفع --}}
                        @if ($activeProject->is_locked)
                            <div class="form-hint mb-3">
                                <i class="ti ti-archive me-1"></i>
                                أُغلق رفع الملفات وحذفها بعد رصد التقييم — الملفات أدناه للعرض والتنزيل فقط.
                            </div>
                        @endif
                        @unless ($activeProject->is_locked)
                        <form action="{{ route('student.files.store', ['project' => $activeProject->id]) }}"
                            method="POST" enctype="multipart/form-data" class="mb-3">
                            @csrf

                            {{-- منطقة السحب والإفلات --}}
                            <label class="dropzone mb-2" id="file-dropzone" for="file-input">
                                <input type="file" name="file" id="file-input" required class="dropzone-input"
                                    accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">
                                <span class="dropzone-idle" id="dropzone-idle">
                                    <i class="ti ti-cloud-upload"></i>
                                    <span class="fw-bold">اسحب ملفك هنا أو اضغط للاختيار</span>
                                    <span class="text-secondary small">حتى 10MB — pdf, docx, pptx, xlsx, zip, صور</span>
                                </span>
                                <span class="dropzone-file d-none" id="dropzone-file">
                                    <i class="ti ti-file-check"></i>
                                    <span class="fw-bold text-truncate" id="dropzone-name"></span>
                                    <span class="text-secondary small" id="dropzone-size"></span>
                                </span>
                            </label>
                            @error('file')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div class="row g-2">
                                <div class="col-sm-8">
                                    <input type="text" name="title" required maxlength="120"
                                        class="form-control @error('title') is-invalid @enderror"
                                        placeholder="اسم الملف (مثال: المقترح)" value="{{ old('title') }}"
                                        aria-label="اسم الملف">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4 d-grid">
                                    <button type="submit" class="btn btn-primary" data-loading-text="جارٍ الرفع..">
                                        <i class="ti ti-upload me-1"></i>
                                        رفع الملف
                                    </button>
                                </div>
                            </div>
                        </form>
                        @endunless

                        @if ($activeProject->files->count() === 0)
                            <div class="empty py-4">
                                <div class="empty-icon"><i class="ti ti-file-off fs-1"></i></div>
                                <p class="empty-subtitle text-secondary">لا توجد ملفات مرفوعة بعد.</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($activeProject->files as $file)
                                    <div class="list-group-item d-flex align-items-center gap-3 px-0">
                                        <span class="avatar avatar-sm bg-blue-lt text-blue rounded">
                                            <i class="ti ti-file-text"></i>
                                        </span>
                                        <div class="flex-fill min-w-0">
                                            <div class="fw-bold text-truncate">{{ $file->title }}</div>
                                            <div class="small text-secondary">
                                                {{ $file->human_size }}
                                                · {{ $file->created_at->format('Y-m-d') }}
                                                · {{ $file->uploader_type === \App\Models\Supervisor::class ? 'المشرف' : ($file->uploader->name ?? 'طالب') }}
                                            </div>
                                        </div>
                                        <a href="{{ route('files.download', ['file' => $file->id]) }}"
                                            class="btn btn-sm btn-outline-primary" title="تنزيل">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        @if (!$activeProject->is_locked && $file->uploader_type === \App\Models\Student::class && (int) $file->uploader_id === (int) auth()->id())
                                            <form action="{{ route('student.files.destroy', ['file' => $file->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('هل أنت متأكد من حذف الملف؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== فريق المشروع ===== --}}
        <div class="card mb-4" id="team" style="scroll-margin-top: 90px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-users-group me-2"></i>
                    فريق المشروع
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>العضو</th>
                            <th>الرقم الجامعي</th>
                            <th>رقم الجوال</th>
                            <th>التخصص</th>
                            <th>الدور</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activeProject->group as $group)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-sm bg-primary-lt text-primary rounded-circle">
                                            {{ mb_substr($group->student->name, 0, 2) }}
                                        </span>
                                        <span class="fw-bold">{{ $group->student->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $group->student->university_id }}</td>
                                <td dir="ltr">{{ $group->student->phone }}</td>
                                <td>{{ $group->student->specialize->name }}</td>
                                <td>
                                    @if ($group->type == 'leader')
                                        <span class="badge bg-purple-lt text-purple">
                                            <i class="ti ti-crown me-1"></i> قائد الفريق
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-lt">عضو</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== نقاش المشروع ===== --}}
        <div class="card mb-4" id="discussion" style="scroll-margin-top: 90px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-message-circle me-2"></i>
                    نقاش المشروع
                </h3>
                <div class="card-actions small text-secondary">
                    {{ $activeProject->comments->count() }} تعليق
                </div>
            </div>
            <div class="card-body">
                @if ($activeProject->comments->count() === 0)
                    <div class="empty py-3">
                        <div class="empty-icon"><i class="ti ti-messages fs-1"></i></div>
                        <p class="empty-subtitle text-secondary">
                            لا توجد تعليقات بعد — ابدأ النقاش مع مشرفك من هنا.
                        </p>
                    </div>
                @else
                    <div class="mb-3" style="max-height: 380px; overflow-y: auto;">
                        @foreach ($activeProject->comments as $comment)
                            <div class="d-flex gap-3 mb-3">
                                <span class="avatar avatar-sm rounded-circle {{ $comment->is_supervisor ? 'bg-purple-lt text-purple' : 'bg-primary-lt text-primary' }}">
                                    {{ mb_substr(str_replace('د.', '', $comment->author->name ?? '؟'), 0, 2) }}
                                </span>
                                <div class="flex-fill min-w-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold">{{ $comment->author->name ?? 'مستخدم' }}</span>
                                        <span class="badge {{ $comment->is_supervisor ? 'bg-purple-lt text-purple' : 'bg-blue-lt text-blue' }}">
                                            {{ $comment->is_supervisor ? 'مشرف' : 'طالب' }}
                                        </span>
                                        <span class="small text-secondary">{{ $comment->created_at->diffForHumans() }}</span>
                                        @if (!$comment->is_supervisor && (int) $comment->author_id === (int) auth()->id())
                                            <form action="{{ route('student.comments.destroy', ['comment' => $comment->id]) }}"
                                                method="POST" class="ms-auto"
                                                onsubmit="return confirm('حذف هذا التعليق؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-ghost-danger p-1" title="حذف">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="text-secondary mt-1" style="white-space: pre-line;">{{ $comment->body }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('student.comments.store', ['project' => $activeProject->id]) }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2">
                        <textarea name="body" rows="2" required maxlength="1000"
                            class="form-control @error('body') is-invalid @enderror"
                            placeholder="اكتب تعليقك أو سؤالك للمشرف..">{{ old('body') }}</textarea>
                        <button type="submit" class="btn btn-primary align-self-end" data-loading-text="..">
                            <i class="ti ti-send"></i>
                        </button>
                    </div>
                    @error('body')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </form>
            </div>
        </div>

        {{-- ===== آخر ردود المشرف ===== --}}
        @if (auth()->user()->notifications->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-messages me-2"></i>
                        آخر الردود
                    </h3>
                    <div class="card-actions">
                        <a href="{{ route('student.showNotification') }}" class="btn btn-sm btn-outline-primary">
                            عرض الكل
                        </a>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @foreach (auth()->user()->notifications->take(3) as $notification)
                        <div class="list-group-item d-flex gap-3">
                            <span class="avatar avatar-sm bg-primary-lt text-primary rounded-circle">
                                <i class="ti ti-message"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-bold">{{ $notification->data['supervisor_name'] ?? '' }}
                                    <span class="text-secondary fw-normal small">
                                        — {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="text-secondary">{{ $notification->data['msg'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else

        {{-- ===== لا يوجد مشروع نشط: سبب الرفض إن وجد + نموذج التقديم ===== --}}
        @if ($lastRejected)
            <div class="alert alert-danger mb-4" role="alert">
                <div class="d-flex">
                    <i class="ti ti-circle-x fs-2 me-2"></i>
                    <div>
                        <h4 class="alert-title">تم رفض مشروعك السابق: "{{ $lastRejected->title }}"</h4>
                        @if ($lastNotification && !empty($lastNotification->data['msg']))
                            <div class="text-secondary">رد المشرف: {{ $lastNotification->data['msg'] }}</div>
                        @endif
                        <div class="mt-1">يمكنك تقديم طلب جديد من النموذج أدناه. 👇</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-briefcase me-2"></i>
                    تقديم طلب مشروع تخرج
                </h3>
            </div>
            <div class="card-body">
                @include('dashboard.student.project_form')
            </div>
        </div>

    @endif

    @push('js')
        <script>
            // ===== رفع الملفات بالسحب والإفلات =====
            (function () {
                var zone = document.getElementById('file-dropzone');
                var input = document.getElementById('file-input');
                if (!zone || !input) return;

                var idle = document.getElementById('dropzone-idle');
                var fileBox = document.getElementById('dropzone-file');
                var nameEl = document.getElementById('dropzone-name');
                var sizeEl = document.getElementById('dropzone-size');

                function humanSize(bytes) {
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                    if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
                    return bytes + ' B';
                }

                function showFile() {
                    var f = input.files && input.files[0];
                    idle.classList.toggle('d-none', !!f);
                    fileBox.classList.toggle('d-none', !f);
                    if (f) {
                        nameEl.textContent = f.name;
                        sizeEl.textContent = humanSize(f.size);
                        zone.classList.add('has-file');
                    } else {
                        zone.classList.remove('has-file');
                    }
                }

                input.addEventListener('change', showFile);

                ['dragenter', 'dragover'].forEach(function (ev) {
                    zone.addEventListener(ev, function (e) {
                        e.preventDefault();
                        zone.classList.add('dragging');
                    });
                });
                ['dragleave', 'drop'].forEach(function (ev) {
                    zone.addEventListener(ev, function (e) {
                        e.preventDefault();
                        zone.classList.remove('dragging');
                    });
                });
                zone.addEventListener('drop', function (e) {
                    if (e.dataTransfer.files.length > 0) {
                        input.files = e.dataTransfer.files;
                        showFile();
                    }
                });
            })();
        </script>
    @endpush

@stop
