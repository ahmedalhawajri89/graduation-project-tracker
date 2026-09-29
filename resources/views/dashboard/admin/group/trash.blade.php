@extends('layouts.admin.admin')
@section('title', 'المجموعات المحذوفة')

@section('crumbs')
    <x-crumb :href="route('admin.groups.index')">المجموعات</x-crumb>
    <x-crumb>المحذوفات</x-crumb>
@endsection

@section('content')

    <x-page-header title="المجموعات المحذوفة"
        subtitle="{{ $projects->total() }} مشروعاً محذوفاً — الاسترجاع يعيد المراحل والملفات والتعليقات والدرجة.">
        <x-slot:actions>
            <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-primary">
                <i class="ti ti-list me-1" aria-hidden="true"></i>
                كل المجموعات
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($projects->count())
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>المشروع</th>
                            <th>المشرف</th>
                            <th>ما سيُفقد بالحذف النهائي</th>
                            <th>حُذف</th>
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
                                        <span>{{ $project->group_count }} عضواً</span>
                                        <span>{{ $project->milestones_count }} مرحلة</span>
                                        <span>{{ $project->files_count }} ملفاً</span>
                                        <span>{{ $project->comments_count }} تعليقاً</span>
                                        @if (! is_null($project->grade))
                                            <span class="loss-grade">
                                                <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                                                درجة مرصودة
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-secondary small">{{ $project->deleted_at?->diffForHumans() }}</td>
                                <td>
                                    <div class="row-actions">
                                        <form action="{{ route('admin.groups.restore', $project->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="btn-action" title="استرجاع" aria-label="استرجاع">
                                                <i class="ti ti-arrow-back-up" aria-hidden="true"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.groups.forceDestroy', $project->id) }}"
                                            method="post" class="force-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action--danger"
                                                title="حذف نهائي" aria-label="حذف نهائي"
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
            <x-empty-state icon="ti-trash" title="لا توجد مجموعات محذوفة"
                text="كل المجموعات المحذوفة ستظهر هنا، ويمكن استرجاعها بكل بياناتها." class="py-6" />
        @endif
    </div>

    @push('js')
        <script>
            // الحذف النهائي لا رجعة فيه — يتطلّب تأكيداً صريحاً
            document.querySelectorAll('.force-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    var btn = form.querySelector('button[type="submit"]');
                    var name = btn ? btn.dataset.title : 'هذا المشروع';
                    if (!confirm('حذف «' + name + '» نهائياً؟\n\nسيُمحى من قاعدة البيانات مع مراحله وملفاته وتعليقاته ودرجته، ولا يمكن استرجاعه بعدها.')) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    @endpush

@endsection
