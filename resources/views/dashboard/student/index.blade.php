@extends('layouts.admin.admin')
@section('title', __('لوحتي'))

@section('crumbs')
    <x-crumb>{{ __('لوحتي') }}</x-crumb>
@endsection

@section('content')

    @php
        // المشروع النشط: أول مجموعة لم يُرفض مشروعها
        $activeProject = null;
        $lastRejected = null;

        if ($student->groups->count() > 0) {
            $first = $student->groups->first()->project;
            if ($first && $first->status !== 'reject') {
                $activeProject = $first;
            } elseif ($first) {
                $lastRejected = $first;
            }
        }

        // مشروع محذوف حذفاً مرناً يحجز طلابه (يُسترجع، فلا يُضمّون لفريق ثانٍ).
        // كان الطالب يرى نموذج المقترح، ثم يُرفض تقديمه برسالة لا تشرح السبب
        $held = null;
        if (! $activeProject && ! $lastRejected && $student->groups->count() > 0) {
            $held = \App\Models\Project::onlyTrashed()
                ->where('status', '!=', 'reject')
                ->find($student->groups->first()->project_id);
        }

        $lastNotification = auth()->user()->notifications->first();
    @endphp

    {{-- بمشروع نشط: التحية داخل بطاقة المشروع — لا ترويسة فوقها تكرّرها --}}
    @unless ($activeProject)
        <x-page-header :title="__('أهلاً، :name', ['name' => $student->name])" subtitle="{{ $semester->label }}" />
    @endunless

    @if ($held)
        <section class="start-panel mb-4" role="status">
            <i class="ti ti-archive" aria-hidden="true"></i>
            <div>
                <h2>{{ __('مشروعك «:title» موقوف لدى الإدارة', ['title' => $held->title]) }}</h2>
                <p>
                    {{ __('حذفته إدارة القسم حذفاً مؤقتاً، وما زلت مرتبطاً به فلا تستطيع تقديم مقترح جديد.') }}
                    {{ __('راجع الإدارة لاسترجاعه أو حذفه نهائياً.') }}
                </p>
            </div>
        </section>
    @elseif ($activeProject)
        @include('dashboard.student._project', ['project' => $activeProject, 'student' => $student, 'semester' => $semester])
    @else
        @include('dashboard.student._no-project', [
            'lastRejected' => $lastRejected,
            'lastNotification' => $lastNotification,
        ])
    @endif

@endsection
