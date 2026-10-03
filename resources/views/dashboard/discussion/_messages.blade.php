{{--
    فقاعات الرسائل — تُرسم مع الصفحة (_thread) ويعيد DiscussionLiveController
    رسمها لما يصل بعدها، فلا نسختان من شكل الرسالة.

    @param \Illuminate\Support\Collection $comments  بالترتيب، بكتّابها
    @param \App\Models\Project            $project   بفريقه (أسماء الذكر)
    @param string                         $role      student | supervisor
    @param string                         $channel   supervisor | team
    @param bool                           $withDivider فاصل «رسائل جديدة» عند الفتح (لا عند الإلحاق)
    @param int|null                       $lastRead  آخر ما قرأه قبل الفتح — عنده الفاصل
    @param \App\Models\ProjectComment|null $prev     آخر رسالة معروضة قبل هذه — ليصحّ التجميع وفاصل اليوم عند الإلحاق
--}}
@php
    $me = auth($role)->user();
    $meType = $role === 'supervisor' ? \App\Models\Supervisor::class : \App\Models\Student::class;
    $isMine = fn ($c) => $c->author_type === $meType && (int) $c->author_id === (int) $me->id;
    $isTeam = ($channel ?? \App\Models\ProjectComment::SUPERVISOR) === \App\Models\ProjectComment::TEAM;
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

    // أول رسالة من غيري لم أقرأها — عندها الفاصل (عند الفتح وحده)
    $lastRead = $lastRead ?? null;
    $firstNew = ($withDivider ?? false)
        ? $comments->first(fn ($c) => ! $isMine($c) && (is_null($lastRead) || $c->id > $lastRead))
        : null;

    $dayLabel = function ($at) {
        if ($at->isToday()) return __('اليوم');
        if ($at->isYesterday()) return __('أمس');

        return $at->translatedFormat($at->isCurrentYear() ? 'l j F' : 'j F Y');
    };

    $prev = $prev ?? null;
@endphp
@foreach ($comments as $comment)
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
        <div class="chat-day" data-day="{{ $comment->created_at->format('Y-m-d') }}"><span>{{ $dayLabel($comment->created_at) }}</span></div>
    @endif

    @if ($isNew)
        <div class="chat-new" id="chat-new"><span>{{ __('رسائل جديدة') }}</span></div>
    @endif

    {{-- رسالة تذكرني: تُميَّز بحدّ ولون الهوية --}}
    @php $mentionsMe = $isTeam && $role === 'student' && ! $mine && $comment->mentions((int) $me->id); @endphp
    <div class="msg {{ $mine ? 'is-mine' : '' }} {{ $cont ? 'is-cont' : '' }} {{ $comment->is_supervisor ? 'is-supervisor' : '' }} {{ $mentionsMe ? 'is-mentioned' : '' }}"
        data-id="{{ $comment->id }}">
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
                    <b>{{ $comment->author->name ?? __('مستخدم محذوف') }}</b>
                    @if ($comment->is_supervisor)
                        <span class="msg-role">{{ __('مشرف') }}</span>
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
                method="POST" class="msg-del" data-confirm="{{ __('حذف هذه الرسالة؟') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-action--danger" title="{{ __('حذف') }}"
                    aria-label="{{ __('حذف الرسالة') }}">
                    <i class="ti ti-trash" aria-hidden="true"></i>
                </button>
            </form>
        @endif
    </div>
@endforeach
