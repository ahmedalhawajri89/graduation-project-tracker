@extends('layouts.admin.admin')
@section('title', __('طلبات الإشراف'))

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">{{ __('لوحتي') }}</x-crumb>
    <x-crumb>{{ __('طلبات الإشراف') }}</x-crumb>
@endsection

@section('content')

    @php
        $pending = $requests->count();
        // المقاعد هي ما يُقرَّر على أساسه — تُقال قبل الطلبات لا بعدها
        $seatsNote = match (true) {
            $seatsLeft < 0 => __('تجاوزت حدّك بـ:n — لا قبول قبل رفع الحدّ من الإدارة', ['n' => abs($seatsLeft)]),
            $seatsLeft === 0 => __('اكتمل حدّك — لا قبول قبل رفع الحدّ من الإدارة'),
            $seatsLeft === 1 => __('بقي لك مقعد واحد'),
            $seatsLeft === 2 => __('بقي لك مقعدان'),
            default => __('بقي لك :n مقاعد', ['n' => $seatsLeft]),
        };
        // نقاط المقاعد: المشغول ممتلئ والمتاح فارغ، والتجاوز يُرسم زائداً
        $pips = max($maxGroup, $acceptedCount);
    @endphp

    <x-page-header title="{{ __('طلبات الإشراف') }}" subtitle="{{ __('قرّر من تشرف عليه هذا الفصل — مقاعدك تُحسب مع كل قبول') }}" />

    {{-- ===== السعة: ما يُقرَّر على أساسه ===== --}}
    <section class="seat-panel mb-4 {{ $seatsLeft <= 0 ? 'is-full' : '' }}" aria-label="{{ __('مقاعدك') }}">
        <div class="seat-main">
            <span class="seat-label">{{ __('مقاعدك هذا الفصل') }}</span>
            <div class="seat-pips" role="img" aria-label="{{ __(':n مشغولة من :max', ['n' => $acceptedCount, 'max' => $maxGroup]) }}">
                @for ($i = 1; $i <= $pips; $i++)
                    <span class="seat-pip {{ $i <= $acceptedCount ? ($i > $maxGroup ? 'is-over' : 'is-taken') : '' }}"></span>
                @endfor
            </div>
            <span class="seat-note">{{ $seatsNote }} <small>· {{ __(':n من :max مشغولة', ['n' => $acceptedCount, 'max' => $maxGroup]) }}</small></span>
        </div>
        <div class="seat-stat">
            <b class="{{ $pending ? 'is-warn' : '' }}">{{ $pending }}</b>
            <span>{{ $pending === 1 ? __('طلب ينتظرك') : __('طلبات تنتظرك') }}</span>
        </div>
        <div class="seat-stat">
            <b>{{ $acceptedCount }}</b>
            <span>{{ __('قبلتها هذا الفصل') }}</span>
        </div>
    </section>

    <div class="dash-grid">
        <div class="dash-main">
            {{-- ===== القرارات ===== --}}
            @if ($pending)
                <div class="dash-section-head">
                    <h2>{{ __('بانتظار قرارك') }}</h2>
                    <span class="dash-section-meta">{{ __('الأقدم أولاً — انتظر أطول') }}</span>
                </div>

                @if ($seatsLeft === 1 && $pending > 1)
                    <p class="hint-bar mb-3" role="status">
                        <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                        <span>
                            <b>{{ __(':n طلبات ومقعد واحد.', ['n' => $pending]) }}</b>
                            {{ __('قبول أيّها يرفض الباقي تلقائياً ويُبلَّغ أصحابها — اقرأها كلّها قبل أن تقرّر.') }}
                        </span>
                    </p>
                @endif

                <div class="req-list">
                    @foreach ($requests as $project)
                        @include('dashboard.supervisor._request-card', [
                            'project' => $project,
                            'seatsLeft' => $seatsLeft,
                            'pending' => $pending,
                            'similar' => $similar[$project->id] ?? collect(),
                        ])
                    @endforeach
                </div>
            @else
                <div class="card req-empty">
                    <span class="req-empty-icon" aria-hidden="true"><i class="ti ti-inbox"></i></span>
                    <h2>{{ __('لا طلبات تنتظرك') }}</h2>
                    <p>
                        {{ __('حين يختارك فريق مشرفاً لمقترحه، يظهر طلبه هنا بفريقه ووصفه لتقبله أو ترفضه.') }}
                        @if ($seatsLeft > 0)
                            <br>{{ __(':note لطلبات جديدة.', ['note' => $seatsNote]) }}
                        @endif
                    </p>
                    @unless ($hasPlan)
                        <a href="{{ route('supervisor.plan', ['new' => 1]) }}#stage-new" class="btn btn-outline-secondary">
                            <i class="ti ti-route me-1" aria-hidden="true"></i>
                            {{ __('جهّز خطة المراحل ريثما تصل') }}
                        </a>
                    @endunless
                </div>
            @endif
        </div>

        <aside class="dash-side">
            {{-- ===== ما قرّرته ===== --}}
            <section class="ctx-card dash-panel" aria-labelledby="decisions-title">
                <h2 class="ctx-head" id="decisions-title">
                    <i class="ti ti-history" aria-hidden="true"></i>
                    {{ __('قراراتك هذا الفصل') }}
                </h2>
                @if ($decisions->isEmpty())
                    <p class="dash-panel-empty">{{ __('لم تقرّر في طلب بعد هذا الفصل.') }}</p>
                @else
                    <ol class="decision-list">
                        @foreach ($decisions as $d)
                            @php $accepted = $d->status !== 'reject'; @endphp
                            <li>
                                @if ($accepted)
                                    <a href="{{ route('supervisor.projects.show', $d->id) }}" class="decision-item">
                                @else
                                    <div class="decision-item">
                                @endif
                                    <span class="decision-icon {{ $accepted ? 'is-accept' : 'is-reject' }}" aria-hidden="true">
                                        <i class="ti {{ $accepted ? 'ti-check' : 'ti-x' }}"></i>
                                    </span>
                                    <span class="decision-body">
                                        <span class="decision-title">{{ $d->title }}</span>
                                        <span class="decision-meta">
                                            {{ $accepted ? __('قبلته') : __('رفضته') }}
                                            @if ($d->group->first()?->student)
                                                · {{ $d->group->first()->student->name }}
                                            @endif
                                            · {{ $d->updated_at->diffForHumans() }}
                                        </span>
                                    </span>
                                @if ($accepted)
                                    </a>
                                @else
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            {{-- ===== التحديثات: كانت فوق الطلبات وتزاحمها ===== --}}
            <section class="ctx-card dash-panel" aria-labelledby="updates-title">
                <h2 class="ctx-head" id="updates-title">
                    <i class="ti ti-bell" aria-hidden="true"></i>
                    {{ __('آخر التحديثات') }}
                </h2>
                @if ($updates->isEmpty())
                    <p class="dash-panel-empty">{{ __('لا تحديثات بعد.') }}</p>
                @else
                    <ol class="activity-feed">
                        @foreach ($updates as $notification)
                            @php $byAdmin = $notification->type === \App\Notifications\AdminChangeGroupNotify::class; @endphp
                            <li class="activity-item">
                                <span class="activity-icon {{ $byAdmin ? 'is-admin' : '' }}" aria-hidden="true">
                                    <i class="ti {{ $byAdmin ? 'ti-shield' : 'ti-bolt' }}"></i>
                                </span>
                                <span class="activity-body">
                                    <span class="activity-text">
                                        @if (! empty($notification->data['supervisor_name']))
                                            {{ $notification->data['supervisor_name'] }}:
                                        @endif
                                        {{ __($notification->data['msg'] ?? '') }}
                                    </span>
                                    <span class="activity-meta">
                                        {{ $byAdmin ? __('الإدارة') . ' · ' : '' }}{{ $notification->data['project'] ?? '' }}
                                        · <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </aside>
    </div>

@endsection
