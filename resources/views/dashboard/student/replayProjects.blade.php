@extends('layouts.admin.admin')
@section('title', 'الإشعارات')

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">لوحتي</x-crumb>
    <x-crumb>الإشعارات</x-crumb>
@endsection

@section('content')

    <x-page-header title="الإشعارات"
        subtitle="{{ count($newIds) ? count($newIds) . ' جديد منذ آخر زيارة' : 'ردود المشرف وتحديثات مشروعك' }}" />

    {{-- كان جدولاً بخمسة رؤوس يتجاوز عرض الجوال --}}
    <section class="dist-panel">
        @forelse ($notifications as $notification)
            <div class="notif-brief {{ in_array($notification->id, $newIds, true) ? 'is-new' : '' }}">
                <b>
                    {{ $notification->data['supervisor_name'] ?? 'الإدارة' }}
                    @if (! empty($notification->data['project']))
                        <span class="notif-project">· {{ $notification->data['project'] }}</span>
                    @endif
                </b>
                <p>{{ $notification->data['msg'] ?? '' }}</p>
                <time datetime="{{ $notification->created_at->toIso8601String() }}"
                    title="{{ $notification->created_at->format('Y-m-d H:i') }}">
                    {{ $notification->created_at->diffForHumans() }}
                </time>
            </div>
        @empty
            <x-empty-state icon="ti-bell-off" title="لا إشعارات بعد"
                text="ستصلك هنا ردود المشرف وتحديثات مشروعك." class="py-6" />
        @endforelse
    </section>

@endsection
