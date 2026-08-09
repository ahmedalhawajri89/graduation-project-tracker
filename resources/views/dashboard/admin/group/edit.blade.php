@extends('layouts.admin.admin')
@section('title', 'تعديل بيانات المجموعة')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إدارة البيانات</div>
                <h2 class="page-title">تعديل بيانات المجموعة</h2>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-users-group me-2"></i>
                بيانات المجموعة (الطلاب)
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الرقم الجامعي</th>
                        <th>البريد الالكتروني</th>
                        <th>رقم الجوال</th>
                        <th>التخصص الجامعي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->group as $group)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $group->student->name }}</td>
                            <td>{{ $group->student->university_id }}</td>
                            <td>{{ $group->student->email }}</td>
                            <td>{{ $group->student->phone }}</td>
                            <td>{{ $group->student->specialize->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ************************************** -->

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-user-star me-2"></i>
                بيانات المشرف
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الرقم الجامعي</th>
                        <th>البريد الالكتروني</th>
                        <th>رقم الجوال</th>
                        <th>التخصص الجامعي</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>{{ $project->supervisor->name }}</td>
                        <td>{{ $project->supervisor->university_id }}</td>
                        <td>{{ $project->supervisor->email }}</td>
                        <td>{{ $project->supervisor->phone }}</td>
                        <td>{{ $project->supervisor->specialize->name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ************************************** -->

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-pencil me-2"></i>
                تعديل بيانات المجموعة
            </h3>
        </div>
        <div class="card-body">
            @php
                $currentCount = $project->group->count();
                $typeMax = $project->project_type->max ?? null;
                $overLimit = $typeMax && $currentCount >= $typeMax;
            @endphp

            {{-- تنبيه حدود الفريق: الأدمن يستطيع التجاوز لكن بوعي --}}
            @if ($typeMax)
                <div class="alert {{ $overLimit ? 'alert-warning' : 'alert-info' }} mb-3" role="status">
                    <div class="d-flex">
                        <i class="ti {{ $overLimit ? 'ti-alert-triangle' : 'ti-info-circle' }} fs-2 me-2"></i>
                        <div>
                            الفريق حالياً <strong>{{ $currentCount }}</strong> طلاب،
                            والحد الأقصى لنوع "{{ $project->project_type->name }}" هو <strong>{{ $typeMax }}</strong>.
                            @if ($overLimit)
                                <div class="mt-1">المجموعة بلغت الحد الأقصى — أي إضافة ستتجاوزه (مسموح إدارياً للحالات الاستثنائية).</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.groups.update') }}" method="post" id="group-edit-form"
                data-current="{{ $currentCount }}" data-max="{{ $typeMax ?? 0 }}">
                @csrf
                <input type="hidden" name="id" value="{{ $project->id }}">

                <div class="mb-3">
                    <label class="form-label">تغير اسم المشرف</label>
                    <select class="form-select" name="supervisor_id">
                        <option></option>
                        @foreach ($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                        @endforeach
                    </select>
                </div>

                @include('dashboard.student.group')

                <div class="mt-3">
                    <button name="registerbtn" type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        حفظ
                    </button>
                </div>

            </form>
        </div>
    </div>

    @push('js')
        <script>
            // ===== تأكيد عند تجاوز الحد الأقصى لأعضاء الفريق =====
            (function () {
                var form = document.getElementById('group-edit-form');
                if (!form) return;

                form.addEventListener('submit', function (e) {
                    var max = parseInt(form.dataset.max, 10);
                    if (!max) return; // لا حد معرّف لهذا النوع

                    var current = parseInt(form.dataset.current, 10) || 0;
                    var selected = form.querySelectorAll('input[name="student_ids[]"]:checked').length;
                    var total = current + selected;

                    if (total > max) {
                        var ok = confirm(
                            'تنبيه: سيصبح عدد أعضاء الفريق ' + total +
                            ' وهو أكبر من الحد الأقصى (' + max + ') لهذا النوع.\n\nهل تريد المتابعة كاستثناء إداري؟'
                        );
                        if (!ok) e.preventDefault();
                    }
                });
            })();
        </script>
    @endpush

@endsection
