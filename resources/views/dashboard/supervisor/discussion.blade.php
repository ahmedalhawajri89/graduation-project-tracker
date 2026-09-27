@extends('layouts.admin.admin')
@section('title', 'النقاش')

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">لوحتي</x-crumb>
    @if ($project)
        <x-crumb :href="route('supervisor.discussion')">النقاش</x-crumb>
        <x-crumb>{{ $project->title }}</x-crumb>
    @else
        <x-crumb>النقاش</x-crumb>
    @endif
@endsection

@section('content')

    <x-page-header title="النقاش" subtitle="رسائل مجموعاتك — ما لم يُقرأ أولاً" />

    @if ($projects->isEmpty() && ! $project)
        <div class="dist-panel">
            <x-empty-state icon="ti-messages-off" title="لا مجموعات هذا الفصل"
                text="حين يطلب فريق إشرافك أو تقبل طلباً، يظهر نقاشه هنا." class="py-6" />
        </div>
    @else
        {{-- على الجوال: القائمة وحدها، أو المحادثة وحدها إن اختيرت --}}
        <section class="chat-shell {{ $project ? 'has-open' : '' }}">

            <nav class="chat-list" aria-label="مجموعاتي">
                @foreach ($projects as $p)
                    @php
                        $last = $p->comments->first();
                        $n = $unread[$p->id] ?? 0;
                    @endphp
                    <a href="{{ route('supervisor.discussion', $p->id) }}"
                        class="chat-item {{ $project && $project->id === $p->id ? 'is-active' : '' }} {{ $n ? 'is-unread' : '' }}"
                        @if ($project && $project->id === $p->id) aria-current="page" @endif>
                        <span class="chat-item-top">
                            <b>{{ $p->title }}</b>
                            @if ($last)
                                <time>{{ $last->created_at->diffForHumans(null, true) }}</time>
                            @endif
                        </span>
                        <span class="chat-item-bottom">
                            <span class="chat-item-snippet">
                                @if ($last)
                                    {{ $last->is_supervisor ? 'أنت: ' : ($last->author->name ?? 'طالب') . ': ' }}{{ \Illuminate\Support\Str::limit($last->body, 60) }}
                                @elseif ($p->status === 'request')
                                    طلب إشراف معلّق — لا رسائل
                                @else
                                    لا رسائل بعد
                                @endif
                            </span>
                            @if ($n)
                                <span class="sidebar-count">{{ $n }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="chat-pane">
                @if ($project)
                    <header class="chat-head">
                        <a href="{{ route('supervisor.discussion') }}" class="chat-back btn-action"
                            aria-label="العودة إلى المجموعات">
                            <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        </a>
                        <span class="chat-head-body">
                            <b>{{ $project->title }}</b>
                            <span>{{ $project->group->map(fn ($g) => $g->student?->name)->filter()->implode('، ') }}</span>
                        </span>
                        @if (in_array($project->status, ['accept', 'complete']))
                            <a href="{{ route('supervisor.projects.show', $project->id) }}"
                                class="btn btn-outline-secondary ms-auto">
                                <i class="ti ti-settings me-1" aria-hidden="true"></i>
                                إدارة المشروع
                            </a>
                        @endif
                    </header>

                    @include('dashboard.discussion._thread', [
                        'project' => $project,
                        'lastRead' => $lastRead,
                        'role' => 'supervisor',
                    ])
                @else
                    <x-empty-state icon="ti-message-circle" title="اختر مجموعة"
                        text="{{ count($unread) ? 'المجموعات برسائل غير مقروءة في أعلى القائمة.' : 'لا رسائل غير مقروءة — كل شيء مقروء.' }}"
                        class="chat-empty" />
                @endif
            </div>
        </section>
    @endif

@endsection
