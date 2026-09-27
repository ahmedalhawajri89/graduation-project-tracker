{{--
    محادثة مشروع واحد — مشتركة بين الطالب والمشرف.

    المجرى يتمرّر داخل صندوقه والنموذج مثبّت أسفله، فالرسائل مهما كثرت
    لا تدفع الصفحة. فاصل «رسائل جديدة» يقع عند ما كان غير مقروء لحظة
    الفتح، والتمرير يبدأ عنده.

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
@endphp

<div class="chat-stream" id="chat-stream">
    @forelse ($project->comments as $comment)
        @if ($firstNew && $comment->id === $firstNew->id)
            <div class="chat-new" id="chat-new"><span>رسائل جديدة</span></div>
        @endif

        @php $mine = $isMine($comment); @endphp
        <div class="cmt-row {{ $mine ? 'is-mine' : '' }}">
            <x-avatar :user="$comment->author"
                class="ctx-avatar {{ $comment->is_supervisor ? 'is-supervisor' : '' }}" />
            <div class="cmt-body">
                <div class="cmt-head">
                    <b>{{ $comment->author->name ?? 'مستخدم محذوف' }}</b>
                    <span class="cmt-role">{{ $mine ? 'أنت' : ($comment->is_supervisor ? 'مشرف' : 'طالب') }}</span>
                    <time class="cmt-time" datetime="{{ $comment->created_at->toIso8601String() }}"
                        title="{{ $comment->created_at->format('Y-m-d H:i') }}">
                        {{ $comment->created_at->diffForHumans() }}
                    </time>

                    {{-- الطالب يحذف ما كتبه وحده؛ المشرف يحذف أيّ تعليق في مشروعه --}}
                    @if ($mine || $role === 'supervisor')
                        <form action="{{ route($role . '.comments.destroy', ['comment' => $comment->id]) }}"
                            method="POST" class="cmt-del" onsubmit="return confirm('حذف هذا التعليق؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action--danger" title="حذف"
                                aria-label="حذف التعليق">
                                <i class="ti ti-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif
                </div>
                <p class="cmt-text">{{ $comment->body }}</p>
            </div>
        </div>
    @empty
        <x-empty-state icon="ti-messages" title="لا رسائل بعد"
            :text="$role === 'student'
                ? 'اسأل مشرفك هنا — كل ما يُكتب يبقى مرجعاً لك وله.'
                : 'ابدأ النقاش مع الفريق — ملاحظة، سؤال، أو توجيه للمرحلة القادمة.'"
            class="chat-empty" />
    @endforelse
</div>

<form action="{{ route($role . '.comments.store', ['project' => $project->id]) }}" method="POST"
    class="cmt-form chat-compose">
    @csrf
    <textarea name="body" rows="2" required maxlength="1000" id="chat-body"
        class="form-control @error('body') is-invalid @enderror"
        placeholder="{{ $role === 'student' ? 'اكتب سؤالك أو تحديثك للمشرف..' : 'اكتب ملاحظتك للفريق..' }}"
        aria-label="نصّ الرسالة">{{ old('body') }}</textarea>
    <button type="submit" class="btn btn-primary" data-loading-text="..">
        <i class="ti ti-send" aria-hidden="true"></i>
        <span>إرسال</span>
    </button>
    <span class="chat-hint">Ctrl + Enter للإرسال</span>
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
            if (body) {
                body.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey) && body.value.trim() !== '') {
                        e.preventDefault();
                        body.form.requestSubmit();
                    }
                });
            }
        })();
    </script>
@endpush
