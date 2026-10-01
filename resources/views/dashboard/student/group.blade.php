{{--
    منتقي أعضاء الفريق.

    كان يُرسل كل المتاحين في الصفحة — أكثر من مئة في التخصص الواحد —
    ليُمسحوا بالعين، وهو ما لا يُمسح. البحث صار على الخادم، والنتائج
    محدودة بأربعين، فلا يكبر الحمل مع كبر الدفعة.

    والاختيارات تُحفظ في حقول مخفيّة **مستقلّة عن القائمة**: نتيجة
    البحث تتغيّر مع كل حرف، ولو كان الاختيار مربوطاً بصفوفها لضاع
    بمجرّد أن يختفي الصفّ.

    يرسل الأرقام الجامعية عبر student_ids[] — نفس عقد الـ backend.
--}}

@php
    $isStudentContext = auth()->guard('student')->check();
    $availableCount = $availableCount ?? 0;
    $searchUrl = $searchUrl ?? null;
    $picked = is_array(old('student_ids')) ? old('student_ids') : [];
@endphp

@error('student_ids')
    <div class="invalid-feedback d-block mb-2">
        <strong>{{ $message }}</strong>
    </div>
@enderror

<div class="team-picker" data-search-url="{{ $searchUrl }}" data-total="{{ $availableCount }}">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="form-hint mb-0">
            <i class="ti ti-info-circle me-1"></i>
            @if ($isStudentContext)
                {{ __('أنت قائد الفريق تلقائياً — ابحث عن بقية الأعضاء.') }}
            @else
                {{ __('ابحث عن الطلاب المراد إضافتهم (المتاحون فقط: بدون مشروع نشط).') }}
            @endif
        </div>
        @if ($isStudentContext)
            <span class="badge bg-primary-lt text-primary" id="picker-counter">{{ __('الفريق: :n', ['n' => 1]) }}</span>
        @endif
    </div>

    {{-- البحث وحده طريق مسدود لمن لا يعرف اسماً. والزرّ يفتح القائمة
         على دفعات لا دفعةً واحدة — وإلا عدنا إلى المئة اسم. --}}
    <div class="picker-bar mb-2">
        <div class="input-icon">
            <span class="input-icon-addon"><i class="ti ti-search"></i></span>
            <input type="search" id="picker-search" class="form-control" autocomplete="off"
                placeholder="{{ __('ابحث بالاسم أو الرقم الجامعي..') }}" aria-label="{{ __('بحث عن طالب') }}">
        </div>
        @if ($availableCount)
            <button type="button" class="btn btn-outline-primary" id="picker-browse">
                <i class="ti ti-list me-1" aria-hidden="true"></i>
                {{ __('عرض المتاحين') }}
            </button>
        @endif
    </div>

    {{-- العدد: من يرى «١٤٧ متاحاً» يعرف أن البحث هو الطريق --}}
    <p class="picker-count" id="picker-count">
        @if ($availableCount)
            {{ __(':n طالباً متاحاً — اكتب للبحث', ['n' => $availableCount]) }}
        @else
            {{ __('لا يوجد طلاب متاحون حالياً في هذا التخصص.') }}
        @endif
    </p>

    {{-- شرائح المختارين: تختار ثم تبحث عن غيره، فتفقد أثر ما اخترت --}}
    <div class="picker-chips d-none" id="picker-chips" aria-live="polite"></div>

    {{-- الحقول المرسَلة فعلاً — مستقلّة عن نتائج البحث --}}
    <div id="picker-selected" class="d-none">
        @foreach ($picked as $id)
            <input type="hidden" name="student_ids[]" value="{{ $id }}">
        @endforeach
    </div>

    <div class="picker-list" id="picker-list" role="group" aria-label="{{ __('نتائج البحث عن أعضاء') }}">
        <p class="picker-hint" id="picker-hint">
            <i class="ti ti-search" aria-hidden="true"></i>
            {{ __('اكتب حرفين للبحث، أو اعرض المتاحين.') }}
        </p>
    </div>
</div>

