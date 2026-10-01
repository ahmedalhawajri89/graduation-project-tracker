@extends('layouts.admin.admin')
@section('title', __('النقاش'))

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">{{ __('لوحتي') }}</x-crumb>
    @if ($project)
        <x-crumb :href="route('supervisor.discussion')">{{ __('النقاش') }}</x-crumb>
        <x-crumb>{{ $project->title }}</x-crumb>
    @else
        <x-crumb>{{ __('النقاش') }}</x-crumb>
    @endif
@endsection

@section('content')

    @php
        $unreadTotal = array_sum($unread);
        $unreadGroups = count($unread);
    @endphp

    <x-page-header title="{{ __('النقاش') }}" subtitle="{{ __('رسائل مجموعاتك — ما لم يُقرأ أولاً') }}" />

    @if ($projects->isEmpty() && ! $project)
        <div class="dist-panel">
            <x-empty-state icon="ti-messages-off" title="{{ __('لا مجموعات هذا الفصل') }}"
                text="{{ __('حين يطلب فريق إشرافك أو تقبل طلباً، يظهر نقاشه هنا.') }}" class="py-6" />
        </div>
    @else
        {{-- على الجوال: القائمة وحدها، أو المحادثة وحدها إن اختيرت --}}
        <section class="chat-shell {{ $project ? 'has-open' : '' }}">

            <div class="chat-side">
                <div class="chat-side-head">
                    <div class="chat-side-title">
                        <b>{{ __('المحادثات') }}</b>
                        <span>{{ $projects->count() }}</span>
                        @if ($unreadTotal)
                            <span class="chat-side-unread">{{ __(':n غير مقروءة', ['n' => $unreadTotal]) }}</span>
                        @endif
                    </div>
                    {{-- البحث حين يستحقّ: أربع محادثات تُمسح بالعين --}}
                    @if ($projects->count() > 4)
                        <div class="chat-search">
                            <i class="ti ti-search" aria-hidden="true"></i>
                            <input type="search" id="chat-search" placeholder="{{ __('ابحث بمشروع أو طالب…') }}" aria-label="{{ __('بحث في المحادثات') }}">
                        </div>
                    @endif
                </div>

                <nav class="chat-list" aria-label="{{ __('مجموعاتي') }}">
                    @foreach ($projects as $p)
                        @php
                            $last = $p->comments->first();
                            $n = $unread[$p->id] ?? 0;
                            $names = $p->group->map(fn ($g) => $g->student?->name)->filter();
                        @endphp
                        <a href="{{ route('supervisor.discussion', $p->id) }}"
                            class="chat-item {{ $project && $project->id === $p->id ? 'is-active' : '' }} {{ $n ? 'is-unread' : '' }}"
                            data-search="{{ mb_strtolower($p->title . ' ' . $names->implode(' ')) }}"
                            @if ($project && $project->id === $p->id) aria-current="page" @endif>
                            {{-- حرف المشروع بلون ثابت له: تُعرف المحادثة قبل قراءة عنوانها --}}
                            <span class="chat-mono" style="--h: {{ ($p->id * 47) % 360 }}" aria-hidden="true">
                                {{ mb_substr(trim($p->title), 0, 1) }}
                            </span>
                            <span class="chat-item-main">
                                <span class="chat-item-top">
                                    <b>{{ $p->title }}</b>
                                    @if ($last)
                                        <time datetime="{{ $last->created_at->toIso8601String() }}">
                                            {{ $last->created_at->isToday() ? $last->created_at->format('H:i') : $last->created_at->diffForHumans(null, true) }}
                                        </time>
                                    @endif
                                </span>
                                <span class="chat-item-bottom">
                                    <span class="chat-item-snippet">
                                        @if ($last)
                                            <em>{{ $last->is_supervisor ? __('أنت') : \Illuminate\Support\Str::of($last->author->name ?? __('طالب'))->explode(' ')->first() }}:</em>
                                            {{ \Illuminate\Support\Str::limit($last->body, 60) }}
                                        @elseif ($p->status === 'request')
                                            <span class="chat-item-flag">{{ __('طلب إشراف معلّق') }}</span>
                                        @else
                                            {{ __('لا رسائل بعد — ابدأ النقاش') }}
                                        @endif
                                    </span>
                                    @if ($n)
                                        <span class="sidebar-count">{{ $n }}</span>
                                    @endif
                                </span>
                            </span>
                        </a>
                    @endforeach
                    <p class="chat-list-none d-none" id="chat-none">{{ __('لا محادثة مطابقة.') }}</p>
                </nav>
            </div>

            <div class="chat-pane">
                @if ($project)
                    @php $members = $project->group->sortByDesc(fn ($g) => $g->type === 'leader')->values(); @endphp
                    <header class="chat-head">
                        <a href="{{ route('supervisor.discussion') }}" class="chat-back btn-action"
                            aria-label="{{ __('العودة إلى المجموعات') }}">
                            <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        </a>
                        <span class="chat-mono is-lg" style="--h: {{ ($project->id * 47) % 360 }}" aria-hidden="true">
                            {{ mb_substr(trim($project->title), 0, 1) }}
                        </span>
                        <span class="chat-head-body">
                            <b>{{ $project->title }}</b>
                            <span>{{ $members->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')) }}</span>
                        </span>
                        <span class="avatar-stack chat-head-team" aria-hidden="true">
                            @foreach ($members->take(4) as $member)
                                <x-avatar :user="$member->student" />
                            @endforeach
                        </span>
                        @if (in_array($project->status, ['accept', 'complete']))
                            <a href="{{ route('supervisor.projects.show', $project->id) }}"
                                class="btn btn-outline-secondary chat-head-btn">
                                <i class="ti ti-layout-dashboard me-1" aria-hidden="true"></i>
                                <span>{{ __('المشروع') }}</span>
                            </a>
                        @else
                            <x-status-badge :status="$project->status" />
                        @endif
                    </header>

                    @include('dashboard.discussion._thread', [
                        'project' => $project,
                        'lastRead' => $lastRead,
                        'role' => 'supervisor',
                    ])
                @else
                    <div class="chat-welcome">
                        <span class="chat-welcome-icon" aria-hidden="true"><i class="ti ti-messages"></i></span>
                        <h2>{{ __('اختر مجموعة لتفتح محادثتها') }}</h2>
                        <p>
                            @if ($unreadTotal)
                                {{ $unreadTotal === 1 ? __(':n رسالة غير مقروءة', ['n' => 1]) : __(':n رسائل غير مقروءة', ['n' => $unreadTotal]) }}
                                {{ $unreadGroups === 1 ? __('في مجموعة واحدة') : __('في :n مجموعات', ['n' => $unreadGroups]) }} — {{ __('في أعلى القائمة.') }}
                            @else
                                {{ __('لا رسائل غير مقروءة — كل شيء مقروء.') }}
                            @endif
                        </p>
                        @if ($unreadTotal)
                            <a href="{{ route('supervisor.discussion', $projects->first(fn ($p) => isset($unread[$p->id]))->id) }}" class="btn btn-primary">
                                <i class="ti ti-message-dots me-1" aria-hidden="true"></i>
                                {{ __('افتح أول غير مقروءة') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    @endif

@endsection

@push('js')
    <script>
        // بحث في المحادثات بالعنوان وأسماء الطلاب
        (function () {
            var input = document.getElementById('chat-search');
            if (!input) return;
            var items = Array.prototype.slice.call(document.querySelectorAll('.chat-item'));
            var none = document.getElementById('chat-none');

            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase();
                var shown = 0;
                items.forEach(function (item) {
                    var ok = q === '' || item.dataset.search.indexOf(q) !== -1;
                    item.classList.toggle('d-none', !ok);
                    if (ok) shown++;
                });
                none.classList.toggle('d-none', shown > 0);
            });
        })();
    </script>
@endpush
