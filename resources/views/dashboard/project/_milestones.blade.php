{{--
    مراحل المشروع — الطالب والمشرف يريانها بالشكل ذاته.

    @param \App\Models\Project $project
    @param bool                $editable  المشرف والمشروع غير مقفل: الدائرة
                                          زرّ تبديل، وحذف، وسطر إضافة
--}}

@php $editable = $editable ?? false; @endphp

<section class="dist-panel mb-4" id="milestones">
    <div class="dist-head">
        <span class="dist-head-title">
            <i class="ti ti-list-check" aria-hidden="true"></i>
            مراحل المشروع
        </span>
        @if ($project->milestones->count())
            <span class="dist-head-note">
                {{ $project->milestones->where('is_done', true)->count() }}
                من {{ $project->milestones->count() }} منجزة
            </span>
        @endif
    </div>

    @if ($editable)
        <form action="{{ route('supervisor.milestones.store', ['project' => $project->id]) }}" method="POST"
            class="ms-add">
            @csrf
            <input type="text" name="title" required maxlength="150"
                class="form-control @error('title') is-invalid @enderror"
                placeholder="مرحلة جديدة — مثال: تسليم فصل التحليل" aria-label="عنوان المرحلة"
                value="{{ old('title') }}">
            <input type="date" name="due_date" class="form-control" aria-label="تاريخ الاستحقاق"
                value="{{ old('due_date') }}">
            <button type="submit" class="btn btn-primary" data-loading-text="..">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة
            </button>
        </form>
    @endif

    @if ($project->milestones->count() === 0)
        <x-empty-state icon="ti-list-details" title="لم تُضَف مراحل بعد"
            :text="$editable
                ? 'قسّم المشروع إلى مراحل بمواعيد — يراها الفريق فور إضافتها، ومنها تُحسب نسبة الإنجاز.'
                : 'يضع المشرف مراحل المشروع ومواعيدها، وستظهر هنا فور إضافتها.'"
            class="is-inline" />
    @else
        <div class="ms-progress"><span style="width: {{ $project->progress }}%"></span></div>

        @foreach ($project->milestones as $milestone)
            @php
                $overdue = ! $milestone->is_done && $milestone->due_date && $milestone->due_date->isPast();
                $icon = $milestone->is_done ? 'ti-check' : ($overdue ? 'ti-alert-triangle' : 'ti-clock');
            @endphp
            <div class="ms-row {{ $milestone->is_done ? 'is-done' : '' }} {{ $overdue ? 'is-overdue' : '' }}">
                @if ($editable)
                    <form action="{{ route('supervisor.milestones.toggle', ['milestone' => $milestone->id]) }}"
                        method="POST">
                        @csrf
                        <button type="submit" class="ms-check"
                            title="{{ $milestone->is_done ? 'إرجاعها قيد التنفيذ' : 'وضع علامة منجزة' }}"
                            aria-label="{{ $milestone->is_done ? 'إرجاع' : 'إنجاز' }} {{ $milestone->title }}">
                            <i class="ti {{ $icon }}" aria-hidden="true"></i>
                        </button>
                    </form>
                @else
                    <span class="ms-check" aria-hidden="true"><i class="ti {{ $icon }}"></i></span>
                @endif

                <div class="ms-body">
                    <span class="ms-title">
                        {{ $milestone->title }}
                        {{-- عند المشرف: هذه المرحلة من خطته — تُعدَّل هناك لكل المجموعات --}}
                        @if ($editable && $milestone->stage)
                            <a href="{{ route('supervisor.plan') }}#stage-{{ $milestone->stage_id }}" class="ms-plan-tag"
                                title="تُعدَّل من خطة المراحل لكل المجموعات">من الخطة</a>
                        @endif
                    </span>
                    @if ($milestone->due_date)
                        <span class="ms-due">
                            الاستحقاق {{ $milestone->due_date->format('Y-m-d') }}
                            @if ($milestone->is_done && $milestone->done_at)
                                · أُنجزت {{ $milestone->done_at->format('Y-m-d') }}
                            @endif
                        </span>
                    @endif

                    {{-- من خطة المشرف: تعليماتها وقالبها في صفّها — لا في قسم الملفات مختلطاً بملفات الطلاب --}}
                    @if (! $editable && $milestone->stage)
                        @if ($milestone->stage->instructions)
                            <span class="ms-instructions">{{ $milestone->stage->instructions }}</span>
                        @endif
                        @if ($milestone->stage->hasTemplate())
                            @php [$tc, $tl] = $milestone->stage->templateBadge(); @endphp
                            <a href="{{ route('stages.template', $milestone->stage_id) }}" class="ms-template">
                                <span class="file-type {{ $tc }}" aria-hidden="true">{{ $tl }}</span>
                                <span>
                                    <b>قالب المشرف</b>
                                    <small>{{ $milestone->stage->template_name }}</small>
                                </span>
                                <i class="ti ti-download" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endif
                </div>

                {{-- الشارة للحالة التي تحتاج تدخّلاً وحدها: المنجز
                     يُفهم من الخطّ فوقه ولا يحتاج إعلاناً --}}
                @if ($overdue)
                    <span class="ms-flag">متأخّرة</span>
                @endif

                @if ($editable)
                    <span class="ms-actions">
                        <form action="{{ route('supervisor.milestones.destroy', ['milestone' => $milestone->id]) }}"
                            method="POST" onsubmit="return confirm('حذف «{{ $milestone->title }}»؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action--danger" title="حذف"
                                aria-label="حذف {{ $milestone->title }}">
                                <i class="ti ti-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </span>
                @endif
            </div>
        @endforeach
    @endif
</section>