@push('js')
    <script>
        // ===== منتقي الفريق: بحث على الخادم + اختيارات مستقلّة =====
        (function () {
            var root = document.querySelector('.team-picker');
            if (!root) return;

            var url = root.dataset.searchUrl;
            var total = parseInt(root.dataset.total, 10) || 0;
            var search = document.getElementById('picker-search');
            var list = document.getElementById('picker-list');
            var hint = document.getElementById('picker-hint');
            var chips = document.getElementById('picker-chips');
            var store = document.getElementById('picker-selected');
            var countLine = document.getElementById('picker-count');
            var counter = document.getElementById('picker-counter');
            var typeSelect = document.getElementById('specialize_project_id');

            var T = {
                remove: @json(__('إزالة')),
                team: @json(__('الفريق: :n')),
                teamOf: @json(__('الفريق: :n من :max')),
                showing: @json(__('يُعرض :shown من :total متاحاً')),
                matched: @json(__(':shown من :matched مطابق')),
                none: @json(__('لا طالب متاح بهذا الاسم أو الرقم.')),
                more: @json(__('عرض المزيد')),
                left: @json(__(':n متبقٍ')),
                loading: @json(__('جارٍ التحميل…')),
                failed: @json(__('تعذّر التحميل. حاول مرة أخرى.')),
                hint: @json(__('اكتب حرفين للبحث، أو اعرض المتاحين.')),
                available: @json(__(':n طالباً متاحاً — اكتب للبحث')),
            };
            function fmt(s, o) {
                return s.replace(/:(\w+)/g, function (m, k) { return k in o ? o[k] : m; });
            }
            function esc(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            // الاسم يُحفظ مع المُعرِّف: الشريحة تبقى معروضة بعد أن
            // يختفي صفّ الطالب من نتيجة بحث جديدة
            var selected = new Map();
            store.querySelectorAll('input').forEach(function (i) {
                selected.set(i.value, i.value);
            });

            function limits() {
                var opt = typeSelect && typeSelect.selectedOptions[0];
                return {
                    min: opt && opt.dataset.min ? parseInt(opt.dataset.min, 10) : null,
                    max: opt && opt.dataset.max ? parseInt(opt.dataset.max, 10) : null,
                };
            }

            function syncStore() {
                store.innerHTML = '';
                selected.forEach(function (name, id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'student_ids[]';
                    input.value = id;
                    store.appendChild(input);
                });
            }

            function renderChips() {
                chips.classList.toggle('d-none', selected.size === 0);
                chips.innerHTML = '';

                selected.forEach(function (name, id) {
                    var chip = document.createElement('span');
                    chip.className = 'picker-chip';
                    chip.innerHTML = '<span></span><button type="button" aria-label="' + esc(T.remove) + '"><i class="ti ti-x"></i></button>';
                    chip.querySelector('span').textContent = name;
                    chip.querySelector('button').addEventListener('click', function () {
                        selected.delete(id);
                        refresh();
                        markRows();
                    });
                    chips.appendChild(chip);
                });
            }

            function refresh() {
                syncStore();
                renderChips();

                var l = limits();
                if (counter) {
                    var team = selected.size + 1; // + قائد الفريق
                    counter.textContent = l.max ? fmt(T.teamOf, { n: team, max: l.max }) : fmt(T.team, { n: team });
                    counter.className = 'badge ' + (l.max && team > l.max
                        ? 'bg-red-lt text-red'
                        : (l.min && team < l.min ? 'bg-yellow-lt text-yellow' : 'bg-green-lt text-green'));
                    if (!l.max && !l.min) counter.className = 'badge bg-primary-lt text-primary';
                }
            }

            /** يُعلّم الصفوف المعروضة حسب ما هو مختار فعلاً */
            function markRows() {
                list.querySelectorAll('.picker-item').forEach(function (row) {
                    row.classList.toggle('is-picked', selected.has(row.dataset.id));
                });
            }

            function render(data, term, append) {
                if (!append) list.innerHTML = '';

                // يُزال زرّ «عرض المزيد» قبل إلحاق الدفعة الجديدة
                var oldMore = document.getElementById('picker-more');
                if (oldMore) oldMore.remove();

                if (countLine) {
                    var shown = data.offset + data.results.length;
                    countLine.textContent = term === ''
                        ? fmt(T.showing, { shown: shown, total: data.total })
                        : fmt(T.matched, { shown: shown, matched: data.matched });
                }

                if (data.results.length === 0 && !append) {
                    list.innerHTML = '<p class="picker-hint"><i class="ti ti-user-off"></i> ' + esc(T.none) + '</p>';
                    return;
                }

                data.results.forEach(function (s) {
                    var row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'picker-item';
                    row.dataset.id = s.university_id;

                    var avatar = s.avatar_url
                        ? '<span class="picker-avatar has-photo"><img src="' + s.avatar_url + '" alt=""></span>'
                        : '<span class="picker-avatar"></span>';

                    row.innerHTML = avatar
                        + '<span class="picker-item-meta"><span class="fw-bold"></span>'
                        + '<span class="text-secondary small" dir="ltr"></span></span>'
                        + '<i class="ti ti-circle-check picker-item-check" aria-hidden="true"></i>';

                    if (!s.avatar_url) row.querySelector('.picker-avatar').textContent = s.initials;
                    row.querySelector('.fw-bold').textContent = s.name;
                    row.querySelector('.text-secondary').textContent = s.university_id;

                    row.addEventListener('click', function () {
                        if (selected.has(s.university_id)) {
                            selected.delete(s.university_id);
                        } else {
                            selected.set(s.university_id, s.name);
                        }
                        refresh();
                        markRows();
                    });

                    list.appendChild(row);
                });

                // «عرض المزيد» بدل تمرير لا نهائي: التمرير يُخفي حجم
                // ما بقي، والزرّ يقوله
                if (data.hasMore) {
                    var more = document.createElement('button');
                    more.type = 'button';
                    more.id = 'picker-more';
                    more.className = 'picker-more';
                    // الأيقونة تقول الاتجاه قبل أن يُقرأ النصّ، والعدد
                    // في \u200E<small>\u200E أهدأ من الدعوة نفسها
                    more.innerHTML = '<i class="ti ti-chevron-down"></i><span>' + esc(T.more) + '</span>'
                        + '<small>' + esc(fmt(T.left, { n: data.matched - (data.offset + data.results.length) })) + '</small>';
                    more.addEventListener('click', function () {
                        more.disabled = true;
                        more.innerHTML = '<i class="ti ti-loader-2"></i><span>' + esc(T.loading) + '</span>';
                        fetchPage(term, data.offset + data.results.length, true);
                    });
                    list.appendChild(more);
                }

                markRows();
            }

            var timer = null;
            var controller = null;

            function fetchPage(term, offset, append) {
                if (controller) controller.abort();
                controller = new AbortController();

                if (!append) {
                    list.innerHTML = '<p class="picker-hint"><i class="ti ti-loader-2"></i> ' + esc(T.loading) + '</p>';
                }

                var query = '?offset=' + offset + (term ? '&q=' + encodeURIComponent(term) : '');

                fetch(url + query, {
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal,
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { render(data, term, append); })
                    .catch(function (e) {
                        if (e.name === 'AbortError') return;
                        list.innerHTML = '<p class="picker-hint"><i class="ti ti-alert-circle"></i> ' + esc(T.failed) + '</p>';
                    });
            }

            function run() {
                var term = search.value.trim();

                if (term.length < 2) {
                    list.innerHTML = '<p class="picker-hint"><i class="ti ti-search"></i> ' + esc(T.hint) + '</p>';
                    if (countLine) countLine.textContent = fmt(T.available, { n: total });
                    return;
                }

                // طلب واحد حيّ: الكتابة السريعة تُلغي ما قبلها فلا
                // تصل نتيجة قديمة بعد الجديدة
                fetchPage(term, 0, false);
            }

            // تأخير: لا طلب لكل ضغطة مفتاح
            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(run, 250);
            });

            var browse = document.getElementById('picker-browse');
            if (browse) {
                browse.addEventListener('click', function () {
                    search.value = '';
                    fetchPage('', 0, false);
                });
            }

            // Enter داخل حقل بحث لا يُرسل النموذج
            search.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });

            if (typeSelect) typeSelect.addEventListener('change', refresh);

            refresh();
        })();
    </script>
@endpush
