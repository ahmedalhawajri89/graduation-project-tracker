{{--
    محتوى قائمة الإشعارات المنسدلة — يُرسم مع الصفحة، ويعيد LiveController
    رسمه كل بضع ثوانٍ فيستبدله live.js دون إعادة تحميل.

    @param int $unreadCount
    @param \Illuminate\Support\Collection $latestNotifications
    @param string $notifyRoute
--}}
<div class="notif-menu-head">
    <span class="fw-bold">{{ __('الإشعارات') }}</span>
    @if ($unreadCount > 0)
        <span class="badge bg-red-lt text-red">{{ __(':n جديد', ['n' => $unreadCount]) }}</span>
    @endif
</div>

@if ($latestNotifications->count() === 0)
    <div class="notif-empty">
        <i class="ti ti-bell-off"></i>
        <span>{{ __('لا توجد إشعارات بعد') }}</span>
    </div>
@else
    @foreach ($latestNotifications as $notification)
        {{-- الطالب: أيقونة الفئة (قبول، تعديل، إدارة…) كصفحة إشعاراته --}}
        @php
            $nv = auth()->guard('student')->check()
                ? \App\Support\NotificationView::present($notification)
                : ['icon' => 'ti-message', 'tone' => 'is-brand'];
        @endphp
        <a href="{{ $notifyRoute }}" class="notif-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
            <span class="notif-dot" aria-hidden="true"></span>
            <span class="notif-card-icon is-sm {{ $nv['tone'] }}" aria-hidden="true">
                <i class="ti {{ $nv['icon'] }}"></i>
            </span>
            <span class="notif-body">
                <span class="notif-title">
                    {{ $notification->data['project'] ?? ($notification->data['title'] ?? __('إشعار')) }}
                </span>
                <span class="notif-text">
                    {{ \Illuminate\Support\Str::limit(__($notification->data['msg'] ?? ($notification->data['type'] ?? '')), 70) }}
                </span>
                <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
            </span>
        </a>
    @endforeach
@endif

<a href="{{ $notifyRoute }}" class="notif-menu-foot">
    {{ __('عرض كل الإشعارات') }}
    <i class="ti ti-arrow-left"></i>
</a>
