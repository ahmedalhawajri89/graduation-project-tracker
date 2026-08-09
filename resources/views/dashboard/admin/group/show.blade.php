@extends('layouts.admin.admin')
@section('title', 'تفاصيل المشروع')

@section('content')

    @php
        $daysLeft = $project->days_left;
        $deadlineTone = is_null($daysLeft) ? 'secondary' : ($daysLeft <= 7 ? 'red' : ($daysLeft <= 14 ? 'yellow' : 'cyan'));
    @endphp

    {{-- ===== الترويسة ===== --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    عرض تفاصيل المشروع — {{ $project->project_type->name }} · الفصل: {{ $project->semester->name }}
                </div>
                <h2 class="page-title d-flex align-items-center gap-2 flex-wrap">
                    {{ $project->title }}
                    <x-status-badge :status="$project->status" />
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.groups.edit', $project->id) }}" class="btn btn-outline-primary">
                    <i class="ti ti-pencil me-1"></i>
                    تعديل المجموعة
                </a>
                <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-right me-1"></i>
                    العودة للمجموعات
                </a>
            </div>
        </div>
    </div>

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

    {{-- ===== التقييم (إن وُجد) ===== --}}
    @if (!is_null($project->grade))
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--ds-brand-50), #f5f3ff); border: 1px solid var(--ds-brand-200);">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <span class="avatar avatar-lg rounded-3" style="background: var(--ds-gradient-135); color: #fff;">
                    <i class="ti ti-award fs-2"></i>
                </span>
                <div>
                    <h3 class="mb-1">
                        التقييم النهائي: {{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }} / 100
                        <span class="badge bg-purple-lt text-purple ms-2">{{ $project->grade_label }}</span>
                    </h3>
                    @if ($project->evaluation_note)
                        <div class="text-secondary">ملاحظات المشرف: {{ $project->evaluation_note }}</div>
                    @endif
                    <div class="small text-secondary mt-1">قُيّم بتاريخ {{ $project->evaluated_at?->format('Y-m-d') }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="row row-deck row-cards mb-4">
        {{-- ===== بيانات المشروع والمشرف ===== --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-briefcase me-2"></i>بيانات المشروع</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid mb-3">
                        <div class="datagrid-item">
                            <div class="datagrid-title">المشرف</div>
                            <div class="datagrid-content">{{ $project->supervisor->name }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">تخصص المشرف</div>
                            <div class="datagrid-content">{{ $project->supervisor->specialize->name ?? '—' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">تاريخ التقديم</div>
                            <div class="datagrid-content">{{ $project->created_at->format('Y-m-d') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">الموعد النهائي</div>
                            <div class="datagrid-content">
                                {{ $project->date_line ? $project->date_line->format('Y-m-d') : 'لم يُحدَّد' }}
                            </div>
                        </div>
                    </div>
                    @if ($project->description)
                        <div class="datagrid-title mb-1">وصف المشروع</div>
                        <p class="mb-0 text-secondary">{{ $project->description }}</p>
                    @endif
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
                                <th>التخصص</th>
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
                                    <td>{{ $group->student->specialize->name ?? '—' }}</td>
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
        {{-- ===== المراحل (عرض فقط) ===== --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-list-check me-2"></i>مراحل المشروع</h3>
                    @if (!is_null($project->progress))
                        <div class="card-actions small text-secondary">الإنجاز: {{ $project->progress }}%</div>
                    @endif
                </div>
                <div class="card-body">
                    @if ($project->milestones->count() === 0)
                        <p class="text-secondary mb-0">لم يضِف المشرف مراحل لهذا المشروع.</p>
                    @else
                        @if (!is_null($project->progress))
                            <div class="progress mb-3" style="height: 8px">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%"
                                    aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endif
                        <div class="list-group list-group-flush">
                            @foreach ($project->milestones as $milestone)
                                <div class="list-group-item d-flex align-items-center gap-3 px-0">
                                    <span class="avatar avatar-sm rounded {{ $milestone->is_done ? 'bg-green-lt text-green' : 'bg-secondary-lt' }}">
                                        <i class="ti {{ $milestone->is_done ? 'ti-check' : 'ti-clock' }}"></i>
                                    </span>
                                    <div class="flex-fill min-w-0">
                                        <div class="{{ $milestone->is_done ? 'text-decoration-line-through text-secondary' : 'fw-bold' }}">
                                            {{ $milestone->title }}
                                        </div>
                                        @if ($milestone->due_date)
                                            <div class="small text-secondary">الاستحقاق: {{ $milestone->due_date->format('Y-m-d') }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== الملفات (تنزيل فقط) ===== --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-files me-2"></i>ملفات المشروع</h3>
                </div>
                <div class="card-body">
                    @if ($project->files->count() === 0)
                        <p class="text-secondary mb-0">لا توجد ملفات مرفوعة.</p>
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
                                            · {{ $file->uploader_type === \App\Models\Supervisor::class ? 'المشرف' : ($file->uploader->name ?? 'طالب') }}
                                            · {{ $file->created_at->format('Y-m-d') }}
                                        </div>
                                    </div>
                                    <a href="{{ route('files.download', ['file' => $file->id]) }}"
                                        class="btn btn-sm btn-outline-primary" title="تنزيل">
                                        <i class="ti ti-download"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== النقاش (عرض فقط) ===== --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-message-circle me-2"></i>نقاش المشروع</h3>
            <div class="card-actions small text-secondary">{{ $project->comments->count() }} تعليق — عرض فقط</div>
        </div>
        <div class="card-body">
            @if ($project->comments->count() === 0)
                <p class="text-secondary mb-0">لا توجد تعليقات بين الفريق والمشرف.</p>
            @else
                <div style="max-height: 380px; overflow-y: auto;">
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
                                </div>
                                <div class="text-secondary mt-1" style="white-space: pre-line;">{{ $comment->body }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection
