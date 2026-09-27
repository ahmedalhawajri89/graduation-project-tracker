@extends('layouts.admin.admin')
@section('title', 'النقاش')

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">لوحتي</x-crumb>
    <x-crumb>النقاش</x-crumb>
@endsection

{{--
    النقاش — قناتان: «مع المشرف»، و«الفريق» لأعضائه وحدهم.

    كان خيطاً واحداً يقرؤه المشرف كلّه، فانتقل الكلام الداخلي إلى واتساب.
    نقاش الفريق لا يراه المشرف ولا الإدارة، وفيه @ لتنبيه زميل بعينه.
--}}

@section('content')

    @php
        $isTeam = $channel === \App\Models\ProjectComment::TEAM;
        $members = $project?->group->sortBy(fn ($g) => $g->type === 'leader' ? 0 : 1)->values() ?? collect();
    @endphp

    <x-page-header title="النقاش"
        subtitle="{{ $project ? $project->title : 'اسأل مشرفك، ونسّق مع فريقك' }}" />

    @if (! $project)
        <div class="dist-panel">
            <x-empty-state icon="ti-messages-off" title="لا نقاش قبل المشروع"
                text="يُفتح النقاش مع المشرف ومع فريقك حين تقدّم مقترح مشروعك وتختار مشرفه." class="py-6">
                <x-slot:action>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                        <i class="ti ti-rocket me-1" aria-hidden="true"></i>
                        تقديم المقترح
                    </a>
                </x-slot:action>
            </x-empty-state>
        </div>
    @else
        {{-- ===== التبويبان ===== --}}
        <nav class="chat-tabs" aria-label="قنوات النقاش">
            <a href="{{ route('student.discussion') }}" class="chat-tab {{ $isTeam ? '' : 'is-active' }}"
                @unless ($isTeam) aria-current="page" @endunless>
                <x-avatar :user="$project->supervisor" class="ctx-avatar chat-tab-avatar is-supervisor" />
                <span class="chat-tab-text">
                    <b>مع المشرف</b>
                    <small>{{ $project->supervisor->name ?? 'بلا مشرف' }}</small>
                </span>
                @if ($unread['supervisor'])
                    <span class="sidebar-count">{{ $unread['supervisor'] }}</span>
                @endif
            </a>
            <a href="{{ route('student.discussion', ['tab' => 'team']) }}" class="chat-tab {{ $isTeam ? 'is-active' : '' }}"
                @if ($isTeam) aria-current="page" @endif>
                <span class="avatar-stack chat-tab-stack" aria-hidden="true">
                    @foreach ($members->take(3) as $g)
                        <x-avatar :user="$g->student" />
                    @endforeach
                </span>
                <span class="chat-tab-text">
                    <b>الفريق <i class="ti ti-lock chat-tab-lock" aria-hidden="true"></i></b>
                    <small>{{ $members->count() }} أعضاء · خاص</small>
                </span>
                @if ($unread['team'])
                    <span class="sidebar-count">{{ $unread['team'] }}</span>
                @endif
            </a>
        </nav>

        <section class="chat-shell is-single {{ $isTeam ? 'is-team' : '' }}">
            <div class="chat-pane">
                @if ($isTeam)
                    <header class="chat-head">
                        <span class="chat-team-icon" aria-hidden="true"><i class="ti ti-users-group"></i></span>
                        <span class="chat-head-body">
                            <b>نقاش الفريق</b>
                            <span>{{ $members->map(fn ($g) => $g->student?->name)->filter()->implode('، ') }}</span>
                        </span>
                    </header>
                    {{-- الخصوصية تُقال لا تُفترض: الطالب يكتب بحرّية حين يعرف من يقرأ --}}
                    <div class="chat-private">
                        <i class="ti ti-lock" aria-hidden="true"></i>
                        خاص بالفريق — لا يراه المشرف ولا الإدارة
                    </div>
                @else
                    <header class="chat-head">
                        <x-avatar :user="$project->supervisor" class="ctx-avatar is-supervisor" />
                        <span class="chat-head-body">
                            <b>{{ $project->supervisor->name ?? 'بلا مشرف' }}</b>
                            <span>{{ $project->supervisor->specialize->name ?? 'مشرف المشروع' }}</span>
                        </span>
                        @if ($project->status === 'request')
                            {{-- السؤال قبل القبول مشروع، لكن ليعرف أن الطلب لم يُبتّ بعد --}}
                            <x-status-badge :status="$project->status" class="ms-auto" />
                        @endif
                    </header>
                @endif

                @include('dashboard.discussion._thread', [
                    'project' => $project,
                    'comments' => $comments,
                    'channel' => $channel,
                    'lastRead' => $lastRead,
                    'role' => 'student',
                ])
            </div>
        </section>
    @endif

@endsection
