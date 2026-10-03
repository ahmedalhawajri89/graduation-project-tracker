@extends('layouts.admin.admin')
@section('title', __('تعديل المجموعة'))

@section('crumbs')
    <x-crumb :href="route('admin.groups.index')">{{ __('المجموعات') }}</x-crumb>
    <x-crumb :href="route('admin.groups.show', $project->id)">{{ $project->title }}</x-crumb>
    <x-crumb>{{ __('تعديل') }}</x-crumb>
@endsection

@section('content')

    @php
        $currentCount = $project->group->count();
        $typeMax = $project->project_type->max ?? null;
        $typeMin = $project->project_type->min ?? null;
        $overLimit = $typeMax && $currentCount >= $typeMax;
    @endphp

    {{-- الصفحة كانت لا تذكر أي مجموعة تُعدَّل إطلاقاً — تفتحها فلا تعرف
         أين أنت. عنوان المشروع صار في الترويسة. --}}
    <x-page-header title="{{ __('تعديل المجموعة') }}"
        subtitle="{{ $project->title }}">
        {{-- زر «المجموعات» حُذف: المسار في الهيدر يؤدّي وظيفته، وكان
             موجوداً أصلاً لأن المسار كان ناقصاً. --}}
        <x-slot:actions>
            <a href="{{ route('admin.groups.show', $project->id) }}" class="btn btn-outline-primary">
                <i class="ti ti-eye me-1" aria-hidden="true"></i>
                {{ __('عرض التفاصيل') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="edit-grid">

        {{-- ===== السياق: قراءة فقط ===== --}}
        <aside class="edit-context">
            <div class="ctx-card">
                <div class="ctx-head">
                    <i class="ti ti-users-group" aria-hidden="true"></i>
                    {{ __('أعضاء الفريق') }}
                    <span class="ctx-badge {{ $typeMax && $currentCount > $typeMax ? 'is-over' : '' }}">
                        {{ $currentCount }}@if ($typeMax)<small>/{{ $typeMax }}</small>@endif
                    </span>
                </div>
                {{-- كان اللوح للقراءة فقط: الصفحة تعرض «الفريق ٥ والحدّ
                     ٣» ثم لا تملك ما تُصلح به. صار كل عضو قابلاً
                     للإزالة، والقيادة قابلة للنقل. --}}
                @foreach ($project->group as $group)
                    <div class="ctx-person {{ $group->type === 'leader' ? 'is-leader' : '' }}">
                        <x-avatar :user="$group->student" class="ctx-avatar" />
                        <span class="ctx-person-body">
                            <span class="ctx-person-name">
                                {{ $group->student?->name ?? __('طالب محذوف') }}
                                @if ($group->type === 'leader')
                                    <span class="ctx-tag">{{ __('قائد') }}</span>
                                @endif
                            </span>
                            <span class="ctx-person-meta" dir="ltr">{{ $group->student?->university_id ?? '—' }}</span>
                        </span>

                        <span class="ctx-person-actions">
                            @if ($group->type !== 'leader')
                                <form action="{{ route('admin.groups.members.leader', [$project->id, $group->id]) }}"
                                    method="post">
                                    @csrf
                                    <button type="submit" class="btn-action" title="{{ __('تعيينه قائداً للفريق') }}"
                                        aria-label="{{ __('تعيينه قائداً للفريق') }}">
                                        <i class="ti ti-crown" aria-hidden="true"></i>
                                    </button>
                                </form>

                                {{-- آخر عضو لا يُزال: مشروع بلا فريق يتيم --}}
                                @if ($currentCount > 1)
                                    <button type="button" class="btn-action btn-action--danger btn-remove-member"
                                        data-bs-toggle="modal" data-bs-target="#removeMemberModal"
                                        data-action="{{ route('admin.groups.members.remove', [$project->id, $group->id]) }}"
                                        data-name="{{ $group->student?->name ?? __('طالب محذوف') }}"
                                        title="{{ __('إزالة من الفريق') }}" aria-label="{{ __('إزالة من الفريق') }}">
                                        <i class="ti ti-user-minus" aria-hidden="true"></i>
                                    </button>
                                @endif
                            @else
                                {{-- القائد يُنقل قبل أن يُزال: فريق بلا
                                     قائد لا مُخاطَب له --}}
                                <span class="btn-action is-disabled"
                                    title="{{ __('قائد الفريق — عيّن قائداً آخر أولاً لتتمكّن من إزالته') }}"
                                    aria-disabled="true">
                                    <i class="ti ti-user-minus" aria-hidden="true"></i>
                                </span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="ctx-card">
                <div class="ctx-head">
                    <i class="ti ti-user-star" aria-hidden="true"></i>
                    {{ __('المشرف الحالي') }}
                </div>
                @php
                    $sv = $project->supervisor;
                    $used = (int) ($sv->projects_count ?? 0);
                    $max = (int) $sv->max_group;
                    $loadState = match (true) {
                        $max > 0 && $used > $max => 'is-over',
                        $max > 0 && $used === $max => 'is-full',
                        default => 'is-free',
                    };
                @endphp

                {{-- كان جدولاً كامل الرؤوس لصفّ واحد — الجدول لسجلّ واحد خطأ --}}
                <div class="ctx-person">
                    <x-avatar :user="$sv" class="ctx-avatar" />
                    <span class="ctx-person-body">
                        <span class="ctx-person-name">{{ $sv->name ?: __('بلا مشرف') }}</span>
                        <span class="ctx-person-meta">{{ $sv->specialize->name ?? '—' }}</span>
                    </span>
                </div>

                {{-- العبء هو ما يُبنى عليه قرار النقل، فيتصدّر ما دونه.
                     \u200E.load-cell\u200E نفسه المستعمل في جدول المشرفين. --}}
                @if ($sv->id)
                    <a href="{{ route('admin.supervisors.groups', $sv->id) }}" class="ctx-load">
                        <span class="ctx-load-label">{{ __('عبء الإشراف هذا الفصل') }}</span>
                        <span class="load-cell {{ $loadState }}">
                            <span class="load-figure">{{ $used }}<small>/{{ $max }}</small></span>
                            <span class="load-bar">
                                <span style="width: {{ $max > 0 ? min(100, round($used / $max * 100)) : 0 }}%"></span>
                            </span>
                        </span>
                        <i class="ti ti-chevron-left ctx-load-go" aria-hidden="true"></i>
                    </a>
                @endif

                {{-- روابط لا نصوص تُنسخ يدوياً: الجوال يُطلَب من الهاتف --}}
                <dl class="ctx-facts">
                    <dt>{{ __('البريد') }}</dt>
                    <dd>
                        @if ($sv->email)
                            <a href="mailto:{{ $sv->email }}" dir="ltr">{{ $sv->email }}</a>
                        @else
                            —
                        @endif
                    </dd>
                    <dt>{{ __('الجوال') }}</dt>
                    <dd>
                        @if ($sv->phone)
                            <a href="tel:{{ $sv->phone }}" dir="ltr">{{ $sv->phone }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </div>
        </aside>

        {{-- ===== النموذج ===== --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-pencil me-2" aria-hidden="true"></i>
                    {{ __('التعديلات') }}
                </h3>
            </div>
            <div class="card-body">

                @if ($typeMax)
                    @php $isOver = $typeMax && $currentCount > $typeMax; @endphp
                    <div class="limit-note {{ $isOver ? 'is-warn' : '' }}" role="status">
                        <i class="ti {{ $isOver ? 'ti-alert-triangle' : 'ti-info-circle' }}" aria-hidden="true"></i>
                        <span>
                            @if ($typeMin)
                                {!! __('نوع «:type» يسمح بـ :min إلى :max أعضاء، والفريق الآن :count.', ['type' => e($project->project_type->name), 'min' => '<b>' . e($typeMin) . '</b>', 'max' => '<b>' . e($typeMax) . '</b>', 'count' => '<b>' . e($currentCount) . '</b>']) !!}
                            @else
                                {!! __('نوع «:type» يسمح بـ :max أعضاء، والفريق الآن :count.', ['type' => e($project->project_type->name), 'max' => '<b>' . e($typeMax) . '</b>', 'count' => '<b>' . e($currentCount) . '</b>']) !!}
                            @endif
                            {{-- التحذير يحمل مخرجه: كان يصف التجاوز ولا
                                 يقول ما يُفعل، ولم تكن الإزالة ممكنة أصلاً --}}
                            @if ($isOver)
                                {{ __('أزِل :n من الأعضاء من اللوح المجاور، أو اتركه استثناءً إدارياً.', ['n' => $currentCount - $typeMax]) }}
                            @elseif ($overLimit)
                                {{ __('الفريق مكتمل — أي إضافة تتجاوز الحد، وهي مسموحة إدارياً للحالات الاستثنائية.') }}
                            @endif
                        </span>
                    </div>
                @endif

                <form action="{{ route('admin.groups.update') }}" method="post" id="group-edit-form"
                    data-current="{{ $currentCount }}" data-max="{{ $typeMax ?? 0 }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $project->id }}">

                    <div class="mb-4">
                        <label class="form-label" for="supervisor_id">{{ __('المشرف') }}</label>
                        {{-- كان خياراً فارغاً بلا تسمية، والمشرف الحالي غير محدَّد،
                             فلا يُعرف معنى ترك الحقل فارغاً --}}
                        <select class="form-select" name="supervisor_id" id="supervisor_id">
                            <option value="">— {{ __('إبقاء المشرف الحالي (:name)', ['name' => $project->supervisor->name ?: __('بلا مشرف')]) }} —</option>
                            {{-- كانت أسماء مجرّدة: تنقل مجموعة إلى مشرف
                                 بلا أن تعرف إن كان يحمل واحدة أو ستّاً،
                                 أو إن كان قد تجاوز حدّه أصلاً --}}
                            @foreach ($supervisors as $supervisor)
                                @php
                                    $svUsed = (int) ($supervisor->projects_count ?? 0);
                                    $svMax = (int) $supervisor->max_group;
                                    $svNote = match (true) {
                                        $svMax > 0 && $svUsed > $svMax => ' — ' . __('تجاوز حدّه'),
                                        $svMax > 0 && $svUsed === $svMax => ' — ' . __('مكتمل'),
                                        default => '',
                                    };
                                @endphp
                                <option value="{{ $supervisor->id }}" @selected(old('supervisor_id') == $supervisor->id)>
                                    {{ $supervisor->name }} — {{ $svUsed }}/{{ $svMax }}{{ $svNote }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-hint">
                            {{ __('الرقم بجانب الاسم هو مجموعاته هذا الفصل من حدّه الأقصى. اتركه كما هو إن لم ترد تغيير المشرف.') }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">{{ __('إضافة أعضاء') }}</label>
                    </div>
                    @include('dashboard.student.group')

                    <div class="form-footer">
                        <button name="registerbtn" type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1" aria-hidden="true"></i>
                            {{ __('حفظ التعديلات') }}
                        </button>
                        <a href="{{ route('admin.groups.show', $project->id) }}" class="btn btn-ghost-secondary">
                            {{ __('إلغاء') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- إزالة عضو تُخرج طالباً من مشروعه ويصير بلا فريق — فعل يستحقّ
         سؤالاً، ولا يُترك لنقرة واحدة --}}
    <div class="modal fade" id="removeMemberModal" tabindex="-1" aria-labelledby="removeMemberLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeMemberLabel">
                        {!! __('إزالة :name من الفريق', ['name' => '<span class="text-danger" id="remove-member-name"></span>']) !!}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
                </div>

                <form method="POST" id="remove-member-form">
                    @csrf
                    @method('DELETE')

                    <div class="modal-body">
                        <ul class="archive-effects">
                            <li class="is-stop">
                                <i class="ti ti-circle-x" aria-hidden="true"></i>
                                {{ __('يخرج من هذا المشروع ويصير بلا فريق') }}
                            </li>
                            <li class="is-keep">
                                <i class="ti ti-circle-check" aria-hidden="true"></i>
                                {{ __('حسابه وبياناته تبقى كما هي، ويمكن ضمّه لفريق آخر') }}
                            </li>
                            <li class="is-keep">
                                <i class="ti ti-circle-check" aria-hidden="true"></i>
                                {{ __('يُسجَّل الإجراء باسمك في سجلّ التدقيق') }}
                            </li>
                        </ul>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-user-minus me-1" aria-hidden="true"></i>
                            {{ __('إزالة من الفريق') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            // نافذة إزالة عضو: المسار يحمل مُعرِّفه، فيُبنى عند كل فتح
            $('body').on('click', '.btn-remove-member', function () {
                var button = $(this);
                $('#remove-member-name').text(button.data('name'));
                $('#remove-member-form').attr('action', button.data('action'));
            });

            // تأكيد عند تجاوز الحد الأقصى لأعضاء الفريق
            (function () {
                var form = document.getElementById('group-edit-form');
                if (!form) return;

                form.addEventListener('submit', function (e) {
                    var max = parseInt(form.dataset.max, 10);
                    if (!max) return; // لا حد معرّف لهذا النوع

                    var current = parseInt(form.dataset.current, 10) || 0;
                    // الاختيارات صارت حقولاً مخفيّة لا مربّعات: نتيجة
                    // البحث تتغيّر مع كل حرف، فلا يُربط الاختيار بصفوفها
                    var selected = form.querySelectorAll('#picker-selected input[name="student_ids[]"]').length;
                    if (selected === 0) return;

                    var total = current + selected;
                    if (total > max) {
                        if (form.dataset.overOk === '1') { delete form.dataset.overOk; return; }
                        // نافذة التأكيد غير متزامنة: يُوقف الإرسال، ويُعاد بعد «متابعة»
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        var submitter = e.submitter;
                        appConfirm({
                            tone: 'warning',
                            title: @json(__('تجاوز حدّ الفريق')),
                            text: @json(__('سيصبح عدد أعضاء الفريق :total وهو أكبر من الحد الأقصى (:max).')).replace(':total', total).replace(':max', max) + '
' +
                                @json(__('هل تريد المتابعة كاستثناء إداري؟')),
                            ok: @json(__('متابعة كاستثناء')),
                        }).then(function (yes) {
                            if (!yes) return;
                            form.dataset.overOk = '1';
                            form.requestSubmit(submitter);
                        });
                    }
                });
            })();
        </script>
    @endpush

@endsection
