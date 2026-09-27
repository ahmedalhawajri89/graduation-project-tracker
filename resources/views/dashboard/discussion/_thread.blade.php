{{--
    محادثة مشروع واحد — مشتركة بين الطالب والمشرف.

    المجرى يتمرّر داخل صندوقه والنموذج مثبّت أسفله، فالرسائل مهما كثرت
    لا تدفع الصفحة. فاصل «رسائل جديدة» يقع عند ما كان غير مقروء لحظة
    الفتح، والتمرير يبدأ عنده.

    فقاعات لا صفوف: رسائلي في جهة ورسائل الآخرين في الأخرى، والمتتالية من
    الكاتب نفسه خلال دقائق تُجمع تحت اسم واحد، والأيام تفصلها عناوين.

    @param \App\Models\Project $project   بتعليقاته وكتّابها
    @param int|null            $lastRead  آخر تعليق قرأه قبل هذا الفتح
    @param string              $role      student | supervisor
    @param \Illuminate\Support\Collection|null $comments  رسائل القناة المعروضة (الافتراضي: قناة المشرف)
    @param string|null         $channel   supervisor | team — نقاش الفريق للطالب وحده، بذكر @
--}}

@php
    $me = auth($role)->user();
    $meType = $role === 'supervisor' ? \App\Models\Supervisor::class : \App\Models\Student::class;
    $isMine = fn ($c) => $c->author_type === $meType && (int) $c->author_id === (int) $me->id;

    $channel = $channel ?? \App\Models\ProjectComment::SUPERVISOR;
    $isTeam = $channel === \App\Models\ProjectComment::TEAM;
    $comments = $comments ?? $project->comments;

    // الزملاء للذكر بـ@: الفريق عدا الكاتب
    $mates = $isTeam
        ? $project->group->filter(fn ($g) => $g->student && (int) $g->student_id !== (int) $me->id)->map(fn ($g) => $g->student)->values()
        : collect();
    $names = $project->group->mapWithKeys(fn ($g) => [(int) $g->student_id => $g->student?->name]);

    // «@الاسم» وسمٌ في الفقاعة — للمذكورين المسجَّلين وحدهم، والنصّ مُهرَّب قبلها
    $renderBody = function ($c) use ($names) {
        $html = e($c->body);
        foreach ($c->mentions ?? [] as $id) {
            if ($name = $names[(int) $id] ?? null) {
                $html = str_replace('@' . e($name), '<span class="mention">@' . e($name) . '</span>', $html);
            }
        }

        return $html;
    };

    // أول رسالة من غيري لم أقرأها — عندها الفاصل
    $firstNew = $comments->first(
        fn ($c) => ! $isMine($c) && (is_null($lastRead) || $c->id > $lastRead)
    );

    $dayLabel = function ($at) {
        if ($at->isToday()) return 'اليوم';
        if ($at->isYesterday()) return 'أمس';

        return $at->translatedFormat($at->isCurrentYear() ? 'l j F' : 'j F Y');
    };

    $prev = null;
@endphp

