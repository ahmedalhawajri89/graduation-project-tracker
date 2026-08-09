{{--
    منتقي أعضاء الفريق:
    قائمة قابلة للبحث بطلاب نفس التخصص المتاحين (بدون مشروع نشط).
    يرسل الأرقام الجامعية عبر student_ids[] — نفس عقد الـ backend القديم.
--}}

@php
    // حماية: الصفحات التي تضمّن هذا الجزء يجب أن تمرر $availableStudents — وإلا قائمة فارغة
    $availableStudents = $availableStudents ?? collect();
    $isStudentContext = auth()->guard('student')->check();
@endphp

@error('student_ids')
    <div class="invalid-feedback d-block mb-2">
        <strong>{{ $message }}</strong>
    </div>
@enderror

<div class="team-picker">

    {{-- ملخص الاختيار --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="form-hint mb-0">
            <i class="ti ti-info-circle me-1"></i>
            @if ($isStudentContext)
                أنت قائد الفريق تلقائياً — اختر بقية الأعضاء من القائمة.
            @else
                اختر الطلاب المراد إضافتهم للمجموعة (المتاحون فقط: بدون مشروع نشط).
            @endif
        </div>
        @if ($isStudentContext)
            <span class="badge bg-primary-lt text-primary" id="picker-counter">الفريق: 1</span>
        @endif
    </div>

    {{-- بحث --}}
    <div class="input-icon mb-2">
        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
        <input type="search" id="picker-search" class="form-control"
            placeholder="ابحث بالاسم أو الرقم الجامعي.." aria-label="بحث عن طالب">
    </div>

    {{-- القائمة --}}
    @if ($availableStudents->count() === 0)
        <div class="empty py-4 border rounded-3">
            <div class="empty-icon"><i class="ti ti-user-off fs-1"></i></div>
            <p class="empty-subtitle text-secondary">
                لا يوجد طلاب متاحون حالياً في تخصصك — جميعهم منضمون لفرق.
            </p>
        </div>
    @else
        <div class="picker-list" id="picker-list" role="group" aria-label="اختيار أعضاء الفريق">
            @foreach ($availableStudents as $mate)
                <label class="picker-item"
                    data-search="{{ mb_strtolower($mate->name . ' ' . $mate->university_id) }}">
                    <input type="checkbox" class="form-check-input picker-check" name="student_ids[]"
                        value="{{ $mate->university_id }}"
                        @if (is_array(old('student_ids')) && in_array($mate->university_id, old('student_ids'))) checked @endif>
                    <span class="avatar avatar-sm bg-primary-lt text-primary rounded-circle">
                        {{ mb_substr($mate->name, 0, 2) }}
                    </span>
                    <span class="picker-item-meta">
                        <span class="fw-bold">{{ $mate->name }}</span>
                        <span class="text-secondary small" dir="ltr">{{ $mate->university_id }}</span>
                    </span>
                    <i class="ti ti-circle-check picker-item-check" aria-hidden="true"></i>
                </label>
            @endforeach
            <div class="empty py-3 d-none" id="picker-empty">
                <p class="empty-subtitle text-secondary mb-0">لا نتائج مطابقة.</p>
            </div>
        </div>
    @endif
</div>

@push('js')
    <script>
        // ===== منتقي الفريق: بحث + عدّاد + حد أقصى حسب نوع المشروع =====
        (function () {
            var search = document.getElementById('picker-search');
            var items = Array.prototype.slice.call(document.querySelectorAll('.picker-item'));
            var checks = Array.prototype.slice.call(document.querySelectorAll('.picker-check'));
            var counter = document.getElementById('picker-counter');
            var emptyMsg = document.getElementById('picker-empty');
            var typeSelect = document.getElementById('specialize_project_id');

            // الحدود من نوع المشروع المختار (data-min / data-max على الـ option)
            function limits() {
                var opt = typeSelect && typeSelect.selectedOptions[0];
                return {
                    min: opt && opt.dataset.min ? parseInt(opt.dataset.min, 10) : null,
                    max: opt && opt.dataset.max ? parseInt(opt.dataset.max, 10) : null,
                };
            }

            function refresh() {
                var selected = checks.filter(function (c) { return c.checked; }).length;
                var team = selected + 1; // + قائد الفريق
                var l = limits();

                if (counter) {
                    counter.textContent = 'الفريق: ' + team + (l.max ? ' من ' + l.max : '');
                    counter.className = 'badge ' + (l.max && team > l.max
                        ? 'bg-red-lt text-red'
                        : (l.min && team < l.min ? 'bg-yellow-lt text-yellow' : 'bg-green-lt text-green'));
                    if (!l.max && !l.min) counter.className = 'badge bg-primary-lt text-primary';
                }

                // منع تجاوز الحد الأقصى: عطّل غير المحدد عند الامتلاء
                var full = l.max && team >= l.max;
                checks.forEach(function (c) {
                    c.disabled = full && !c.checked;
                    c.closest('.picker-item').classList.toggle('disabled', c.disabled);
                });
            }

            function filter() {
                var q = search.value.trim().toLowerCase();
                var visible = 0;
                items.forEach(function (el) {
                    var show = el.getAttribute('data-search').indexOf(q) !== -1;
                    el.classList.toggle('d-none', !show);
                    if (show) visible++;
                });
                if (emptyMsg) emptyMsg.classList.toggle('d-none', visible > 0);
            }

            if (search) search.addEventListener('input', filter);
            checks.forEach(function (c) { c.addEventListener('change', refresh); });
            if (typeSelect) typeSelect.addEventListener('change', refresh);
            refresh();
        })();
    </script>
@endpush
