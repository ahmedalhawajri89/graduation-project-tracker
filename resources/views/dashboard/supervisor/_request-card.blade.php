{{--
    طلب إشراف ينتظر قراراً — في صفحة الطلبات وفي اللوحة.

    كان سطراً مطويّاً في أكورديون، والقبول والرفض لا يظهران إلا بعد فتحه.

    @param \App\Models\Project $project    بـ group.student و project_type
    @param int                 $seatsLeft  مقاعد الفصل الحالي
    @param int                 $pending    عدد الطلبات المعلّقة كلّها — لتنبيه المقعد الأخير
    @param bool                $compact    في اللوحة: بلا وصف
--}}

@php
    $compact = $compact ?? false;
    $canAccept = $seatsLeft > 0;
    $lastSeat = $seatsLeft === 1 && $pending > 1;
    $replyUrl = route('supervisor.replay.project', ['project_id' => $project->id]);
    $leader = $project->group->firstWhere('type', 'leader');
@endphp

<article class="req-card" id="request-{{ $project->id }}">
    <header class="req-head">
        <div class="req-title">
            <b>{{ $project->title }}</b>
            <span>
                {{ $project->project_type->name ?? '—' }}
                · قُدّم {{ $project->created_at->diffForHumans() }}
            </span>
        </div>
        <span class="req-waiting">
            <i class="ti ti-clock-hour-4" aria-hidden="true"></i>
            بانتظار ردّك
        </span>
    </header>

    @if (! $compact && $project->description)
        <p class="req-desc">{{ $project->description }}</p>
    @endif

    <ul class="req-team" aria-label="الفريق">
        @foreach ($project->group as $member)
            <li class="{{ $member->type === 'leader' ? 'is-leader' : '' }}">
                <x-avatar :user="$member->student" class="ctx-avatar" />
                <span>
                    {{ $member->student?->name ?? 'طالب محذوف' }}
                    @if ($member->type === 'leader')
                        <span class="ctx-tag">قائد</span>
                    @endif
                    <small dir="ltr">{{ $member->student?->university_id }}</small>
                </span>
            </li>
        @endforeach
    </ul>

    <footer class="req-actions">
        @if ($canAccept)
            <form action="{{ $replyUrl }}" method="POST">
                @csrf
                <button name="btnAccept" value="accept" class="btn btn-primary"
                    onclick="return confirm({{ Js::from($lastSeat
                        ? 'هذا آخر مقعد لك هذا الفصل — قبوله يرفض الطلبات الباقية تلقائياً. متابعة؟'
                        : 'قبول «' . $project->title . '»؟ سيُبلَّغ الفريق.') }})">
                    <i class="ti ti-check me-1" aria-hidden="true"></i>
                    قبول الإشراف
                </button>
            </form>
        @else
            <span class="req-full">
                <i class="ti ti-lock" aria-hidden="true"></i>
                اكتمل حدّك — القبول يحتاج رفع الحدّ من الإدارة
            </span>
        @endif

        {{-- الرفض بسببه: هو ما يحتاجه الطلاب ليعدّلوا فكرتهم --}}
        <details class="req-reject">
            <summary class="btn btn-outline-secondary">
                <i class="ti ti-x me-1" aria-hidden="true"></i>
                رفض…
            </summary>
            <form action="{{ $replyUrl }}" method="POST" class="req-reject-form">
                @csrf
                <label class="form-label" for="reason-{{ $project->id }}">
                    سبب الرفض <small>(اختياري — يصل إلى {{ $leader?->student?->name ?? 'الفريق' }} وفريقه)</small>
                </label>
                <textarea id="reason-{{ $project->id }}" name="reason" rows="2" maxlength="500" class="form-control"
                    placeholder="مثال: الفكرة منفّذة سابقاً، أو نطاقها أوسع من فصل واحد.."></textarea>
                <button name="btnReject" value="reject" class="btn btn-outline-danger"
                    onclick="return confirm('رفض هذا الطلب؟ سيصل الفريقَ إشعار بالرفض والسبب إن كتبته.')">
                    تأكيد الرفض
                </button>
            </form>
        </details>
    </footer>
</article>