<div class="chat-stream" id="chat-stream">
    @forelse ($comments as $comment)
        @php
            $mine = $isMine($comment);
            $newDay = ! $prev || ! $prev->created_at->isSameDay($comment->created_at);
            $isNew = $firstNew && $comment->id === $firstNew->id;

            // تتمّة: الكاتب نفسه خلال خمس دقائق، بلا فاصل يوم أو «جديد» بينهما
            $cont = $prev && ! $newDay && ! $isNew
                && $prev->author_type === $comment->author_type
                && (int) $prev->author_id === (int) $comment->author_id
                && $prev->created_at->diffInMinutes($comment->created_at) < 5;
            $prev = $comment;
        @endphp

        @if ($newDay)
            <div class="chat-day"><span>{{ $dayLabel($comment->created_at) }}</span></div>
        @endif

        @if ($isNew)
            <div class="chat-new" id="chat-new"><span>رسائل جديدة</span></div>
        @endif

        {{-- رسالة تذكرني: تُميَّز بحدّ ولون الهوية --}}
        @php $mentionsMe = $isTeam && $role === 'student' && ! $mine && $comment->mentions((int) $me->id); @endphp
        <div class="msg {{ $mine ? 'is-mine' : '' }} {{ $cont ? 'is-cont' : '' }} {{ $comment->is_supervisor ? 'is-supervisor' : '' }} {{ $mentionsMe ? 'is-mentioned' : '' }}">
            @unless ($mine)
                @if ($cont)
                    <span class="msg-avatar-gap" aria-hidden="true"></span>
                @else
                    <x-avatar :user="$comment->author" class="ctx-avatar msg-avatar {{ $comment->is_supervisor ? 'is-supervisor' : '' }}" />
                @endif
            @endunless

            <div class="msg-body">
                @unless ($mine || $cont)
                    <div class="msg-author">
                        <b>{{ $comment->author->name ?? 'مستخدم محذوف' }}</b>
                        @if ($comment->is_supervisor)
                            <span class="msg-role">مشرف</span>
                        @endif
                    </div>
                @endunless

                <div class="msg-bubble">
                    <p class="cmt-text">{!! $renderBody($comment) !!}</p>
                    <time class="msg-time" datetime="{{ $comment->created_at->toIso8601String() }}"
                        title="{{ $comment->created_at->format('Y-m-d H:i') }}">
                        {{ $comment->created_at->format('H:i') }}
                    </time>
                </div>
            </div>

            {{-- الطالب يحذف ما كتبه وحده؛ المشرف يحذف أيّ تعليق في مشروعه --}}
            @if ($mine || $role === 'supervisor')
                <form action="{{ route($role . '.comments.destroy', ['comment' => $comment->id]) }}"
                    method="POST" class="msg-del" onsubmit="return confirm('حذف هذه الرسالة؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-action--danger" title="حذف"
                        aria-label="حذف الرسالة">
                        <i class="ti ti-trash" aria-hidden="true"></i>
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="chat-empty chat-start">
            <span class="chat-start-icon" aria-hidden="true"><i class="ti ti-messages"></i></span>
            <h3>{{ $isTeam ? 'نقاش الفريق فارغ بعد' : 'لا رسائل بعد' }}</h3>
            <p>
                {{ $isTeam
                    ? 'مساحة الفريق وحده — نسّقوا المهام، واكتب @ لتنبيه زميل بعينه.'
                    : ($role === 'student'
                        ? 'اسأل مشرفك هنا — كل ما يُكتب يبقى مرجعاً لك وله.'
                        : 'ابدأ النقاش مع الفريق — ملاحظة، سؤال، أو توجيه للمرحلة القادمة.') }}
            </p>
        </div>
    @endforelse
</div>

<form action="{{ route($role . '.comments.store', ['project' => $project->id]) }}" method="POST"
    class="cmt-form chat-compose" @if ($isTeam) data-mentions @endif>
    @csrf
    <input type="hidden" name="channel" value="{{ $channel }}">
    <div data-mention-inputs hidden></div>

    {{-- قائمة @: الزملاء، تُصفّى بما يُكتب بعد @ --}}
    @if ($isTeam && $mates->isNotEmpty())
        <div class="mention-menu" data-mention-menu role="listbox" hidden>
            <div class="mention-menu-head">تنبيه زميل</div>
            @foreach ($mates as $mate)
                <button type="button" class="mention-option" role="option" data-mate="{{ $mate->id }}" data-name="{{ $mate->name }}">
                    <x-avatar :user="$mate" class="ctx-avatar" />
                    <span>{{ $mate->name }}</span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="compose-box">
        <textarea name="body" rows="1" required maxlength="1000" id="chat-body"
            class="@error('body') is-invalid @enderror"
            placeholder="{{ $isTeam ? 'اكتب للفريق… و@ لتنبيه زميل' : ($role === 'student' ? 'اكتب سؤالك أو تحديثك للمشرف…' : 'اكتب ملاحظتك للفريق…') }}"
            aria-label="نصّ الرسالة">{{ old('body') }}</textarea>
        <button type="submit" class="compose-send" data-loading-text=" " aria-label="إرسال" title="إرسال (Ctrl + Enter)">
            <i class="ti ti-send" aria-hidden="true"></i>
        </button>
    </div>
    <span class="chat-hint">
        @if ($isTeam)
            <kbd>@</kbd> لتنبيه زميل ·
        @endif
        <kbd>Ctrl</kbd> + <kbd>Enter</kbd> للإرسال · <span id="chat-count">0</span>/1000
    </span>
    @error('body')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</form>

