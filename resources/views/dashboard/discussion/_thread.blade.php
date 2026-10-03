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
    $channel = $channel ?? \App\Models\ProjectComment::SUPERVISOR;
    $isTeam = $channel === \App\Models\ProjectComment::TEAM;
    $comments = $comments ?? $project->comments;

    // الزملاء للذكر بـ@: الفريق عدا الكاتب
    $mates = $isTeam
        ? $project->group->filter(fn ($g) => $g->student && (int) $g->student_id !== (int) $me->id)->map(fn ($g) => $g->student)->values()
        : collect();
@endphp

{{-- النقاش الحيّ: السكربت يسأل نقطة السؤال عمّا بعد آخر رقم، ويُلحق الجديد --}}
<div class="chat-stream-wrap">
<div class="chat-stream" id="chat-stream"
    data-live-url="{{ route('discussion.live', ['project' => $project->id, 'channel' => $channel]) }}"
    data-last-id="{{ (int) ($comments->max('id') ?? 0) }}">
    @if ($comments->isNotEmpty())
        @include('dashboard.discussion._messages', ['withDivider' => true, 'lastRead' => $lastRead ?? null, 'prev' => null])
    @else
        <div class="chat-empty chat-start">
            <span class="chat-start-icon" aria-hidden="true"><i class="ti ti-messages"></i></span>
            <h3>{{ $isTeam ? __('نقاش الفريق فارغ بعد') : __('لا رسائل بعد') }}</h3>
            <p>
                {{ $isTeam
                    ? __('مساحة الفريق وحده — نسّقوا المهام، واكتب @ لتنبيه زميل بعينه.')
                    : ($role === 'student'
                        ? __('اسأل مشرفك هنا — كل ما يُكتب يبقى مرجعاً لك وله.')
                        : __('ابدأ النقاش مع الفريق — ملاحظة، سؤال، أو توجيه للمرحلة القادمة.')) }}
            </p>
        </div>
    @endif
</div>
{{-- وصل جديد والقارئ في رسائل قديمة: لا يُسحب من مكانه، بل يُدعى إليه --}}
<button type="button" class="chat-jump" id="chat-jump" hidden>
    <i class="ti ti-arrow-down" aria-hidden="true"></i> <span data-chat-jump-label>{{ __('رسائل جديدة') }}</span>
</button>
</div>

