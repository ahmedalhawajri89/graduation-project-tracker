{{--
    الفريق — المشرف والأعضاء في بطاقة واحدة، للطالب وللمشرف.

    كانت بطاقتين: المشرف ببريده وهاتفه في جدول بعناوين، والأعضاء بصفوف
    ٦٣ بكسل تحمل أرقاماً جامعية لا يحتاجها الطالب عن زميله. والتواصل
    صار أزراراً صغيرة كما في صفحة المشرف.

    @param \App\Models\Project $project         بـ group.student و supervisor
    @param string              $role            student | supervisor — من يرى البطاقة
--}}

@php
    $meId = (int) auth($role)->id();
    // القائد أولاً ثم الباقون بترتيبهم
    $members = $project->group->sortBy(fn ($m) => $m->type === 'leader' ? 0 : 1)->values();
    $contact = function ($person) {
        return array_filter([
            $person?->email ? ['href' => 'mailto:' . $person->email, 'icon' => 'ti-mail', 'title' => $person->email] : null,
            $person?->phone ? ['href' => 'tel:' . $person->phone, 'icon' => 'ti-phone', 'title' => $person->phone] : null,
        ]);
    };
@endphp

<div class="ctx-card is-team" id="team">
    <div class="ctx-head">
        <i class="ti ti-users-group" aria-hidden="true"></i>
        الفريق
        <span class="ctx-count">{{ $members->count() + ($role === 'student' ? 1 : 0) }}</span>
    </div>

    {{-- المشرف أولاً عند الطالب — هو من يُسأل. والمشرف لا يرى نفسه --}}
    @if ($role === 'student' && $project->supervisor)
        <div class="ctx-person">
            <x-avatar :user="$project->supervisor" class="ctx-avatar is-supervisor" />
            <span class="ctx-person-body">
                <span class="ctx-person-name">
                    {{ $project->supervisor->name }}
                    <span class="ctx-tag is-neutral">مشرف</span>
                </span>
                <span class="ctx-person-meta">{{ $project->supervisor->specialize->name ?? 'مشرف المشروع' }}</span>
            </span>
            <span class="ctx-person-actions">
                <a href="{{ route('student.discussion') }}" class="btn-action" title="النقاش مع المشرف"
                    aria-label="النقاش مع المشرف">
                    <i class="ti ti-messages" aria-hidden="true"></i>
                </a>
                @foreach ($contact($project->supervisor) as $c)
                    <a href="{{ $c['href'] }}" class="btn-action" title="{{ $c['title'] }}" aria-label="{{ $c['title'] }}">
                        <i class="ti {{ $c['icon'] }}" aria-hidden="true"></i>
                    </a>
                @endforeach
            </span>
        </div>
    @endif

    @foreach ($members as $member)
        @php $isMe = $role === 'student' && (int) $member->student_id === $meId; @endphp
        {{-- الرقم الجامعي في التلميح لا في الصفّ: يحتاجه المشرف أحياناً والطالب نادراً --}}
        <div class="ctx-person" title="{{ $member->student?->university_id }}">
            <x-avatar :user="$member->student" class="ctx-avatar" />
            <span class="ctx-person-body">
                <span class="ctx-person-name">
                    {{ $member->student?->name ?? 'طالب محذوف' }}
                    @if ($member->type === 'leader')
                        <span class="ctx-tag">قائد</span>
                    @endif
                    @if ($isMe)
                        <span class="cell-you">أنت</span>
                    @endif
                </span>
                @if ($role === 'supervisor')
                    <span class="ctx-person-meta" dir="ltr">{{ $member->student?->university_id }}</span>
                @endif
            </span>
            @unless ($isMe)
                <span class="ctx-person-actions">
                    @foreach ($contact($member->student) as $c)
                        <a href="{{ $c['href'] }}" class="btn-action" title="{{ $c['title'] }}" aria-label="{{ $c['title'] }}">
                            <i class="ti {{ $c['icon'] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </span>
            @endunless
        </div>
    @endforeach
</div>