@push('js')
    <script>
        (function () {
            var stream = document.getElementById('chat-stream');
            if (!stream) return;

            // يبدأ عند أول جديد، وإلا في الأسفل حيث آخر ما قيل
            var mark = document.getElementById('chat-new');
            stream.scrollTop = mark ? mark.offsetTop - stream.offsetTop - 12 : stream.scrollHeight;

            var body = document.getElementById('chat-body');
            if (!body) return;

            var count = document.getElementById('chat-count');
            var send = body.form.querySelector('.compose-send');

            // الصندوق ينمو مع النصّ حتى حدّه، والزرّ يضيء حين يوجد ما يُرسل
            function fit() {
                body.style.height = 'auto';
                body.style.height = Math.min(body.scrollHeight, 160) + 'px';
                if (count) count.textContent = body.value.length;
                send.classList.toggle('is-ready', body.value.trim() !== '');
            }
            body.addEventListener('input', fit);
            fit();

            body.addEventListener('keydown', function (e) {
                if (menuOpen() && handleMenuKey(e)) return;
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey) && body.value.trim() !== '') {
                    e.preventDefault();
                    body.form.requestSubmit();
                }
            });

            // ===== @ذكر زميل (نقاش الفريق) =====
            var form = body.form;
            var menu = form.querySelector('[data-mention-menu]');
            var picked = {}; // id ← الاسم
            var active = 0;

            function menuOpen() { return !!menu && !menu.hidden; }
            function options() {
                return Array.prototype.filter.call(menu.querySelectorAll('[data-mate]'), function (o) { return !o.hidden; });
            }
            // النصّ من آخر @ حتى المؤشّر — والـ@ في أول الكلام أو بعد مسافة، لا وسط كلمة
            function query() {
                var upto = body.value.slice(0, body.selectionStart);
                var at = upto.lastIndexOf('@');
                if (at === -1) return null;
                var prev = at === 0 ? ' ' : upto.charAt(at - 1);
                if (prev.trim() !== '') return null;
                var q = upto.slice(at + 1);
                if (q.indexOf('\n') !== -1 || q.length > 24) return null;
                return { at: at, q: q };
            }
            function highlight() {
                options().forEach(function (o, i) { o.classList.toggle('is-active', i === active); });
            }
            function refreshMenu() {
                if (!menu) return;
                var m = query();
                if (!m) { menu.hidden = true; return; }
                var shown = 0;
                menu.querySelectorAll('[data-mate]').forEach(function (o) {
                    var ok = o.dataset.name.indexOf(m.q) !== -1;
                    o.hidden = !ok;
                    if (ok) shown++;
                });
                menu.hidden = shown === 0;
                active = 0;
                highlight();
            }
            function pick(o) {
                var m = query();
                if (!m) return;
                var before = body.value.slice(0, m.at), after = body.value.slice(body.selectionStart);
                var tag = '@' + o.dataset.name + ' ';
                body.value = before + tag + after;
                var pos = before.length + tag.length;
                body.setSelectionRange(pos, pos);
                picked[o.dataset.mate] = o.dataset.name;
                menu.hidden = true;
                fit();
                body.focus();
            }
            function handleMenuKey(e) {
                var list = options();
                if (!list.length) return false;
                if (e.key === 'ArrowDown') { active = (active + 1) % list.length; highlight(); e.preventDefault(); return true; }
                if (e.key === 'ArrowUp') { active = (active - 1 + list.length) % list.length; highlight(); e.preventDefault(); return true; }
                if ((e.key === 'Enter' && !e.ctrlKey && !e.metaKey) || e.key === 'Tab') { pick(list[active]); e.preventDefault(); return true; }
                if (e.key === 'Escape') { menu.hidden = true; e.preventDefault(); return true; }
                return false;
            }

            if (menu) {
                body.addEventListener('input', refreshMenu);
                body.addEventListener('click', refreshMenu);
                menu.addEventListener('mousedown', function (e) { e.preventDefault(); }); // يبقى التركيز في الصندوق
                menu.addEventListener('click', function (e) {
                    var o = e.target.closest('[data-mate]');
                    if (o) pick(o);
                });
                body.addEventListener('blur', function () { setTimeout(function () { menu.hidden = true; }, 120); });

                // عند الإرسال: المذكور من بقي اسمه في النصّ — حذفُ «@الاسم» يُسقط الذكر
                form.addEventListener('submit', function () {
                    var box = form.querySelector('[data-mention-inputs]'), html = '';
                    Object.keys(picked).forEach(function (id) {
                        if (body.value.indexOf('@' + picked[id]) !== -1) {
                            html += '<input type="hidden" name="mentions[]" value="' + parseInt(id, 10) + '">';
                        }
                    });
                    box.innerHTML = html;
                });
            }
        })();
    </script>
@endpush
