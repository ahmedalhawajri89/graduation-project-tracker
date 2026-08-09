@extends('layouts.admin.admin')
@section('title', 'طلبات المشاريع')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">لوحة المشرف</div>
                <h2 class="page-title">الاشعارات التي لم يتم الرد عليها</h2>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-bell me-2"></i>
                إشعارات تغيير المجموعات
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان المشروع</th>
                        <th>نص الرسالة</th>
                        <th>تاريخ الاشعار</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (auth()->user()->notifications->where('type', 'App\Notifications\AdminChangeGroupNotify') as $notification)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $notification->data['project'] }}</td>
                            <td>{{ $notification->data['msg'] }}</td>
                            <td>{{ $notification->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
        $activityNotifications = auth()->user()->notifications
            ->where('type', 'App\Notifications\ProjectActivityNotify')
            ->take(10);
    @endphp
    @if ($activityNotifications->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-activity me-2"></i>
                    آخر تحديثات المشاريع
                </h3>
            </div>
            <div class="list-group list-group-flush">
                @foreach ($activityNotifications as $notification)
                    <div class="list-group-item d-flex gap-3">
                        <span class="avatar avatar-sm bg-primary-lt text-primary rounded-circle">
                            <i class="ti ti-bell"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="fw-bold">
                                {{ $notification->data['project'] ?? '' }}
                                <span class="text-secondary fw-normal small">
                                    — {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="text-secondary">
                                {{ $notification->data['supervisor_name'] ?? '' }}: {{ $notification->data['msg'] ?? '' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-briefcase me-2"></i>
                طلبات المشاريع الجديدة
            </h3>
        </div>
        <div class="card-body">
            <div class="accordion" id="accordion">
                @foreach (auth()->user()->unreadNotifications->where('type', 'App\Notifications\SuperVisorRequestProjectNotify') as $notification)
                    {{-- {{ dd($notification) }} --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-{{ $notification->id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ $notification->id }}" aria-expanded="false"
                                aria-controls="collapse-{{ $notification->id }}">
                                <span class="badge bg-primary-lt text-primary me-2">{{ $notification->data['type'] }}</span>
                                <strong class="text-primary">{{ $notification->data['title'] }}</strong>
                                <span class="ms-auto me-3 d-flex align-items-center gap-2 text-secondary small">
                                    <span><i class="ti ti-users me-1"></i>{{ count($notification->data['students'] ?? []) }} طلاب</span>
                                    <span><i class="ti ti-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $notification->id }}" class="accordion-collapse collapse"
                            aria-labelledby="heading-{{ $notification->id }}" data-bs-parent="#accordion">
                            <div class="accordion-body">
                                @if ($notification->data['description'])
                                    <h4>وصف المشروع</h4>
                                    <p>{{ $notification->data['description'] }}</p>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-vcenter table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>اسماء الطلبة</th>
                                                <th>الرقم الجامعي</th>
                                                <th>رقم الجوال</th>
                                                <th>تخصص الجامعة</th>
                                                <th>قائد الفريق</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($notification->data['students'] as $std)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $std['name'] }}</td>
                                                    <td>{{ $std['university_id'] }}</td>
                                                    <td>{{ $std['phone'] }}</td>
                                                    <td>{{ $std['specialize']['name'] }}</td>
                                                    <td>
                                                        @if ($std['groups']['0']['type'] == 'leader')
                                                            <i class="ti ti-check text-green"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 pt-3 border-top">
                                    <form
                                        action="{{ route('supervisor.replay.project', [
                                            'project_id' => $notification->data['project_id'],
                                            'notify_id' => $notification->id,
                                        ]) }}"
                                        method="POST">
                                        @csrf
                                        <label class="form-label mb-1" for="reason-{{ $notification->id }}">
                                            سبب الرفض <span class="text-secondary">(اختياري — يصل للطلاب عند الرفض فقط)</span>
                                        </label>
                                        <textarea id="reason-{{ $notification->id }}" name="reason" rows="2" maxlength="500"
                                            class="form-control mb-3"
                                            placeholder="مثال: الفكرة منفّذة سابقاً، أو تحتاج توضيحاً أكثر لنطاق المشروع.."></textarea>

                                        <div class="d-flex flex-wrap gap-2">
                                            <button name="btnAccept" value='accept' class="btn btn-success"
                                                onclick="return confirm('قبول هذا المشروع؟')">
                                                <i class="ti ti-check me-1"></i>
                                                قبول المشروع
                                            </button>
                                            <button name="btnReject" value="reject" class="btn btn-outline-danger"
                                                onclick="return confirm('رفض هذا المشروع؟ سيصل الطلابَ إشعارٌ بالرفض والسبب إن كتبته.')">
                                                <i class="ti ti-x me-1"></i>
                                                رفض المشروع
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@stop
