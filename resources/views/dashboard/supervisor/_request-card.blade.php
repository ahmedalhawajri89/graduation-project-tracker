{{--
    طلب إشراف ينتظر قراراً — في صفحة الطلبات وفي اللوحة.

    كان سطراً مطويّاً في أكورديون، والقبول والرفض لا يظهران إلا بعد فتحه.

    @param \App\Models\Project $project    بـ group.student و project_type
    @param int                 $seatsLeft  مقاعد الفصل الحالي
    @param int                 $pending    عدد الطلبات المعلّقة كلّها — لتنبيه المقعد الأخير
    @param bool                $compact    في اللوحة: بلا وصف
    @param \Illuminate\Support\Collection|null $similar  مشاريع مكتملة تشبهه (صفحة الطلبات وحدها)
--}}

@php
    $compact = $compact ?? false;
    $similar = $similar ?? collect();
    // الانتظار: بعد ثلاثة أيام يصير تنبيهاً — الفريق لا يبدأ قبل الردّ
    $waitDays = (int) $project->created_at->diffInDays(now());
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
                · {{ __('قُدّم :when', ['when' => $project->created_at->diffForHumans()]) }}
            </span>
        </div>
        <span class="req-waiting {{ $waitDays >= 3 ? 'is-long' : '' }}">
            <i class="ti ti-clock-hour-4" aria-hidden="true"></i>
            {{ $waitDays === 0 ? __('وصل اليوم') : ($waitDays === 1 ? __('ينتظر منذ يوم') : __('ينتظر منذ :n أيام', ['n' => $waitDays])) }}
        </span>
    </header>

    @if (! $compact && $project->description)
        <p class="req-desc">{{ $project->description }}</p>
    @endif

    {{-- تنبيه لا منع: مطابقة كلمات، والقرار للمشرف --}}
    @if ($similar->isNotEmpty())
        <div class="req-similar">
            <i class="ti ti-copy" aria-hidden="true"></i>
            <span>
                <b>{{ $similar->count() === 1 ? __('يشبه مشروعاً مكتملاً:') : __('يشبه مشاريع مكتملة:') }}</b>
                @foreach ($similar as $s)
                    {{ __('«:title»', ['title' => $s->title]) }}<small> — {{ $s->semester?->label }}@if ($s->supervisor) · {{ $s->supervisor->name }}@endif</small>@if (! $loop->last){{ __('، ') }}@endif
                @endforeach
            </span>
        </div>
    @endif

    <ul class="req-team" aria-label="{{ __('الفريق') }}">
        @foreach ($project->group as $member)
            <li class="{{ $member->type === 'leader' ? 'is-leader' : '' }}">
                <x-avatar :user="$member->student" class="ctx-avatar" />
                <span>
                    {{ $member->student?->name ?? __('طالب محذوف') }}
                    @if ($member->type === 'leader')
                        <span class="ctx-tag">{{ __('قائد') }}</span>
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
                        ? __('هذا آخر مقعد لك هذا الفصل — قبوله يرفض الطلبات الباقية تلقائياً. متابعة؟')
                        : __('قبول «:title»؟ سيُبلَّغ الفريق.', ['title' => $project->title])) }})">
                    <i class="ti ti-check me-1" aria-hidden="true"></i>
                    {{ __('قبول الإشراف') }}
                </button>
            </form>
        @else
            <span class="req-full">
                <i class="ti ti-lock" aria-hidden="true"></i>
                {{ __('اكتمل حدّك — القبول يحتاج رفع الحدّ من الإدارة') }}
            </span>
        @endif

        {{-- الرفض بسببه: هو ما يحتاجه الطلاب ليعدّلوا فكرتهم --}}
        <details class="req-reject">
            <summary class="btn btn-outline-secondary">
                <i class="ti ti-x me-1" aria-hidden="true"></i>
                {{ __('رفض…') }}
            </summary>
            <form action="{{ $replyUrl }}" method="POST" class="req-reject-form">
                @csrf
                <label class="form-label" for="reason-{{ $project->id }}">
                    {{ __('سبب الرفض') }} <small>{{ $leader?->student?->name ? __('(اختياري — يصل إلى :name وفريقه)', ['name' => $leader->student->name]) : __('(اختياري — يصل إلى الفريق وفريقه)') }}</small>
                </label>
                <textarea id="reason-{{ $project->id }}" name="reason" rows="2" maxlength="500" class="form-control"
                    placeholder="{{ __('مثال: الفكرة منفّذة سابقاً، أو نطاقها أوسع من فصل واحد..') }}"></textarea>
                <button name="btnReject" value="reject" class="btn btn-outline-danger"
                    onclick="return confirm({{ Js::from(__('رفض هذا الطلب؟ سيصل الفريقَ إشعار بالرفض والسبب إن كتبته.')) }})">
                    {{ __('تأكيد الرفض') }}
                </button>
            </form>
        </details>
    </footer>
</article>
