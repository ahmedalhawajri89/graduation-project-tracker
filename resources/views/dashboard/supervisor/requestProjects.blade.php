@extends('layouts.admin.admin')
@section('title', 'طلبات الإشراف')

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">لوحتي</x-crumb>
    <x-crumb>طلبات الإشراف</x-crumb>
@endsection

@section('content')

    @php
        $pending = $requests->count();
        // المقاعد هي ما يُقرَّر على أساسه — تُقال قبل الطلبات لا بعدها
        $seatsNote = match (true) {
            $seatsLeft < 0 => 'تجاوزت حدّك بـ' . abs($seatsLeft) . ' — لا قبول قبل رفع الحدّ من الإدارة',
            $seatsLeft === 0 => 'اكتمل حدّك (' . $maxGroup . ' من ' . $maxGroup . ') — لا قبول قبل رفع الحدّ من الإدارة',
            $seatsLeft === 1 => 'بقي لك مقعد واحد من ' . $maxGroup,
            $seatsLeft === 2 => 'بقي لك مقعدان من ' . $maxGroup,
            default => 'بقي لك ' . $seatsLeft . ' مقاعد من ' . $maxGroup,
        };
    @endphp

    <x-page-header title="طلبات الإشراف" subtitle="{{ $seatsNote }}" />

    {{-- ===== القرارات ===== --}}
    @if ($pending)
        @if ($seatsLeft === 1 && $pending > 1)
            <p class="hint-bar mb-3" role="status">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                <span>
                    <b>{{ $pending }} طلبات ومقعد واحد.</b>
                    قبول أيّها يرفض الباقي تلقائياً ويُبلَّغ أصحابها — اقرأها كلّها قبل أن تقرّر.
                </span>
            </p>
        @endif

        <div class="req-list mb-4">
            @foreach ($requests as $project)
                @include('dashboard.supervisor._request-card', [
                    'project' => $project,
                    'seatsLeft' => $seatsLeft,
                    'pending' => $pending,
                ])
            @endforeach
        </div>
    @else
        <div class="dist-panel mb-4">
            <x-empty-state icon="ti-inbox-off" title="لا طلبات تنتظرك"
                text="حين يختارك فريق مشرفاً لمقترحه، يظهر طلبه هنا لتقبله أو ترفضه." class="py-5" />
        </div>
    @endif

    {{-- ===== التحديثات: كانت فوق الطلبات وتزاحمها ===== --}}
    <section class="dist-panel">
        <div class="dist-head">
            <span>آخر التحديثات</span>
            <span class="dist-head-note">نشاط المشاريع وتغييرات الإدارة</span>
        </div>

        @forelse ($updates as $notification)
            <div class="notif-brief">
                <b>
                    {{ $notification->data['project'] ?? '' }}
                    @if ($notification->type === \App\Notifications\AdminChangeGroupNotify::class)
                        <span class="cmt-role">الإدارة</span>
                    @endif
                </b>
                <p>
                    @if (! empty($notification->data['supervisor_name']))
                        {{ $notification->data['supervisor_name'] }}:
                    @endif
                    {{ $notification->data['msg'] ?? '' }}
                </p>
                <time>{{ $notification->created_at->diffForHumans() }}</time>
            </div>
        @empty
            <p class="todo-clear">
                <i class="ti ti-circle-check" aria-hidden="true"></i>
                لا تحديثات بعد.
            </p>
        @endforelse
    </section>

@endsection
