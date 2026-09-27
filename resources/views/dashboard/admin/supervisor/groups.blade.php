@extends('layouts.admin.admin')
@section('title', "مجموعات {$supervisor->name}")

@section('crumbs')
    <x-crumb :href="route('admin.supervisors.index')">المشرفون</x-crumb>
    <x-crumb>{{ $supervisor->name }}</x-crumb>
@endsection

@section('content')

    @php
        $count = $projects->count();
        $max = (int) $supervisor->max_group;
    @endphp

    <x-page-header title="مجموعات {{ $supervisor->name }}"
        subtitle="{{ $semester?->name ?? 'لا فصل نشط' }} · {{ $supervisor->specialize->name ?: 'بلا تخصص' }} · {{ $count }} من {{ $max }}{{ $count > $max ? ' — تجاوز الحدّ' : '' }}" />

    @forelse ($projects as $project)
        <section class="dist-panel mb-3">
            <div class="dist-head">
                <span>
                    <a href="{{ route('admin.groups.show', $project->id) }}" class="text-reset">{{ $project->title }}</a>
                    <small class="text-secondary fw-normal ms-2">{{ $project->project_type->name ?? '—' }}</small>
                </span>
                <x-status-badge :status="$project->status" />
            </div>

            @foreach ($project->group->sortBy(fn ($g) => $g->type === 'leader' ? 0 : 1) as $member)
                <div class="ctx-person">
                    <x-avatar :user="$member->student" class="ctx-avatar" />
                    <span class="ctx-person-body">
                        <span class="ctx-person-name">
                            {{ $member->student?->name ?? 'طالب محذوف' }}
                            @if ($member->type === 'leader')
                                <span class="ctx-tag">قائد</span>
                            @endif
                        </span>
                        {{-- التخصص الفعلي للطالب — كان العمود يعرض عنوان المشروع --}}
                        <span class="ctx-person-meta">
                            <span dir="ltr">{{ $member->student?->university_id }}</span>
                            · {{ $member->student?->specialize?->name ?: '—' }}
                            @if ($member->student?->phone)
                                · <span dir="ltr">{{ $member->student->phone }}</span>
                            @endif
                        </span>
                    </span>
                </div>
            @endforeach
        </section>
    @empty
        <div class="dist-panel">
            <x-empty-state icon="ti-users-group" title="لا مجموعات هذا الفصل"
                text="لم يُقبل لهذا المشرف مشروع في الفصل الحالي بعد." class="py-6" />
        </div>
    @endforelse

@endsection
