<form action="{{ route('student.project.create') }}" method="post">
    @csrf

    <div class="mb-3">
        <label for="specialize_project_id" class="form-label">{{ __('نوع المشروع') }}</label>
        <select id="specialize_project_id" class="form-select @error('specialize_project_id') is-invalid @enderror"
            name="specialize_project_id">
            <option value="">{{ __('اختر نوع المشروع') }}</option>
            @foreach ($student->specialize->projects as $project)
                <option @if (old('specialize_project_id') == $project->id) selected @endif value="{{ $project->id }}"
                    data-min="{{ $project->min }}" data-max="{{ $project->max }}">
                    {{ $project->name }}
                    "{{ __('المجموعة تتكون من :min - :max طالب', ['min' => $project->min, 'max' => $project->max]) }}"
                </option>
            @endforeach
        </select>
        @error('specialize_project_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="supervisor_id" class="form-label">{{ __('مشرف المشروع') }}</label>
        <select id="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror" name="supervisor_id">
            <option value="">{{ __('اختر مشرف المشروع') }}</option>
            @foreach ($student->specialize->supervisorsAvailable as $supervisor)
                @if ($supervisor->max_group > $supervisor->projects_accept_count)
                    <option @if (old('supervisor_id') == $supervisor->id) selected @endif value="{{ $supervisor->id }}">
                        {{ $supervisor->name }}
                        — {{ __('متبقي :left من :max مجموعات', ['left' => $supervisor->max_group - $supervisor->projects_accept_count, 'max' => $supervisor->max_group]) }}
                    </option>
                @endif
            @endforeach
        </select>
        @error('supervisor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="title" class="form-label">{{ __('عنوان المشروع') }}</label>
        <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title"
            value="{{ old('title') }}" placeholder="{{ __('عنوان المشروع') }}" required autocomplete="off">
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        {{-- «هل نُفّذت فكرتي؟» في لحظتها — تنبيه لا يمنع الإرسال --}}
        <div class="hint-bar similar-hint mt-2 d-none" id="similar-hint" role="status" aria-live="polite">
            <i class="ti ti-bulb" aria-hidden="true"></i>
            <div>
                <b>{{ __('مشاريع منجزة مشابهة') }}</b> — {{ __('راجعها لتميّز فكرتك عنها:') }}
                <ul id="similar-list"></ul>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">{{ __('وصف المشروع') }}</label>
        <textarea rows="8" name="description" id="description" maxlength="500"
            class="form-control @error('description') is-invalid @enderror" placeholder="{{ __('وصف مختصر للمشروع ان وجد') }}">{{ old('description') }}</textarea>
        <div class="form-hint d-flex justify-content-between">
            <span>{{ __('اشرح فكرة المشروع والمشكلة التي يحلها.') }}</span>
            <span><span id="desc-counter">0</span> / 500</span>
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

                // مشاريع منجزة مشابهة للعنوان — بتأخير حتى يتوقّف عن الكتابة
                var title = document.getElementById('title');
                var hint = document.getElementById('similar-hint');
                var list = document.getElementById('similar-list');
                if (!title || !hint || !list) return;

                var timer = null, last = '';
                function check() {
                    var q = title.value.trim();
                    if (q === last) return;
                    last = q;
                    if (q.length < 4) { hint.classList.add('d-none'); return; }

                    fetch(@json(route('student.projects.similar')) + '?title=' + encodeURIComponent(q), {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(function (r) { return r.ok ? r.json() : { results: [] }; })
                        .then(function (data) {
                            if (q !== last) return; // ردّ قديم وصل بعد كتابة جديدة
                            list.textContent = '';
                            (data.results || []).forEach(function (p) {
                                // textContent لا innerHTML: العناوين نصّ كتبه طلاب
                                var li = document.createElement('li');
                                var a = document.createElement('a');
                                a.href = p.url;
                                a.target = '_blank';
                                a.textContent = p.title;
                                li.appendChild(a);
                                li.appendChild(document.createTextNode(
                                    ' — ' + (p.supervisor || '') + (p.semester ? @json(__('، ')) + p.semester : '')
                                ));
                                list.appendChild(li);
                            });
                            hint.classList.toggle('d-none', !list.children.length);
                        })
                        .catch(function () { hint.classList.add('d-none'); });
                }
                title.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(check, 400);
                });
                check();
            })();
        </script>
    @endpush

    <h3 class="mt-4 mb-3">
        <i class="ti ti-users-group me-2"></i>
        {{ __('إدخال بيانات المجموعة') }}
    </h3>

    <div class="mb-3">
        <label class="form-label">{{ __('قائد المجموعة') }}</label>
        <div class="input-icon">
            <span class="input-icon-addon"><i class="ti ti-crown text-yellow"></i></span>
            <input type="text" class="form-control" name="student_ids[]" value="{{ $student->university_id }}"
                readonly autocomplete="off" aria-label="{{ __('الرقم الجامعي لقائد المجموعة') }}">
        </div>
        <div class="form-hint">{{ $student->name }} — {{ __('أنت قائد هذا الفريق.') }}</div>
    </div>

    @include('dashboard.student.group')

    <div class="mt-4">
        <button name="registerbtn" type="submit" class="btn btn-primary">
            <i class="ti ti-send me-1"></i>
            {{ __('إرسال البيانات') }}
        </button>
    </div>

</form>
