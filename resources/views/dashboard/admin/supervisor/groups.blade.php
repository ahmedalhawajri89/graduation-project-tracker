@extends('layouts.admin.admin')
@section('title', __('مجموعات :name', ['name' => $supervisor->name]))

@section('crumbs')
    <x-crumb :href="route('admin.supervisors.index')">{{ __('المشرفون') }}</x-crumb>
    <x-crumb>{{ $supervisor->name }}</x-crumb>
@endsection

@section('content')

    @php
        $count = $summary['groups'];
        $max = (int) $supervisor->max_group;
        $over = $count > $max;
        $capPct = $max > 0 ? min(100, (int) round($count * 100 / $max)) : 100;
        $issueMeta = \App\Support\TeamHealth::issues();
    @endphp

    <x-page-header title="{{ __('مجموعات :name', ['name' => $supervisor->name]) }}"
        subtitle="{{ $semester?->label ?? __('لا فصل نشط') }} · {{ $supervisor->specialize->name ?: __('بلا تخصص') }}">
        <x-slot:actions>
            <a href="{{ route('admin.groups.index', array_filter(['supervisor' => $supervisor->id, 'semester' => $semester?->id])) }}"
                class="btn btn-outline-primary">
                <i class="ti ti-table me-1" aria-hidden="true"></i>
                {{ __('في جدول المجموعات') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ بطاقة المشرف: من هو، وكيف يُتواصل معه، وكم بقي من سعته ═══ --}}
    <section class="sg-head">
        <div class="sg-who">
            <x-avatar :user="$supervisor" class="cell-avatar sg-avatar" />
            <div class="sg-who-body">
                <b>{{ $supervisor->name }}</b>
                <span>{{ $supervisor->specialize->name ?: __('بلا تخصص') }}</span>
                <span class="sg-contact">
                    <a href="mailto:{{ $supervisor->email }}" dir="ltr"><i class="ti ti-mail" aria-hidden="true"></i>{{ $supervisor->email }}</a>
                    @if ($supervisor->phone)
                        <a href="tel:{{ $supervisor->phone }}" dir="ltr"><i class="ti ti-phone" aria-hidden="true"></i>{{ $supervisor->phone }}</a>
                    @endif
                </span>
            </div>
        </div>

        {{-- حلقة السعة: المقبول من الحدّ، وحمراء إن تجاوزه --}}
        <div class="sg-cap {{ $over ? 'is-over' : '' }}" style="--p: {{ $capPct }}">
            <span class="sg-cap-ring" aria-hidden="true"></span>
            <span class="sg-cap-text">
                <b><bdi dir="ltr">{{ $count }} / {{ $max }}</bdi></b>
                <small>{{ $over ? __('تجاوز الحدّ الأقصى') : ($max - $count > 0 ? __(':n مقاعد متبقية', ['n' => $max - $count]) : __('اكتملت السعة')) }}</small>
            </span>
        </div>
    </section>

    {{-- ═══ الملخّص ═══ --}}
    <section class="sg-stats" aria-label="{{ __('ملخّص المجموعات') }}">
        <div><b>{{ $summary['groups'] }}</b><small>{{ __('مجموعة هذا الفصل') }}</small></div>
        <div><b>{{ $summary['students'] }}</b><small>{{ __('طالباً') }}</small></div>
        <div><b>{{ $summary['progress'] === null ? '—' : $summary['progress'] . '%' }}</b><small>{{ __('متوسط الإنجاز') }}</small></div>
        <div class="{{ $summary['issues'] ? 'is-warn' : '' }}"><b>{{ $summary['issues'] }}</b><small>{{ __('تحتاج انتباهاً') }}</small></div>
    </section>

    {{-- ═══ المجموعات ═══ --}}
    @if ($projects->count())
        <div class="sg-grid">
            @foreach ($projects as $project)
                @php
                    $members = $project->group->sortBy(fn ($g) => $g->type === 'leader' ? 0 : 1);
                    $next = $project->next_stage;
                    $late = $next && $next->due_date && \Illuminate\Support\Carbon::parse($next->due_date)->lt(today());
                @endphp
                <article class="sg-card {{ $project->issues ? 'has-issues' : '' }}">
                    <header class="sg-card-head">
                        <div>
                            <a href="{{ route('admin.groups.show', $project->id) }}" class="sg-title">{{ $project->title }}</a>
                            <span class="sg-type">{{ $project->project_type->name ?? '—' }}</span>
                        </div>
                        <x-status-badge :status="$project->status" />
                    </header>

                    <div class="sg-progress">
                        <span class="sg-bar"><i style="width: {{ $project->progress }}%"></i></span>
                        <span class="sg-progress-text"><b>{{ $project->progress }}%</b> · {{ __(':done من :total مراحل', ['done' => $project->stages_done, 'total' => $project->stages_total]) }}</span>
                    </div>

                    <div class="sg-next {{ $late ? 'is-late' : '' }}">
                        @if ($project->grade !== null)
                            <i class="ti ti-award" aria-hidden="true"></i>
                            <span>{{ __('الدرجة') }} <b>{{ $project->grade }}</b> / 100</span>
                        @elseif ($next)
                            <i class="ti {{ $late ? 'ti-alarm' : 'ti-flag' }}" aria-hidden="true"></i>
                            <span>
                                {{ __('التالية:') }} <b>{{ $next->title }}</b>
                                @if ($next->due_date)
                                    · {{ $late ? __('فات موعدها منذ :ago', ['ago' => \Illuminate\Support\Carbon::parse($next->due_date)->diffForHumans(null, true)]) : __('موعدها :date', ['date' => \Illuminate\Support\Carbon::parse($next->due_date)->format('Y-m-d')]) }}
                                @endif
                            </span>
                        @elseif ($project->stages_total)
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            <span>{{ __('أُنجزت كل المراحل — بانتظار الدرجة') }}</span>
                        @else
                            <i class="ti ti-list-details" aria-hidden="true"></i>
                            <span>{{ __('لا مراحل بعد') }}</span>
                        @endif
                    </div>

                    @if ($project->issues)
                        <div class="sg-issues">
                            @foreach ($project->issues as $key => $meta)
                                <span class="sg-issue"><i class="ti {{ $meta[2] }}" aria-hidden="true"></i>{{ $meta[0] }}</span>
                            @endforeach
                        </div>
                    @endif

                    <footer class="sg-members">
                        @foreach ($members as $member)
                            <span class="sg-member" title="{{ $member->student?->name }} · {{ $member->student?->university_id }}">
                                <x-avatar :user="$member->student" class="cell-avatar sg-member-av" />
                                <span>
                                    {{ $member->student?->name ?? __('طالب محذوف') }}
                                    @if ($member->type === 'leader')<em>{{ __('قائد') }}</em>@endif
                                </span>
                            </span>
                        @endforeach
                        <a href="{{ route('admin.groups.show', $project->id) }}" class="sg-open" aria-label="{{ __('فتح :title', ['title' => $project->title]) }}">
                            <i class="ti ti-arrow-left" aria-hidden="true"></i>
                        </a>
                    </footer>
                </article>
            @endforeach
        </div>
    @else
        <div class="dist-panel">
            <x-empty-state icon="ti-users-group" title="{{ __('لا مجموعات هذا الفصل') }}"
                text="{{ __('لم يُقبل لهذا المشرف مشروع في الفصل الحالي بعد.') }}" class="py-6" />
        </div>
    @endif

    {{-- ═══ الفصول السابقة ═══ --}}
    @if ($past->count())
        <section class="sg-past">
            <h2><i class="ti ti-history" aria-hidden="true"></i> {{ __('الفصول السابقة') }}</h2>
            <div class="sg-past-list">
                @foreach ($past as $row)
                    <a href="{{ route('admin.groups.index', ['supervisor' => $supervisor->id, 'semester' => $row->semester_id]) }}">
                        <span>{{ $row->semester->label }}</span>
                        <b>{{ $row->n }} {{ $row->n == 1 ? __('مجموعة') : __('مجموعات') }}</b>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

@endsection
