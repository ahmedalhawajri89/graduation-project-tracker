@extends('layouts.admin.admin')
@section('title', __('الفريق والأدوار'))

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">{{ __('لوحتي') }}</x-crumb>
    <x-crumb>{{ __('الفريق والأدوار') }}</x-crumb>
@endsection

{{--
    الفريق والأدوار — الأدوار في المركز، والأعضاء يُسنَدون إليها.

    كانت بطاقة لكل عضو تحمل كل الأدوار المقترحة (٧ وسوم × ٥ أعضاء)، ومصفوفة
    بجانبها تكرّر البيانات نفسها. صارت: لوحة أدوار بأفاتارات حامليها، وتحتها
    شريط الأعضاء بحمل كلٍّ ومسؤوليته — ومصدر واحد للحقيقة يرسمه الخادم،
    ويعيد المحرّر (للقائد) رسمه من حالته.
--}}

@section('content')

    @php
        $presets = \App\Support\TeamRoles::presetsFor($project);
        $presetKeys = array_column($presets, 'key');
        $max = \App\Support\TeamRoles::max();
        $meId = (int) auth('student')->id();
        $firstName = fn ($m) => (string) \Illuminate\Support\Str::of($m->student?->name ?? '')->explode(' ')->first();

        // الإسناد: من المدخل القديم بعد خطأ تحقّق، وإلا من القاعدة. الحرّ بمفتاح «custom:<تسمية>»
        $assign = [];
        foreach ($members as $m) {
            $old = old("members.{$m->id}.roles");
            $assign[$m->id] = is_array($old)
                ? array_values(array_filter($old))
                : $m->roles->map(fn ($r) => $r->role_key === 'custom' ? 'custom:' . $r->label : $r->role_key)->all();
        }

        // الأدوار: المقترحة، ثم ما حمله الفريق من خارجها (حرّ، أو من نوع آخر)
        $roles = collect($presets);
        foreach (collect($assign)->flatten()->unique() as $key) {
            if (in_array($key, $presetKeys, true)) {
                continue;
            }
            $def = str_starts_with($key, 'custom:')
                ? \App\Support\TeamRoles::custom(mb_substr($key, 7))
                : \App\Support\TeamRoles::find($key);
            if ($def) {
                $roles->push(['key' => $key] + $def);
            }
        }
        // تسميات الأدوار الجاهزة بلغة العرض — الحرّ نصّ كتبه القائد كما هو
        $roles = $roles->map(fn ($r) => str_starts_with($r['key'], 'custom:') ? $r : ['label' => __($r['label'])] + $r)->values();
        $loadLabel = fn ($n) => $n === 0 ? __('بلا دور') : ($n === 1 ? __('دور واحد') : ($n === 2 ? __('دوران') : __(':n أدوار', ['n' => $n])));

        $holders = fn ($key) => $members->filter(fn ($m) => in_array($key, $assign[$m->id], true))->values();
        $loads = collect($assign)->map(fn ($keys) => count($keys));
        $covered = $roles->filter(fn ($r) => $holders($r['key'])->isNotEmpty())->count();
        $empty = $loads->filter(fn ($n) => $n === 0)->count();

        $balance = match (true) {
            $loads->sum() === 0 => ['is-idle', 'ti-circle-dashed', __('لم يبدأ التوزيع')],
            $empty > 0 => ['is-warn', 'ti-user-question', $empty === 1 ? __('عضو بلا دور') : __(':n أعضاء بلا دور', ['n' => $empty])],
            $loads->max() - $loads->min() > 1 => ['is-warn', 'ti-scale', __('الحمل غير متوازن')],
            default => ['is-ok', 'ti-scale', __('الحمل متوازن')],
        };

        $hasErrors = $errors->has('members') || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'members.'));
    @endphp

    <x-page-header :title="__('الفريق والأدوار')" subtitle="{{ $project->title }}" />

    @if ($project->is_locked)
        <p class="hint-bar mb-3"><i class="ti ti-lock" aria-hidden="true"></i><span>{{ __('المشروع مؤرشف بعد التقييم — التوزيع سجلّ لا يُعدَّل.') }}</span></p>
    @elseif (! $isLeader)
        <p class="hint-bar mb-3"><i class="ti ti-info-circle" aria-hidden="true"></i><span>{{ __('القائد يوزّع الأدوار. لتغيير دورك، كلّمه أو اكتب في') }} <a href="{{ route('student.discussion') }}">{{ __('النقاش') }}</a>.</span></p>
    @endif

    @if ($hasErrors)
        <div class="alert alert-danger py-2 mb-3">{{ $errors->first('members') ?: collect($errors->all())->first() }}</div>
    @endif

    @if ($canAssign)
        <form action="{{ route('student.roles.update', $project->id) }}" method="POST" id="roles-form" class="roles-app">
            @csrf
            <div data-inputs hidden></div>
    @else
        <div class="roles-app is-readonly">
    @endif

        {{-- ===== الملخّص: التغطية والتوازن ===== --}}
        <section class="roles-summary-bar">
            <div class="roles-cover">
                <span class="roles-cover-label">
                    {{ __('التغطية') }}
                    <b data-cover-text>{{ __(':covered من :total أدوار', ['covered' => $covered, 'total' => $roles->count()]) }}</b>
                </span>
                <span class="roles-cover-track" aria-hidden="true">
                    <span data-cover-fill style="width: {{ $roles->count() ? round($covered * 100 / $roles->count()) : 0 }}%"></span>
                </span>
            </div>
            <span class="roles-balance {{ $balance[0] }}" data-balance>
                <i class="ti {{ $balance[1] }}" aria-hidden="true"></i>
                <span>{{ $balance[2] }}</span>
            </span>
            @if ($canAssign)
                <button type="button" class="btn btn-outline-primary roles-suggest" data-suggest>
                    <i class="ti ti-sparkles me-1" aria-hidden="true"></i>
                    {{ __('اقترح توزيعاً') }}
                </button>
            @endif
        </section>

        {{-- ===== لوحة الأدوار ===== --}}
        <section class="role-board" data-board aria-label="{{ __('الأدوار') }}">
            @foreach ($roles as $role)
                @php $held = $holders($role['key']); @endphp
                <article class="role-card {{ $held->isEmpty() ? 'is-vacant' : '' }} {{ $held->contains(fn ($m) => (int) $m->student_id === $meId) ? 'is-mine' : '' }}"
                    data-role="{{ $role['key'] }}" style="--h: {{ $role['hue'] }}">
                    <header class="role-card-head">
                        <span class="role-card-icon" aria-hidden="true"><i class="ti {{ $role['icon'] }}"></i></span>
                        <span class="role-card-title">
                            <b>{{ $role['label'] }}</b>
                            <small data-role-status>
                                {{ $held->isEmpty() ? __('بلا مسؤول') : ($held->count() === 1 ? __('مسؤول واحد') : __(':n مسؤولين', ['n' => $held->count()])) }}
                            </small>
                        </span>
                        @if (str_starts_with($role['key'], 'custom:'))
                            <span class="role-card-kind">{{ __('دور حرّ') }}</span>
                        @endif
                    </header>

                    <ul class="role-holders" data-holders>
                        @foreach ($held as $m)
                            <li class="role-holder {{ (int) $m->student_id === $meId ? 'is-me' : '' }}">
                                <x-avatar :user="$m->student" class="ctx-avatar" />
                                <span>{{ $firstName($m) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($canAssign)
                        <button type="button" class="role-assign-btn" data-assign="{{ $role['key'] }}"
                            aria-haspopup="menu" aria-label="{{ __('إسناد «:role»', ['role' => $role['label']]) }}">
                            <i class="ti ti-user-plus" aria-hidden="true"></i>
                            {{ __('إسناد') }}
                        </button>
                    @endif
                </article>
            @endforeach

            @if ($canAssign)
                {{-- دور خارج المقترحات: حقل بزرّه، واقتراحات بنقرة --}}
                @php
                    $ideas = collect([__('الأمن والصلاحيات'), __('النشر والاستضافة'), __('إدارة المخاطر'), __('تجربة المستخدم')])
                        ->reject(fn ($label) => $roles->contains('label', $label))->take(3);
                @endphp
                <article class="role-card is-add">
                    <header class="role-card-head">
                        <span class="role-card-icon" aria-hidden="true"><i class="ti ti-circle-plus"></i></span>
                        <span class="role-card-title">
                            <b>{{ __('دور جديد') }}</b>
                            <small>{{ __('خارج المقترحات — يُحفظ حين تسنده لعضو') }}</small>
                        </span>
                    </header>

                    <div class="role-add-box">
                        <i class="ti ti-tag" aria-hidden="true"></i>
                        <input type="text" maxlength="30" data-new-role
                            placeholder="{{ __('اسم الدور…') }}" aria-label="{{ __('اسم الدور الجديد') }}">
                        <button type="button" class="role-add-submit" data-new-role-add disabled aria-label="{{ __('إضافة الدور') }}">
                            <i class="ti ti-plus" aria-hidden="true"></i>
                            <span>{{ __('إضافة') }}</span>
                        </button>
                    </div>

                    @if ($ideas->isNotEmpty())
                        <div class="role-add-ideas">
                            <small>{{ __('اقتراحات:') }}</small>
                            @foreach ($ideas as $idea)
                                <button type="button" class="role-idea" data-role-idea="{{ $idea }}">{{ $idea }}</button>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endif
        </section>

        {{-- ===== الأعضاء: الحمل والمسؤولية ===== --}}
        <section class="roster" aria-labelledby="roster-title">
            <h2 id="roster-title" class="roster-title">
                <i class="ti ti-users" aria-hidden="true"></i>
                {{ __('الأعضاء') }}
                <span>{{ __('الحمل والمسؤولية') }}</span>
            </h2>

            @foreach ($members as $m)
                @php
                    $load = count($assign[$m->id]);
                    $isMe = (int) $m->student_id === $meId;
                    $resp = old("members.{$m->id}.responsibility", $m->responsibility);
                @endphp
                <div class="roster-row {{ $load === 0 ? 'is-empty' : '' }} {{ $isMe ? 'is-me' : '' }}" data-member="{{ $m->id }}">
                    <x-avatar :user="$m->student" class="ctx-avatar roster-avatar" />

                    <div class="roster-who">
                        <b>{{ $m->student?->name }}</b>
                        @if ($m->type === 'leader')
                            <span class="ctx-tag">{{ __('قائد') }}</span>
                        @endif
                        @if ($isMe)
                            <span class="cell-you">{{ __('أنت') }}</span>
                        @endif
                    </div>

                    <div class="roster-load" data-load title="{{ __(':load من :max', ['load' => $load, 'max' => $max]) }}">
                        <span class="load-dots" aria-hidden="true">
                            @for ($i = 1; $i <= $max; $i++)
                                <i class="{{ $i <= $load ? 'is-on' : '' }}"></i>
                            @endfor
                        </span>
                        <small data-load-text>{{ $loadLabel($load) }}</small>
                    </div>

                    <div class="roster-roles" data-chips>
                        @foreach ($assign[$m->id] as $key)
                            @php $def = $roles->firstWhere('key', $key); @endphp
                            @if ($def)
                                <span class="role-chip" style="--h: {{ $def['hue'] }}"><i class="ti {{ $def['icon'] }}" aria-hidden="true"></i>{{ $def['label'] }}</span>
                            @endif
                        @endforeach
                    </div>

                    <div class="roster-resp">
                        @if ($canAssign)
                            <span class="roster-resp-view {{ $resp ? '' : 'is-placeholder' }}" data-resp-view>{{ $resp ?: __('أضف سطراً يصف مسؤوليته…') }}</span>
                            <input type="text" name="members[{{ $m->id }}][responsibility]" maxlength="160" value="{{ $resp }}"
                                class="form-control form-control-sm roster-resp-input" data-resp hidden
                                aria-label="{{ __('مسؤولية :name', ['name' => $m->student?->name]) }}" placeholder="{{ __('مثال: واجهات الطالب والمشرف') }}">
                            <button type="button" class="btn-action" data-resp-edit aria-label="{{ __('تعديل مسؤولية :name', ['name' => $m->student?->name]) }}">
                                <i class="ti ti-pencil" aria-hidden="true"></i>
                            </button>
                        @elseif ($resp)
                            <span class="roster-resp-view">{{ $resp }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

    @if ($canAssign)
            {{-- شريط الحفظ يظهر حين يوجد ما يُحفظ --}}
            <div class="roles-savebar" data-savebar>
                <span class="roles-savebar-text">
                    <i class="ti ti-point-filled" aria-hidden="true"></i>
                    {{ __('تغييرات غير محفوظة') }}
                </span>
                <button type="button" class="btn btn-outline-secondary" data-reset>{{ __('تراجع') }}</button>
                <button type="submit" class="btn btn-primary" data-loading-text="{{ __('جارٍ الحفظ…') }}">
                    <i class="ti ti-check me-1" aria-hidden="true"></i>
                    {{ __('حفظ التوزيع') }}
                </button>
            </div>
        </form>

        {{-- قائمة الإسناد: واحدة تُنقل تحت الدور المنقور. على الجوال لوح من الأسفل --}}
        <div class="assign-menu" data-menu role="menu" hidden>
            <div class="assign-menu-head">
                <b data-menu-title></b>
                <button type="button" class="btn-close" data-menu-close aria-label="{{ __('إغلاق') }}"></button>
            </div>
            <div class="assign-menu-list">
                @foreach ($members as $m)
                    <button type="button" class="assign-option" role="menuitemcheckbox" aria-checked="false" data-option="{{ $m->id }}">
                        <x-avatar :user="$m->student" class="ctx-avatar" />
                        <span class="assign-option-name">
                            {{ $m->student?->name }}
                            <small data-option-load></small>
                        </span>
                        <span class="assign-check" aria-hidden="true"><i class="ti ti-check"></i></span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- أفاتار كل عضو قالباً يُنسخ في لوحة الأدوار --}}
        @foreach ($members as $m)
            <template data-avatar="{{ $m->id }}"><x-avatar :user="$m->student" class="ctx-avatar" /></template>
        @endforeach
    @else
        </div>
    @endif

@endsection

@if ($canAssign)
    @push('js')
        <script>
            (function () {
                var form = document.getElementById('roles-form');
                if (!form) return;

                var MAX = {{ $max }};
                var ME = {{ $meId }};
                var members = @json($members->map(fn ($m) => ['id' => $m->id, 'first' => $firstName($m), 'me' => (int) $m->student_id === $meId])->values());
                var roles = @json($roles->values());
                var initial = @json($assign);
                var state = JSON.parse(JSON.stringify(initial));
                var suggested = {};
                var T = {
                    load0: @json(__('بلا دور')),
                    load1: @json(__('دور واحد')),
                    load2: @json(__('دوران')),
                    loadN: @json(__(':n أدوار')),
                    none: @json(__('بلا مسؤول')),
                    one: @json(__('مسؤول واحد')),
                    many: @json(__(':n مسؤولين')),
                    cover: @json(__(':covered من :total أدوار')),
                    idle: @json(__('لم يبدأ التوزيع')),
                    empty1: @json(__('عضو بلا دور')),
                    emptyN: @json(__(':n أعضاء بلا دور')),
                    unbalanced: @json(__('الحمل غير متوازن')),
                    balanced: @json(__('الحمل متوازن')),
                    assignTo: @json(__('إسناد «:role»')),
                    assign: @json(__('إسناد')),
                    full: @json(__('بلغ الحدّ (:max)')),
                    custom: @json(__('دور حرّ')),
                    respPh: @json(__('أضف سطراً يصف مسؤوليته…')),
                };
                function fmt(s, o) { return s.replace(/:(\w+)/g, function (m, k) { return k in o ? o[k] : m; }); }

                var board = form.querySelector('[data-board]');
                var menu = document.querySelector('[data-menu]');
                var savebar = form.querySelector('[data-savebar]');
                var openRole = null;

                function roleOf(key) { return roles.find(function (r) { return r.key === key; }); }
                function load(id) { return (state[id] || []).length; }
                function holders(key) { return members.filter(function (m) { return state[m.id].indexOf(key) !== -1; }); }
                function loadText(n) { return n === 0 ? T.load0 : n === 1 ? T.load1 : n === 2 ? T.load2 : fmt(T.loadN, { n: n }); }
                function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
                function avatar(id) { return document.querySelector('template[data-avatar="' + id + '"]').innerHTML; }
                function dirty() { return JSON.stringify(state) !== JSON.stringify(initial) || respDirty(); }
                function respDirty() {
                    return Array.prototype.some.call(form.querySelectorAll('[data-resp]'), function (i) { return i.value !== i.defaultValue; });
                }

                // ===== الرسم: كل شيء من الحالة =====
                function render() {
                    board.querySelectorAll('[data-role]').forEach(function (card) {
                        var key = card.dataset.role, list = holders(key);
                        card.classList.toggle('is-vacant', list.length === 0);
                        card.classList.toggle('is-mine', list.some(function (m) { return m.me; }));
                        card.querySelector('[data-role-status]').textContent =
                            list.length === 0 ? T.none : list.length === 1 ? T.one : fmt(T.many, { n: list.length });
                        card.querySelector('[data-holders]').innerHTML = list.map(function (m) {
                            return '<li class="role-holder' + (m.me ? ' is-me' : '') + (suggested[m.id + '|' + key] ? ' is-suggested' : '') + '">' +
                                avatar(m.id) + '<span>' + esc(m.first) + '</span></li>';
                        }).join('');
                    });

                    form.querySelectorAll('[data-member]').forEach(function (row) {
                        var id = row.dataset.member, n = load(id);
                        row.classList.toggle('is-empty', n === 0);
                        row.querySelectorAll('.load-dots i').forEach(function (dot, i) { dot.classList.toggle('is-on', i < n); });
                        row.querySelector('[data-load-text]').textContent = loadText(n);
                        row.querySelector('[data-chips]').innerHTML = state[id].map(function (key) {
                            var r = roleOf(key);
                            return r ? '<span class="role-chip" style="--h:' + r.hue + '"><i class="ti ' + r.icon + '" aria-hidden="true"></i>' + esc(r.label) + '</span>' : '';
                        }).join('');
                    });

                    // الملخّص
                    var covered = roles.filter(function (r) { return holders(r.key).length; }).length;
                    form.querySelector('[data-cover-text]').textContent = fmt(T.cover, { covered: covered, total: roles.length });
                    form.querySelector('[data-cover-fill]').style.width = (roles.length ? Math.round(covered * 100 / roles.length) : 0) + '%';

                    var loads = members.map(function (m) { return load(m.id); });
                    var empty = loads.filter(function (n) { return n === 0; }).length;
                    var total = loads.reduce(function (a, b) { return a + b; }, 0);
                    var b = total === 0 ? ['is-idle', 'ti-circle-dashed', T.idle]
                        : empty ? ['is-warn', 'ti-user-question', empty === 1 ? T.empty1 : fmt(T.emptyN, { n: empty })]
                        : Math.max.apply(null, loads) - Math.min.apply(null, loads) > 1 ? ['is-warn', 'ti-scale', T.unbalanced]
                        : ['is-ok', 'ti-scale', T.balanced];
                    var bal = form.querySelector('[data-balance]');
                    bal.className = 'roles-balance ' + b[0];
                    bal.innerHTML = '<i class="ti ' + b[1] + '" aria-hidden="true"></i><span>' + b[2] + '</span>';

                    savebar.classList.toggle('is-visible', dirty());
                    if (openRole) renderMenu();
                }

                // ===== قائمة الإسناد =====
                function renderMenu() {
                    menu.querySelector('[data-menu-title]').textContent = fmt(T.assignTo, { role: roleOf(openRole).label });
                    menu.querySelectorAll('[data-option]').forEach(function (opt) {
                        var id = opt.dataset.option, on = state[id].indexOf(openRole) !== -1, n = load(id);
                        opt.setAttribute('aria-checked', on ? 'true' : 'false');
                        opt.classList.toggle('is-on', on);
                        opt.disabled = !on && n >= MAX;
                        opt.querySelector('[data-option-load]').textContent = n >= MAX && !on ? fmt(T.full, { max: MAX }) : loadText(n);
                    });
                }

                function openMenu(btn) {
                    openRole = btn.dataset.assign;
                    renderMenu();
                    menu.hidden = false;
                    var r = btn.getBoundingClientRect();
                    if (window.innerWidth >= 576) {
                        menu.style.top = (window.scrollY + r.bottom + 6) + 'px';
                        menu.style.left = Math.max(12, Math.min(window.scrollX + r.left + r.width - menu.offsetWidth, window.innerWidth - menu.offsetWidth - 12)) + 'px';
                    } else {
                        menu.style.top = menu.style.left = '';
                    }
                    menu.classList.add('is-open');
                    menu.querySelector('.assign-option:not(:disabled)')?.focus();
                }
                function closeMenu() {
                    menu.classList.remove('is-open');
                    menu.hidden = true;
                    openRole = null;
                }

                board.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-assign]');
                    if (!btn) return;
                    e.stopPropagation();
                    openRole === btn.dataset.assign ? closeMenu() : openMenu(btn);
                });
                menu.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (e.target.closest('[data-menu-close]')) return closeMenu();
                    var opt = e.target.closest('[data-option]');
                    if (!opt || opt.disabled) return;
                    var list = state[opt.dataset.option], i = list.indexOf(openRole);
                    i === -1 ? list.push(openRole) : list.splice(i, 1);
                    delete suggested[opt.dataset.option + '|' + openRole];
                    render();
                });
                document.addEventListener('click', function () { if (openRole) closeMenu(); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && openRole) closeMenu(); });

                // ===== دور جديد (حرّ) =====
                var newInput = form.querySelector('[data-new-role]');
                function addRole() {
                    var label = newInput.value.trim();
                    if (!label) return;
                    var key = 'custom:' + label;
                    if (roleOf(key)) { newInput.value = ''; return; }
                    roles.push({ key: key, label: label, icon: 'ti-tag', hue: 220 });
                    var card = document.createElement('article');
                    card.className = 'role-card is-vacant is-new';
                    card.dataset.role = key;
                    card.style.setProperty('--h', 220);
                    card.innerHTML = '<header class="role-card-head"><span class="role-card-icon" aria-hidden="true"><i class="ti ti-tag"></i></span>' +
                        '<span class="role-card-title"><b>' + esc(label) + '</b><small data-role-status>' + esc(T.none) + '</small></span>' +
                        '<span class="role-card-kind">' + esc(T.custom) + '</span></header><ul class="role-holders" data-holders></ul>' +
                        '<button type="button" class="role-assign-btn" data-assign="' + esc(key) + '" aria-haspopup="menu"><i class="ti ti-user-plus" aria-hidden="true"></i> ' + esc(T.assign) + '</button>';
                    board.insertBefore(card, board.querySelector('.role-card.is-add'));
                    newInput.value = '';
                    addBtn.disabled = true;
                    render();
                    openMenu(card.querySelector('[data-assign]'));
                }
                var addBtn = form.querySelector('[data-new-role-add]');
                addBtn.addEventListener('click', function (e) { e.stopPropagation(); addRole(); });
                newInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addRole(); } });
                // الزرّ يضيء حين يوجد ما يُضاف — لا زرّ نشط لحقل فارغ
                newInput.addEventListener('input', function () { addBtn.disabled = !newInput.value.trim(); });

                // اقتراح بنقرة: يُضاف دوراً ويُفتح إسناده، ويختفي من الاقتراحات
                form.querySelectorAll('[data-role-idea]').forEach(function (chip) {
                    chip.addEventListener('click', function (e) {
                        e.stopPropagation();
                        newInput.value = chip.dataset.roleIdea;
                        chip.remove();
                        addRole();
                    });
                });

                // ===== اقتراح توزيع: الشاغر للأقلّ حملاً، بالتناوب — اقتراح يُراجَع لا حفظ =====
                form.querySelector('[data-suggest]').addEventListener('click', function () {
                    var vacant = roles.filter(function (r) { return !holders(r.key).length; });
                    if (!vacant.length) {
                        this.classList.add('is-done');
                        return;
                    }
                    vacant.forEach(function (r) {
                        var pick = members
                            .filter(function (m) { return load(m.id) < MAX; })
                            .sort(function (a, b) { return load(a.id) - load(b.id); })[0];
                        if (pick) {
                            state[pick.id].push(r.key);
                            suggested[pick.id + '|' + r.key] = true;
                        }
                    });
                    render();
                });

                // ===== المسؤولية: تُعدَّل في مكانها =====
                form.querySelectorAll('[data-resp-edit]').forEach(function (btn) {
                    var row = btn.closest('[data-member]');
                    var view = row.querySelector('[data-resp-view]'), input = row.querySelector('[data-resp]');
                    function done() {
                        input.hidden = true;
                        view.hidden = false;
                        view.textContent = input.value.trim() || T.respPh;
                        view.classList.toggle('is-placeholder', !input.value.trim());
                        render();
                    }
                    btn.addEventListener('click', function () {
                        view.hidden = true;
                        input.hidden = false;
                        input.focus();
                    });
                    view.addEventListener('click', function () { btn.click(); });
                    input.addEventListener('blur', done);
                    input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); input.blur(); } });
                    input.addEventListener('input', function () { savebar.classList.toggle('is-visible', dirty()); });
                });

                // ===== تراجع وحفظ =====
                form.querySelector('[data-reset]').addEventListener('click', function () {
                    state = JSON.parse(JSON.stringify(initial));
                    suggested = {};
                    form.querySelectorAll('[data-resp]').forEach(function (i) {
                        i.value = i.defaultValue;
                        var v = i.closest('[data-member]').querySelector('[data-resp-view]');
                        v.textContent = i.value || T.respPh;
                        v.classList.toggle('is-placeholder', !i.value);
                    });
                    render();
                });

                form.addEventListener('submit', function () {
                    // الحقول من الحالة: عضو بلا دور يُرسَل فارغاً فتُمسح أدواره
                    var box = form.querySelector('[data-inputs]'), html = '';
                    members.forEach(function (m) {
                        html += '<input type="hidden" name="members[' + m.id + '][roles]" value="">';
                        state[m.id].forEach(function (key) {
                            html += '<input type="hidden" name="members[' + m.id + '][roles][]" value="' + esc(key).replace(/"/g, '&quot;') + '">';
                        });
                    });
                    box.innerHTML = html;
                    initial = JSON.parse(JSON.stringify(state));
                });

                window.addEventListener('beforeunload', function (e) {
                    if (dirty()) { e.preventDefault(); e.returnValue = ''; }
                });

                render();
            })();
        </script>
    @endpush
@endif
