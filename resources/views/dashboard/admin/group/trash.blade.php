@extends('layouts.admin.admin')
@section('title', __('المجموعات المحذوفة'))

@section('crumbs')
    <x-crumb :href="route('admin.groups.index')">{{ __('المجموعات') }}</x-crumb>
    <x-crumb>{{ __('المحذوفات') }}</x-crumb>
@endsection

@section('content')

    <x-page-header title="{{ __('المجموعات المحذوفة') }}"
        subtitle="{{ __(':n مشروعاً محذوفاً — الاسترجاع يعيد المراحل والملفات والتعليقات والدرجة.', ['n' => $projects->total()]) }}">
        <x-slot:actions>
            <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-primary">
                <i class="ti ti-list me-1" aria-hidden="true"></i>
                {{ __('كل المجموعات') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($projects->count())
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>{{ __('المشروع') }}</th>
                            <th>{{ __('المشرف') }}</th>
                            <th>{{ __('ما سيُفقد بالحذف النهائي') }}</th>
                            <th>{{ __('حُذف') }}</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $project->title }}</span>
                                    <div class="text-secondary small">
                                        {{ $project->project_type->name }} · {{ $project->semester->label ?? '—' }}
                                    </div>
                                </td>
                                <td>{{ $project->supervisor->name ?: '—' }}</td>
                                <td>
                                    {{-- الأدمن يرى ما سيضيع قبل أن يقرر --}}
                                    <div class="loss-list">
                                        <span>{{ __(':n عضواً', ['n' => $project->group_count]) }}</span>
                                        <span>{{ __(':n مرحلة', ['n' => $project->milestones_count]) }}</span>
                                        <span>{{ __(':n ملفاً', ['n' => $project->files_count]) }}</span>
                                        <span>{{ __(':n تعليقاً', ['n' => $project->comments_count]) }}</span>
                                        @if (! is_null($project->grade))
                                            <span class="loss-grade">
                                                <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                                                {{ __('درجة مرصودة') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-secondary small">{{ $project->deleted_at?->diffForHumans() }}</td>
                                <td>
                                    <div class="row-actions">
                                        <form action="{{ route('admin.groups.restore', $project->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="btn-action" title="{{ __('استرجاع') }}" aria-label="{{ __('استرجاع') }}">
                                                <i class="ti ti-arrow-back-up" aria-hidden="true"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.groups.forceDestroy', $project->id) }}"
                                            method="post" class="force-form"
                                            data-confirm-title="{{ __('حذف نهائي') }}"
                                            data-confirm="{{ __('حذف «:name» نهائياً؟', ['name' => $project->title]) }}&#10;{{ __('سيُمحى من قاعدة البيانات مع مراحله وملفاته وتعليقاته ودرجته، ولا يمكن استرجاعه بعدها.') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action--danger"
                                                title="{{ __('حذف نهائي') }}" aria-label="{{ __('حذف نهائي') }}"
                                                data-title="{{ $project->title }}">
                                                <i class="ti ti-trash-x" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($projects->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {!! $projects->links() !!}
                </div>
            @endif
        @else
            <x-empty-state icon="ti-trash" title="{{ __('لا توجد مجموعات محذوفة') }}"
                text="{{ __('كل المجموعات المحذوفة ستظهر هنا، ويمكن استرجاعها بكل بياناتها.') }}" class="py-6" />
        @endif
    </div>


@endsection
