@extends('layouts.admin.admin')
@section('title', "أنواع المشاريع — {$specialize->name}")

@section('crumbs')
    <x-crumb :href="route('admin.specialize.index')">التخصصات</x-crumb>
    <x-crumb>{{ $specialize->name }}</x-crumb>
@endsection

@section('content')

    @php
        $inUse = $types->where('projects_count', '>', 0)->count();
        $count = $types->count();
        $subtitle = 'تخصص ' . $specialize->name;
        if ($count) {
            $subtitle .= ' · ' . $count . ' ' . ($count == 1 ? 'نوع' : ($count == 2 ? 'نوعان' : 'أنواع'));
        }
    @endphp

    <x-page-header title="أنواع المشاريع" :subtitle="$subtitle">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة نوع مشروع
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- نوع المشروع ليس تسمية: حدّاه هما ما يقبل النظام به فريقاً أو
         يرفضه عند التسجيل. الشرح هنا لأن الحقلين في النافذة وحدهما
         لا يقولان أثرهما. --}}
    <div class="hint-bar mb-3">
        <i class="ti ti-info-circle" aria-hidden="true"></i>
        <span>
            حدّا الفريق يُطبَّقان عند تسجيل الطالب لمشروعه: فريق خارج المدى يُرفض.
            وتعديلهما لا يمسّ الفرق المسجَّلة سابقاً.
        </span>
    </div>

    @if ($types->count())
        <div class="card">
            <div class="type-list">
                @foreach ($types as $type)
                    <article class="type-row">
                        <div class="type-main">
                            <h3 class="type-name">{{ $type->name }}</h3>

                            {{-- الحدّان كانا عمودين منفصلين بعبارة «٢ عضو»
                                 و«٣ عضو» — وهما مفهوم واحد: مدى حجم الفريق --}}
                            <p class="type-range">
                                <i class="ti ti-users" aria-hidden="true"></i>
                                الفريق من
                                <b>{{ $type->min }}</b>
                                إلى
                                <b>{{ $type->max }}</b>
                                {{ $type->max == 2 ? 'عضوين' : ($type->max > 2 ? 'أعضاء' : 'عضو') }}
                            </p>
                        </div>

                        {{-- العدد الذي يُبنى عليه قرار التعديل والحذف، ولم
                             يكن معروضاً إطلاقاً --}}
                        <div class="type-usage">
                            @if ($type->projects_count > 0)
                                <a href="{{ route('admin.groups.index', ['type' => $type->id]) }}" class="type-usage-link">
                                    <span class="type-usage-n">{{ $type->projects_count }}</span>
                                    <span class="type-usage-l">مشروع يستعمله</span>
                                </a>
                            @else
                                <span class="type-usage-none">لم يُستعمل بعد</span>
                            @endif
                        </div>

                        <div class="btn-group">
                            @if ($type->projects_count > 0)
                                <span class="btn-action is-disabled"
                                    title="لا يمكن حذفه — يستعمله {{ $type->projects_count }} مشروعاً، وستفقد نوعها وحدود فريقها."
                                    aria-disabled="true">
                                    <i class="ti ti-trash" aria-hidden="true"></i>
                                </span>
                            @else
                                <button type="button" class="btn-action btn-action--danger btn-delete"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $type->id }}"
                                    data-name="{{ $type->name }}" title="حذف" aria-label="حذف">
                                    <i class="ti ti-trash" aria-hidden="true"></i>
                                </button>
                            @endif

                            <a class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="{{ $type->id }}" data-name="{{ $type->name }}" data-min="{{ $type->min }}"
                                data-max="{{ $type->max }}" title="تعديل" aria-label="تعديل">
                                <i class="ti ti-pencil" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @else
        {{-- هذه ليست قائمة فارغة عادية: بلا نوع واحد، لا يستطيع أي طالب
             في هذا التخصص تسجيل مشروع إطلاقاً --}}
        <div class="card">
            <x-empty-state icon="ti-shape" title="لا أنواع مشاريع في هذا التخصص"
                text="لا يستطيع طلاب «{{ $specialize->name }}» تسجيل مشروع حتى يوجد نوع واحد على الأقل: نموذج التسجيل يطلب النوع، ويتحقّق من حجم الفريق بحدّيه."
                class="py-6">
                <x-slot:action>
                    <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="ti ti-plus me-1" aria-hidden="true"></i>
                        إضافة أول نوع
                    </button>
                </x-slot:action>
            </x-empty-state>
        </div>
    @endif

    @include('dashboard.admin.setting.specialize.project.create_modal')
    @include('dashboard.admin.setting.specialize.project.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'نوع المشروع',
        'delete_controller_name' => 'admin.specialize.projects',
        'delete_note' => 'لا يمكن حذف نوع يستعمله مشروع قائم.',
    ])

@endsection

@push('js')
    <script>
        // إعادة فتح النافذة عند فشل التحقّق — كانت تأتي ضمن تضمين
        // \u200Edatatables_style_script\u200E وقد زال مع الجدول
        @if ($errors->any() && old('submit') == 'create')
            new bootstrap.Modal(document.getElementById('createModal')).show();
        @endif
        @if ($errors->any() && old('submit') == 'update')
            new bootstrap.Modal(document.getElementById('editModal')).show();
        @endif

        // قادم من بطاقة تخصص ينقصه نوع: تُفتح نافذة الإضافة مباشرةً
        if (window.location.hash === '#add') {
            new bootstrap.Modal(document.getElementById('createModal')).show();
        }
    </script>
@endpush
