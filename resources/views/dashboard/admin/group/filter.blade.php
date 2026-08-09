<div class="card mb-4">
    <div class="card-body">

        <form action="{{ route('admin.groups.index') }}" method="get">
            <div class="row g-2 align-items-end">

                <div class="col-sm-3">
                    <label class="form-label mb-1">الفصل الدراسي</label>
                    <select name="semester" class="form-select" aria-label="فلترة حسب الفصل">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @if ($currentSemesterId == $sem->id) selected @endif>
                                {{ $sem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3">
                    <label class="form-label mb-1">المشرف</label>
                    <select name="supervisor" class="form-select" aria-label="فلترة حسب المشرف">
                        <option value="">كل المشرفين</option>
                        @foreach ($supervisors as $supervisor)
                            <option value='{{ $supervisor->id }}' @if (request()->supervisor == $supervisor->id) selected @endif>
                                {{ $supervisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3">
                    <label class="form-label mb-1">نوع المشروع</label>
                    <select name="type" class="form-select" aria-label="فلترة حسب نوع المشروع">
                        <option value="">كل الأنواع</option>
                        @foreach ($project_type as $type)
                            <option value='{{ $type->id }}' @if (request()->type == $type->id) selected @endif>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="ti ti-filter me-1"></i>
                        بحث
                    </button>
                    <a href="{{ route('admin.groups.export', ['semester' => $currentSemesterId]) }}"
                        class="btn btn-outline-success" title="تصدير كشف النتائج لهذا الفصل (Excel)">
                        <i class="ti ti-file-spreadsheet me-1"></i>
                        تصدير
                    </a>
                </div>

            </div>
        </form>

    </div>
</div>
