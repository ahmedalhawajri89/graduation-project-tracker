@extends('layouts.admin.admin')
@section('title', 'النقاش مع المشرف')

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">لوحتي</x-crumb>
    <x-crumb>النقاش</x-crumb>
@endsection

@section('content')

    <x-page-header title="النقاش مع المشرف"
        subtitle="{{ $project ? $project->title : 'اسأل مشرفك عن أي شيء في مشروعك' }}" />

    @if (! $project)
        <div class="dist-panel">
            <x-empty-state icon="ti-messages-off" title="لا نقاش قبل المشروع"
                text="يُفتح النقاش مع المشرف حين تقدّم مقترح مشروعك وتختار مشرفه." class="py-6">
                <x-slot:action>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                        <i class="ti ti-rocket me-1" aria-hidden="true"></i>
                        تقديم المقترح
                    </a>
                </x-slot:action>
            </x-empty-state>
        </div>
    @else
        {{-- محادثة واحدة: لا قائمة، فالطالب له مشرف واحد --}}
        <section class="chat-shell is-single">
            <div class="chat-pane">
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

                @include('dashboard.discussion._thread', [
                    'project' => $project,
                    'lastRead' => $lastRead,
                    'role' => 'student',
                ])
            </div>
        </section>
    @endif

@endsection
