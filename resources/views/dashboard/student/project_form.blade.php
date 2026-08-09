<form action="{{ route('student.project.create') }}" method="post">
    @csrf
    <input type="hidden" name="semester_id" value="{{ $semester->id }}">

    <div class="mb-3">
        <label for="specialize_project_id" class="form-label">نوع المشروع</label>
        <select id="specialize_project_id" class="form-select @error('specialize_project_id') is-invalid @enderror"
            name="specialize_project_id">
            <option value="">اختر نوع المشروع</option>
            @foreach ($student->specialize->projects as $project)
                <option @if (old('specialize_project_id') == $project->id) selected @endif value="{{ $project->id }}"
                    data-min="{{ $project->min }}" data-max="{{ $project->max }}">
                    {{ $project->name }}
                    "المجموعة تتكون من {{ $project->min }} - {{ $project->max }} طالب"
                </option>
            @endforeach
        </select>
        @error('specialize_project_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="supervisor_id" class="form-label">مشرف المشروع</label>
        <select id="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror" name="supervisor_id">
            <option value="">اختر مشرف المشروع</option>
            @foreach ($student->specialize->supervisorsAvailable as $supervisor)
                @if ($supervisor->max_group > $supervisor->projects_accept_count)
                    <option @if (old('supervisor_id') == $supervisor->id) selected @endif value="{{ $supervisor->id }}">
                        {{ $supervisor->name }}
                        — متبقي {{ $supervisor->max_group - $supervisor->projects_accept_count }} من {{ $supervisor->max_group }} مجموعات
                    </option>
                @endif
            @endforeach
        </select>
        @error('supervisor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="title" class="form-label">عنوان المشروع</label>
        <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title"
            value="{{ old('title') }}" placeholder="عنوان المشروع" required autocomplete="off">
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">وصف المشروع</label>
        <textarea rows="8" name="description" id="description" maxlength="1000"
            class="form-control @error('description') is-invalid @enderror" placeholder="وصف مختصر للمشروع ان وجد">{{ old('description') }}</textarea>
        <div class="form-hint d-flex justify-content-between">
            <span>اشرح فكرة المشروع والمشكلة التي يحلها.</span>
            <span><span id="desc-counter">0</span> / 1000</span>
        </div>
        @error('description')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    @push('js')
        <script>
            (function () {
                var desc = document.getElementById('description');
                var counter = document.getElementById('desc-counter');
                if (desc && counter) {
                    var update = function () { counter.textContent = desc.value.length; };
                    desc.addEventListener('input', update);
                    update();
                }
            })();
        </script>
    @endpush

    <h3 class="mt-4 mb-3">
        <i class="ti ti-users-group me-2"></i>
        إدخال بيانات المجموعة
    </h3>

    <div class="mb-3">
        <label class="form-label">قائد المجموعة</label>
        <div class="input-icon">
            <span class="input-icon-addon"><i class="ti ti-crown text-yellow"></i></span>
            <input type="text" class="form-control" name="student_ids[]" value="{{ $student->university_id }}"
                readonly autocomplete="off" aria-label="الرقم الجامعي لقائد المجموعة">
        </div>
        <div class="form-hint">{{ $student->name }} — أنت قائد هذا الفريق.</div>
    </div>

    @include('dashboard.student.group')

    <div class="mt-4">
        <button name="registerbtn" type="submit" class="btn btn-primary">
            <i class="ti ti-send me-1"></i>
            إرسال البيانات
        </button>
    </div>

</form>