<form action="{{ route($role . '.comments.store', ['project' => $project->id]) }}" method="POST"
    class="cmt-form chat-compose" @if ($isTeam) data-mentions @endif>
    @csrf
    <input type="hidden" name="channel" value="{{ $channel }}">
    <div data-mention-inputs hidden></div>

    {{-- قائمة @: الزملاء، تُصفّى بما يُكتب بعد @ --}}
    @if ($isTeam && $mates->isNotEmpty())
        <div class="mention-menu" data-mention-menu role="listbox" hidden>
            <div class="mention-menu-head">{{ __('تنبيه زميل') }}</div>
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
            placeholder="{{ $isTeam ? __('اكتب للفريق… و@ لتنبيه زميل') : ($role === 'student' ? __('اكتب سؤالك أو تحديثك للمشرف…') : __('اكتب ملاحظتك للفريق…')) }}"
            aria-label="{{ __('نصّ الرسالة') }}">{{ old('body') }}</textarea>
        <button type="submit" class="compose-send" data-loading-text=" " aria-label="{{ __('إرسال') }}" title="{{ __('إرسال (Ctrl + Enter)') }}">
            <i class="ti ti-send" aria-hidden="true"></i>
        </button>
    </div>
    <span class="chat-hint">
        @if ($isTeam)
            <kbd>@</kbd> {{ __('لتنبيه زميل') }} ·
        @endif
        <kbd>Ctrl</kbd> + <kbd>Enter</kbd> {{ __('للإرسال') }} · <span id="chat-count">0</span>/1000
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
    <script>
        (function () {
            var stream = document.getElementById('chat-stream');
            if (!stream || !window.fetch) return;

            var L = {
                sending: @json(__('جارٍ الإرسال…')),
                failed: @json(__('لم تُرسل')),
                retry: @json(__('إعادة المحاولة')),
                newOne: @json(__('رسالة جديدة')),
                newMany: @json(__(':n رسائل جديدة')),
                delFailed: @json(__('تعذّر حذف الرسالة — حاول مرة أخرى.')),
            };
            var url = stream.dataset.liveUrl;
            var lastId = parseInt(stream.dataset.lastId || '0', 10);
            var form = document.querySelector('.chat-compose');
            var jump = document.getElementById('chat-jump');
            var INTERVAL = 5000, timer = null, busy = false, unseen = 0, failures = 0;
            var headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

            function nearBottom() { return stream.scrollHeight - stream.scrollTop - stream.clientHeight < 90; }
            function toBottom(smooth) { stream.scrollTo({ top: stream.scrollHeight, behavior: smooth ? 'smooth' : 'auto' }); }

            function showJump() {
                if (!jump) return;
                jump.hidden = unseen === 0;
                jump.querySelector('[data-chat-jump-label]').textContent = unseen === 1 ? L.newOne : L.newMany.replace(':n', unseen);
            }
            if (jump) jump.addEventListener('click', function () { unseen = 0; showJump(); toBottom(true); });
            stream.addEventListener('scroll', function () { if (unseen && nearBottom()) { unseen = 0; showJump(); } });

            // إلحاق ما رسمه الخادم — وما هو معروض أصلاً (رسالتي) لا يتكرّر
            function append(html, stick) {
                if (!html) return;
                var near = stick || nearBottom();
                var tpl = document.createElement('template');
                tpl.innerHTML = html;
                tpl.content.querySelectorAll('.msg[data-id]').forEach(function (m) {
                    if (stream.querySelector('.msg[data-id="' + m.dataset.id + '"]')) m.remove();
                });
                // فاصل يوم موجود أصلاً لا يتكرّر
                tpl.content.querySelectorAll('.chat-day[data-day]').forEach(function (d) {
                    if (stream.querySelector('.chat-day[data-day="' + d.dataset.day + '"]')) d.remove();
                });
                var added = tpl.content.querySelectorAll('.msg').length;
                if (!added) return;
                var empty = stream.querySelector('.chat-start');
                if (empty) empty.remove();
                tpl.content.querySelectorAll('.msg').forEach(function (m) { m.classList.add('is-arriving'); });
                stream.appendChild(tpl.content);
                if (near) { toBottom(true); } else { unseen += added; showJump(); }
            }

            function removeMsg(m) {
                m.classList.add('is-removing');
                setTimeout(function () { m.remove(); }, 220);
            }

            // ما حذفه الطرف الآخر يختفي هنا أيضاً
            function syncDeleted(ids) {
                if (!ids) return;
                var keep = {};
                ids.forEach(function (id) { keep[id] = true; });
                stream.querySelectorAll('.msg[data-id]').forEach(function (m) {
                    if (!keep[m.dataset.id]) removeMsg(m);
                });
            }

            // عدّادات القناة الأخرى (الطالب) وقائمة المشاريع (المشرف)
            function counters(unread) {
                if (!unread) return;
                document.querySelectorAll('[data-chat-unread]').forEach(function (el) {
                    var n = unread[el.dataset.chatUnread] || 0;
                    el.hidden = !n; el.textContent = n;
                });
                if (unread.projects !== undefined) {
                    document.querySelectorAll('[data-chat-unread-project]').forEach(function (el) {
                        var n = (unread.projects || {})[el.dataset.chatUnreadProject] || 0;
                        el.hidden = !n; el.textContent = n;
                        var item = el.closest('.chat-item');
                        if (item && !item.classList.contains('is-active')) item.classList.toggle('is-unread', !!n);
                    });
                }
            }

            function schedule(ms) { clearTimeout(timer); timer = setTimeout(poll, ms); }

            function poll() {
                if (busy) return schedule(INTERVAL);
                if (document.hidden) return schedule(INTERVAL);
                busy = true;
                fetch(url + '&after=' + lastId + '&read=1', { headers: headers, credentials: 'same-origin', cache: 'no-store' })
                    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                    .then(function (d) {
                        append(d.html);
                        lastId = Math.max(lastId, d.last_id || 0);
                        syncDeleted(d.ids);
                        counters(d.unread);
                        failures = 0;
                    })
                    .catch(function () { failures++; })
                    .finally(function () {
                        busy = false;
                        // تباطؤ عند الخطأ حتى دقيقة
                        schedule(Math.min(INTERVAL * Math.pow(2, failures), 60000));
                    });
            }
            document.addEventListener('visibilitychange', function () { if (!document.hidden) schedule(200); });
            schedule(INTERVAL);

            // ===== الإرسال بلا تحميل =====
            // يأتي بعد مستمع الذكر أعلاه، فحقول mentions[] جاهزة حين نقرأ النموذج
            if (form) {
                var body = form.querySelector('textarea[name="body"]');
                form.addEventListener('submit', function (e) {
                    if (e.defaultPrevented) return;
                    var text = body.value.trim();
                    if (!text) return;
                    e.preventDefault();

                    var data = new FormData(form);
                    data.append('after', lastId);

                    // تظهر فوراً شفافةً «جارٍ الإرسال…»، وتثبت حين يردّ الخادم
                    var pending = document.createElement('div');
                    pending.className = 'msg is-mine is-sending';
                    pending.innerHTML = '<div class="msg-body"><div class="msg-bubble"><p class="cmt-text"></p><span class="msg-time"></span></div></div>';
                    pending.querySelector('.cmt-text').textContent = text;
                    pending.querySelector('.msg-time').textContent = L.sending;
                    var empty = stream.querySelector('.chat-start');
                    if (empty) empty.remove();
                    stream.appendChild(pending);
                    toBottom(true);

                    body.value = '';
                    body.dispatchEvent(new Event('input'));
                    var box = form.querySelector('[data-mention-inputs]');
                    if (box) box.innerHTML = '';

                    fetch(form.action, { method: 'POST', body: data, headers: headers, credentials: 'same-origin' })
                        .then(function (r) {
                            if (r.status === 422) return r.json().then(function (j) { throw { message: (j.errors && Object.values(j.errors)[0][0]) || j.message }; });
                            if (!r.ok) throw { message: L.failed };
                            return r.json();
                        })
                        .then(function (d) {
                            pending.remove();
                            append(d.html, true);
                            lastId = Math.max(lastId, d.last_id || 0);
                        })
                        .catch(function (err) {
                            // النصّ لا يضيع: «لم تُرسل» وزرّ يعيده إلى الصندوق
                            pending.classList.remove('is-sending');
                            pending.classList.add('is-failed');
                            var t = pending.querySelector('.msg-time');
                            t.textContent = (err && err.message) || L.failed;
                            var again = document.createElement('button');
                            again.type = 'button';
                            again.className = 'msg-retry';
                            again.textContent = L.retry;
                            again.addEventListener('click', function () {
                                body.value = text;
                                body.dispatchEvent(new Event('input'));
                                pending.remove();
                                body.focus();
                            });
                            pending.querySelector('.msg-bubble').appendChild(again);
                        });
                });
            }

            // ===== الحذف بلا تحميل (بعد تأكيد النموذج نفسه) =====
            stream.addEventListener('submit', function (e) {
                var del = e.target.closest('.msg-del');
                if (!del || e.defaultPrevented) return;
                e.preventDefault();
                var msg = del.closest('.msg');
                msg.classList.add('is-sending');
                fetch(del.action, { method: 'POST', body: new FormData(del), headers: headers, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw new Error(r.status); removeMsg(msg); })
                    .catch(function () { msg.classList.remove('is-sending'); window.alert(L.delFailed); });
            });
        })();
    </script>
@endpush
