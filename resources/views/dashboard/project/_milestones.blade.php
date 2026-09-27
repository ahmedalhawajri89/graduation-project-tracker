{{--
    مراحل المشروع — الطالب والمشرف يريانها بالشكل ذاته.

    لكل مرحلة دورة تسليم: الفريق يسلّم (ملاحظة وملف)، والمشرف يعتمد أو
    يطلب تعديلاً بملاحظة، فيعدّل الفريق ويعيد التسليم. الجولات كلّها تبقى.

    @param \App\Models\Project $project
    @param bool                $editable  المشرف والمشروع غير مقفل: الدائرة
                                          زرّ تبديل، وحذف، وسطر إضافة، ومراجعة
--}}

@php
    $editable = $editable ?? false;
    $project->loadMissing('milestones.submissions.student');

    // الطالب يسلّم في مشروع جارٍ غير مؤرشف
    $isStudent = auth('student')->check();
    $canSubmit = $isStudent && in_array($project->status, ['accept', 'complete'], true) && ! $project->is_locked;

    // النموذج الذي فشل تحقّقه يُفتح من جديد لمرحلته
    $failedFor = (int) old('milestone_ref');
@endphp

<section class="dist-panel mb-4" id="milestones">
    <div class="dist-head">
        <span class="dist-head-title">
            <i class="ti ti-list-check" aria-hidden="true"></i>
            مراحل المشروع
        </span>
        @if ($project->milestones->count())
            @php $inReview = $project->milestones->filter->isSubmitted()->count(); @endphp
            <span class="dist-head-note">
                @if ($inReview)
                    <span class="ms-head-review">{{ $inReview }} بانتظار المراجعة</span> ·
                @endif
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
                $overdue = $milestone->isLate();
                $icon = match (true) {
                    $milestone->is_done => 'ti-check',
                    $milestone->isSubmitted() => 'ti-inbox',
                    $milestone->needsRevision() => 'ti-pencil',
                    $overdue => 'ti-alert-triangle',
                    default => 'ti-clock',
                };
                $latest = $milestone->submissions->first();
                $older = $milestone->submissions->slice(1);
            @endphp
            <div class="ms-row {{ $milestone->is_done ? 'is-done' : '' }} {{ $overdue ? 'is-overdue' : '' }} is-{{ $milestone->status }}"
                id="milestone-{{ $milestone->id }}">
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

                    {{-- ===== آخر تسليم وردّ المشرف عليه ===== --}}
                    @if ($latest)
                        <div class="ms-sub {{ $latest->decision ? 'is-' . $latest->decision : 'is-pending' }}">
                            <div class="ms-sub-head">
                                <x-avatar :user="$latest->student" class="ctx-avatar ms-sub-avatar" />
                                <span class="ms-sub-who">
                                    <b>{{ $latest->student->name }}</b>
                                    {{ $latest->round > 1 ? 'أعاد التسليم' : 'سلّم' }}
                                    · <time datetime="{{ $latest->created_at->toIso8601String() }}"
                                        title="{{ $latest->created_at->format('Y-m-d H:i') }}">{{ $latest->created_at->diffForHumans() }}</time>
                                </span>
                                @if ($latest->round > 1)
                                    <span class="ms-round">الجولة {{ $latest->round }}</span>
                                @endif
                            </div>

                            @if ($latest->note)
                                <p class="ms-sub-note">{{ $latest->note }}</p>
                            @endif

                            @if ($latest->hasFile())
                                @php [$fc, $fl] = $latest->fileBadge(); @endphp
                                <a href="{{ route('submissions.file', $latest->id) }}" class="ms-template ms-sub-file">
                                    <span class="file-type {{ $fc }}" aria-hidden="true">{{ $fl }}</span>
                                    <span>
                                        <b>{{ $latest->file_name }}</b>
                                        <small>ملف التسليم</small>
                                    </span>
                                    <i class="ti ti-download" aria-hidden="true"></i>
                                </a>
                            @endif

                            @if ($latest->decision === \App\Models\ProjectMilestone::REVISION)
                                <div class="ms-feedback" role="note">
                                    <i class="ti ti-message-2-exclamation" aria-hidden="true"></i>
                                    <span>
                                        <b>{{ $isStudent ? 'طلب المشرف تعديلاً' : 'طلبتَ تعديلاً' }}</b>
                                        <span class="ms-feedback-text">{{ $latest->feedback }}</span>
                                        <small>{{ $latest->reviewed_at?->diffForHumans() }}</small>
                                    </span>
                                </div>
                            @elseif ($latest->decision === \App\Models\ProjectMilestone::APPROVED)
                                <div class="ms-approved">
                                    <i class="ti ti-circle-check" aria-hidden="true"></i>
                                    اعتُمدت {{ $latest->reviewed_at?->diffForHumans() }}
                                </div>
                            @endif

                            {{-- ===== مراجعة المشرف ===== --}}
                            @if ($editable && $milestone->isSubmitted())
                                <div class="ms-review">
                                    <form action="{{ route('supervisor.milestones.review', $milestone->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ti ti-check me-1" aria-hidden="true"></i>
                                            اعتماد
                                        </button>
                                    </form>
                                    <details class="ms-revise" @if ($failedFor === $milestone->id) open @endif>
                                        <summary class="btn btn-outline-warning btn-sm">
                                            <i class="ti ti-pencil me-1" aria-hidden="true"></i>
                                            مطلوب تعديل…
                                        </summary>
                                        <form action="{{ route('supervisor.milestones.review', $milestone->id) }}" method="POST" class="ms-form">
                                            @csrf
                                            <input type="hidden" name="decision" value="revision">
                                            <input type="hidden" name="milestone_ref" value="{{ $milestone->id }}">
                                            <label class="form-label" for="feedback-{{ $milestone->id }}">ما الذي يجب تعديله؟</label>
                                            <textarea id="feedback-{{ $milestone->id }}" name="feedback" rows="3" maxlength="2000" required
                                                class="form-control {{ $failedFor === $milestone->id && $errors->has('feedback') ? 'is-invalid' : '' }}"
                                                placeholder="مثال: ينقص مخطط الكيانات، والفصل الثاني يحتاج ثلاثة مراجع إضافية…">{{ $failedFor === $milestone->id ? old('feedback') : '' }}</textarea>
                                            @if ($failedFor === $milestone->id && $errors->has('feedback'))
                                                <div class="invalid-feedback d-block">{{ $errors->first('feedback') }}</div>
                                            @endif
                                            <button type="submit" class="btn btn-warning btn-sm">إرسال طلب التعديل</button>
                                        </form>
                                    </details>
                                </div>
                            @endif

                            @if ($older->isNotEmpty())
                                <details class="ms-history">
                                    <summary>الجولات السابقة ({{ $older->count() }})</summary>
                                    <ol>
                                        @foreach ($older as $sub)
                                            <li>
                                                <span class="ms-round">الجولة {{ $sub->round }}</span>
                                                <span>
                                                    {{ $sub->student->name }} · {{ $sub->created_at->format('Y-m-d') }}
                                                    @if ($sub->hasFile())
                                                        · <a href="{{ route('submissions.file', $sub->id) }}">{{ $sub->file_name }}</a>
                                                    @endif
                                                    @if ($sub->feedback)
                                                        <em>«{{ $sub->feedback }}»</em>
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </details>
                            @endif
                        </div>
                    @endif

                    {{-- ===== تسليم الطالب ===== --}}
                    @if ($canSubmit && $milestone->canSubmit())
                        <details class="ms-submit" @if ($failedFor === $milestone->id) open @endif>
                            <summary class="btn btn-sm {{ $milestone->needsRevision() ? 'btn-warning' : 'btn-outline-primary' }}">
                                <i class="ti ti-upload me-1" aria-hidden="true"></i>
                                {{ $milestone->needsRevision() ? 'إعادة التسليم بعد التعديل' : 'تسليم المرحلة' }}
                            </summary>
                            <form action="{{ route('student.milestones.submit', $milestone->id) }}" method="POST"
                                enctype="multipart/form-data" class="ms-form">
                                @csrf
                                <input type="hidden" name="milestone_ref" value="{{ $milestone->id }}">
                                <label class="form-label" for="note-{{ $milestone->id }}">ملاحظة للمشرف</label>
                                <textarea id="note-{{ $milestone->id }}" name="note" rows="2" maxlength="2000"
                                    class="form-control {{ $failedFor === $milestone->id && $errors->has('note') ? 'is-invalid' : '' }}"
                                    placeholder="{{ $milestone->needsRevision() ? 'ما الذي عدّلتموه؟' : 'ما الذي أنجزتموه في هذه المرحلة؟' }}">{{ $failedFor === $milestone->id ? old('note') : '' }}</textarea>
                                <label class="ms-file">
                                    <i class="ti ti-paperclip" aria-hidden="true"></i>
                                    <span>إرفاق ملف (اختياري — حتى 10MB)</span>
                                    <input type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg"
                                        onchange="this.previousElementSibling.textContent = this.files[0] ? this.files[0].name : 'إرفاق ملف (اختياري — حتى 10MB)'">
                                </label>
                                @if ($failedFor === $milestone->id)
                                    @foreach (['note', 'file'] as $field)
                                        @if ($errors->has($field))
                                            <div class="invalid-feedback d-block">{{ $errors->first($field) }}</div>
                                        @endif
                                    @endforeach
                                @endif
                                <button type="submit" class="btn btn-primary btn-sm" data-loading-text="جارٍ التسليم…">
                                    <i class="ti ti-send me-1" aria-hidden="true"></i>
                                    تسليم
                                </button>
                            </form>
                        </details>
                    @endif
                </div>

                {{-- الشارة للحالة التي تحتاج انتباهاً وحدها: المنجز
                     يُفهم من الخطّ فوقه ولا يحتاج إعلاناً --}}
                @if ($milestone->isSubmitted())
                    <span class="ms-flag is-review">{{ $editable ? 'بانتظار مراجعتك' : 'بانتظار المراجعة' }}</span>
                @elseif ($milestone->needsRevision())
                    <span class="ms-flag is-revision">مطلوب تعديل</span>
                @elseif ($overdue)
                    <span class="ms-flag">متأخّرة</span>
                @endif

                @if ($editable)
                    <span class="ms-actions">
                        <form action="{{ route('supervisor.milestones.destroy', ['milestone' => $milestone->id]) }}"
                            method="POST" onsubmit="return confirm({{ Js::from('حذف «' . $milestone->title . '»؟' . ($milestone->submissions->isNotEmpty() ? ' تُحذف معها تسليماتها.' : '')) }})">
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
