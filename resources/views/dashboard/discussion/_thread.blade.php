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
--}}

@php
    $me = auth($role)->user();
    $meType = $role === 'supervisor' ? \App\Models\Supervisor::class : \App\Models\Student::class;
    $isMine = fn ($c) => $c->author_type === $meType && (int) $c->author_id === (int) $me->id;

    // أول رسالة من غيري لم أقرأها — عندها الفاصل
    $firstNew = $project->comments->first(
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
    @forelse ($project->comments as $comment)
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

        <div class="msg {{ $mine ? 'is-mine' : '' }} {{ $cont ? 'is-cont' : '' }} {{ $comment->is_supervisor ? 'is-supervisor' : '' }}">
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
                    <p class="cmt-text">{{ $comment->body }}</p>
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
            <h3>لا رسائل بعد</h3>
            <p>
                {{ $role === 'student'
                    ? 'اسأل مشرفك هنا — كل ما يُكتب يبقى مرجعاً لك وله.'
                    : 'ابدأ النقاش مع الفريق — ملاحظة، سؤال، أو توجيه للمرحلة القادمة.' }}
            </p>
        </div>
    @endforelse
</div>

<form action="{{ route($role . '.comments.store', ['project' => $project->id]) }}" method="POST"
    class="cmt-form chat-compose">
    @csrf
    <div class="compose-box">
        <textarea name="body" rows="1" required maxlength="1000" id="chat-body"
            class="@error('body') is-invalid @enderror"
            placeholder="{{ $role === 'student' ? 'اكتب سؤالك أو تحديثك للمشرف…' : 'اكتب ملاحظتك للفريق…' }}"
            aria-label="نصّ الرسالة">{{ old('body') }}</textarea>
        <button type="submit" class="compose-send" data-loading-text=" " aria-label="إرسال" title="إرسال (Ctrl + Enter)">
            <i class="ti ti-send" aria-hidden="true"></i>
        </button>
    </div>
    <span class="chat-hint"><kbd>Ctrl</kbd> + <kbd>Enter</kbd> للإرسال · <span id="chat-count">0</span>/1000</span>
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
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey) && body.value.trim() !== '') {
                    e.preventDefault();
                    body.form.requestSubmit();
                }
            });
        })();
    </script>
@endpush
