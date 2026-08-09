@extends('layouts.admin.admin')
@section('title', 'إدارة المشروع')

@section('content')

    @php
        $daysLeft = $project->days_left;
        $deadlineTone = is_null($daysLeft) ? 'secondary' : ($daysLeft <= 7 ? 'red' : ($daysLeft <= 14 ? 'yellow' : 'cyan'));
    @endphp

    {{-- ===== الترويسة ===== --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إدارة المشروع — {{ $project->project_type->name }}</div>
                <h2 class="page-title d-flex align-items-center gap-2 flex-wrap">
                    {{ $project->title }}
                    <x-status-badge :status="$project->status" />
                </h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('supervisor.dashboard') }}" class="btn btn-outline-primary">
                    <i class="ti ti-arrow-right me-1"></i>
                    العودة لمجموعاتي
                </a>
            </div>
        </div>
    </div>

    {{-- ===== لافتة الأرشفة بعد التقييم ===== --}}
    @if ($project->is_locked)
        <div class="alert alert-info mb-4" role="status">
            <div class="d-flex">
                <i class="ti ti-archive fs-2 me-2"></i>
                <div>
                    <h4 class="alert-title">مشروع مؤرشف — تم رصد التقييم</h4>
                    <div class="text-secondary">
                        المراحل والملفات والموعد النهائي أصبحت للعرض فقط حفاظاً على سلامة السجل.
                        يبقى النقاش مفتوحاً، ويمكنك تعديل الدرجة لتصحيح خطأ إدخال.
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== شريط مؤشرات ===== --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <span class="avatar bg-purple-lt text-purple rounded-3"><i class="ti ti-progress-check"></i></span>
                    <div>
                        <div class="h3 mb-0">{{ is_null($project->progress) ? '—' : $project->progress . '%' }}</div>
                        <div class="text-secondary small">نسبة الإنجاز</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <span class="avatar bg-{{ $deadlineTone }}-lt text-{{ $deadlineTone }} rounded-3"><i class="ti ti-alarm"></i></span>
                    <div>
                        <div class="h3 mb-0">
                            @if (is_null($daysLeft)) لم يُحدَّد
                            @elseif ($daysLeft < 0) انقضى
                            @else {{ $daysLeft }} يوم
                            @endif
                        </div>
                        <div class="text-secondary small">الموعد النهائي</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <span class="avatar bg-blue-lt text-blue rounded-3"><i class="ti ti-users-group"></i></span>
                    <div>
                        <div class="h3 mb-0">{{ $project->group->count() }}</div>
                        <div class="text-secondary small">أعضاء الفريق</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <span class="avatar bg-cyan-lt text-cyan rounded-3"><i class="ti ti-files"></i></span>
                    <div>
                        <div class="h3 mb-0">{{ $project->files->count() }}</div>
                        <div class="text-secondary small">الملفات المرفوعة</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-deck row-cards mb-4">
        {{-- ===== بيانات المشروع ===== --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-briefcase me-2"></i>بيانات المشروع</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid mb-3">
                        <div class="datagrid-item">
                            <div class="datagrid-title">نوع المشروع</div>
                            <div class="datagrid-content">{{ $project->project_type->name }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">الفصل</div>
                            <div class="datagrid-content">{{ $project->semester->name }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">تاريخ التقديم</div>
                            <div class="datagrid-content">{{ $project->created_at->format('Y-m-d') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">الموعد النهائي</div>
                            <div class="datagrid-content">
                                {{ $project->date_line ? $project->date_line->format('Y-m-d') : 'لم يُحدَّد بعد' }}
                            </div>
                        </div>
                    </div>
                    @if ($project->description)
                        <div class="datagrid-title mb-1">وصف المشروع</div>
                        <p class="mb-0 text-secondary">{{ $project->description }}</p>
                    @endif

                    {{-- الموعد النهائي + اكتمال (يُقفل بعد التقييم) --}}
                    @unless ($project->is_locked)
                        <div class="d-flex flex-wrap align-items-end gap-2 mt-4 pt-3 border-top">
                            <form action="{{ route('supervisor.deadline.update', ['project' => $project->id]) }}"
                                method="POST" class="d-flex align-items-end gap-2 flex-fill">
                                @csrf
                                <div class="flex-fill">
                                    <label class="form-label mb-1">الموعد النهائي</label>
                                    <input type="date" name="date_line" required class="form-control"
                                        value="{{ $project->date_line ? $project->date_line->format('Y-m-d') : '' }}">
                                </div>
                                <button type="submit" class="btn btn-outline-primary" data-loading-text="..">
                                    <i class="ti ti-alarm"></i>
                                </button>
                            </form>
                            @if ($project->status != 'complete')
                                <form action="{{ route('supervisor.project.complete', ['project_id' => $project->id]) }}" method="POST">
                                    @csrf
                                    <button name="btnAccept" value="accept" class="btn btn-success"
                                        onclick="return confirm('تأكيد اكتمال المشروع؟ سيُشعر الفريق.')">
                                        <i class="ti ti-check me-1"></i>
                                        اكتمال المشروع
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endunless
                </div>
            </div>
        </div>

        {{-- ===== فريق المشروع ===== --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-users-group me-2"></i>فريق المشروع</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>العضو</th>
                                <th>الرقم الجامعي</th>
                                <th>رقم الجوال</th>
                                <th>الدور</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($project->group as $group)
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
        </div>
    </div>

    <div class="row row-deck row-cards mb-4">
        {{-- ===== المراحل ===== --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-list-check me-2"></i>مراحل المشروع</h3>
                    @if (!is_null($project->progress))
                        <div class="card-actions small text-secondary">الإنجاز: {{ $project->progress }}%</div>
                    @endif
                </div>
                <div class="card-body">
                    @unless ($project->is_locked)
                        <form action="{{ route('supervisor.milestones.store', ['project' => $project->id]) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <input type="text" name="title" required maxlength="150" class="form-control"
                                        placeholder="عنوان المرحلة">
                                </div>
                                <div class="col-sm-4">
                                    <input type="date" name="due_date" class="form-control" aria-label="تاريخ الاستحقاق">
                                </div>
                                <div class="col-sm-2 d-grid">
                                    <button type="submit" class="btn btn-primary" data-loading-text="..">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endunless

                    @if ($project->milestones->count() === 0)
                        <p class="text-secondary mb-0">لا توجد مراحل بعد — أضف أول مرحلة ليتابعها الفريق.</p>
                    @else
                        @if (!is_null($project->progress))
                            <div class="progress mb-3" style="height: 8px">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%"
                                    aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endif
                        <div class="list-group list-group-flush">
                            @foreach ($project->milestones as $milestone)
                                <div class="list-group-item d-flex align-items-center gap-2 px-0">
                                    @if ($project->is_locked)
                                        {{-- عرض فقط بعد الأرشفة --}}
                                        <span class="avatar avatar-sm rounded {{ $milestone->is_done ? 'bg-green-lt text-green' : 'bg-secondary-lt' }}">
                                            <i class="ti {{ $milestone->is_done ? 'ti-check' : 'ti-clock' }}"></i>
                                        </span>
                                    @else
                                        <form action="{{ route('supervisor.milestones.toggle', ['milestone' => $milestone->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $milestone->is_done ? 'btn-success' : 'btn-outline-secondary' }}"
                                                title="{{ $milestone->is_done ? 'إرجاعها قيد التنفيذ' : 'وضع علامة منجزة' }}">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <div class="flex-fill min-w-0">
                                        <div class="{{ $milestone->is_done ? 'text-decoration-line-through text-secondary' : 'fw-bold' }}">
                                            {{ $milestone->title }}
                                        </div>
                                        @if ($milestone->due_date)
                                            <div class="small text-secondary">الاستحقاق: {{ $milestone->due_date->format('Y-m-d') }}</div>
                                        @endif
                                    </div>
                                    @unless ($project->is_locked)
                                        <form action="{{ route('supervisor.milestones.destroy', ['milestone' => $milestone->id]) }}"
                                            method="POST" onsubmit="return confirm('حذف هذه المرحلة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== الملفات ===== --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-files me-2"></i>ملفات المشروع</h3>
                </div>
                <div class="card-body">
                    @unless ($project->is_locked)
                        <form action="{{ route('supervisor.files.store', ['project' => $project->id]) }}" method="POST"
                            enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <input type="text" name="title" required maxlength="120" class="form-control"
                                        placeholder="اسم الملف">
                                </div>
                                <div class="col-sm-5">
                                    <input type="file" name="file" required class="form-control">
                                </div>
                                <div class="col-sm-2 d-grid">
                                    <button type="submit" class="btn btn-primary" data-loading-text="..">
                                        <i class="ti ti-upload"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endunless

                    @if ($project->files->count() === 0)
                        <p class="text-secondary mb-0">لا توجد ملفات مرفوعة بعد.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($project->files as $file)
                                <div class="list-group-item d-flex align-items-center gap-2 px-0">
                                    <span class="avatar avatar-sm bg-blue-lt text-blue rounded">
                                        <i class="ti ti-file-text"></i>
                                    </span>
                                    <div class="flex-fill min-w-0">
                                        <div class="fw-bold text-truncate">{{ $file->title }}</div>
                                        <div class="small text-secondary">
                                            {{ $file->human_size }}
                                            · {{ $file->uploader_type === \App\Models\Supervisor::class ? 'أنت' : ($file->uploader->name ?? 'طالب') }}
                                        </div>
                                    </div>
                                    <a href="{{ route('files.download', ['file' => $file->id]) }}"
                                        class="btn btn-sm btn-outline-primary" title="تنزيل">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    @unless ($project->is_locked)
                                        <form action="{{ route('supervisor.files.destroy', ['file' => $file->id]) }}" method="POST"
                                            onsubmit="return confirm('حذف هذا الملف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== التقييم النهائي ===== --}}
    @if ($project->status == 'complete')
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-award me-2"></i>التقييم النهائي</h3>
            </div>
            <div class="card-body">
                @if (!is_null($project->grade))
                    <div class="alert alert-success mb-3">
                        الدرجة الحالية: <strong>{{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }} / 100</strong>
                        ({{ $project->grade_label }})
                        @if ($project->evaluation_note) — {{ $project->evaluation_note }} @endif
                    </div>
                @endif
                <form action="{{ route('supervisor.project.evaluate', ['project' => $project->id]) }}" method="POST">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-3">
                            <label class="form-label mb-1">الدرجة (0–100)</label>
                            <input type="number" name="grade" min="0" max="100" step="0.5" required
                                class="form-control" value="{{ $project->grade }}">
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label mb-1">ملاحظات التقييم (اختياري)</label>
                            <input type="text" name="evaluation_note" maxlength="2000" class="form-control"
                                placeholder="ملاحظة ختامية للفريق.." value="{{ $project->evaluation_note }}">
                        </div>
                        <div class="col-sm-2 d-grid">
                            <button type="submit" class="btn btn-primary" data-loading-text="..">
                                {{ is_null($project->grade) ? 'حفظ التقييم' : 'تحديث' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== نقاش المشروع ===== --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-message-circle me-2"></i>نقاش المشروع</h3>
            <div class="card-actions small text-secondary">{{ $project->comments->count() }} تعليق</div>
        </div>
        <div class="card-body">
            @if ($project->comments->count() > 0)
                <div class="mb-3" style="max-height: 380px; overflow-y: auto;">
                    @foreach ($project->comments as $comment)
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
                                    <form action="{{ route('supervisor.comments.destroy', ['comment' => $comment->id]) }}"
                                        method="POST" class="ms-auto" onsubmit="return confirm('حذف هذا التعليق؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost-danger p-1" title="حذف">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="text-secondary mt-1" style="white-space: pre-line;">{{ $comment->body }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-secondary">لا توجد تعليقات بعد — ابدأ النقاش مع الفريق.</p>
            @endif

            <form action="{{ route('supervisor.comments.store', ['project' => $project->id]) }}" method="POST">
                @csrf
                <div class="d-flex gap-2">
                    <textarea name="body" rows="2" required maxlength="1000" class="form-control"
                        placeholder="اكتب ملاحظتك للفريق.."></textarea>
                    <button type="submit" class="btn btn-primary align-self-end" data-loading-text="..">
                        <i class="ti ti-send"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

@stop
