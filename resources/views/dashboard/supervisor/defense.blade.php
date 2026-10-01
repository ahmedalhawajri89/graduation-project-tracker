@extends('layouts.admin.admin')
@section('title', __('مناقشة :title', ['title' => $project->title]))

@section('crumbs')
    <x-crumb :href="route('supervisor.defenses.index')">{{ __('مناقشاتي') }}</x-crumb>
    <x-crumb>{{ $project->title }}</x-crumb>
@endsection

@section('content')

    @php
        $fmt = fn ($g) => is_null($g) ? '—' : rtrim(rtrim(number_format((float) $g, 2, '.', ''), '0'), '.');
        $isOwn = (int) $project->supervisor_id === (int) auth('supervisor')->id();
        $msLabel = ['open' => __('مفتوحة'), 'submitted' => __('سُلّمت'), 'revision' => __('مطلوب تعديل'), 'approved' => __('اعتُمدت')];
        $done = $defense->status === 'done';
        $gradedCount = $defense->members->whereNotNull('grade')->count();
        // الوزن يُعرض حين تضبطه الإدارة؛ وإلا فالنهائية متوسط متساوٍ
        $weighted = \App\Support\DefenseGrading::supervisorWeight() !== null;
        $weights = \App\Support\DefenseGrading::weights($defense->members);
        $pct = fn ($w) => rtrim(rtrim(number_format($w, 1, '.', ''), '0'), '.') . '%';
        // العرض التقديمي يُعرض وحده أعلى الصفحة، لا بين ملفات المشروع
        $slides = $project->files->first(fn ($f) => $f->isPresentation());
        $otherFiles = $project->files->reject(fn ($f) => $f->isPresentation());
        // DefenseNotifier::when عربي دائماً (للإشعارات)؛ هنا نص الصفحة بلغة العارض
        $when = $defense->starts_at->translatedFormat('l j F Y')
            . ' · ' . \App\Support\DefenseNotifier::range($defense)
            . ' · ' . $defense->place_label;
    @endphp

    <x-page-header title="{{ $project->title }}"
        subtitle="{{ __('مناقشة') }} · {{ $when }}">
        <x-slot:actions>
            @if ($defense->needsLink() && $defense->meeting_url)
                <a href="{{ $defense->meeting_url }}" target="_blank" rel="noopener" class="btn {{ $defense->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $defense->isJoinable() ? __('انضم الآن') : __('رابط الاجتماع') }}
                </a>
            @endif
            <a href="{{ $defense->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="ti ti-brand-google me-1" aria-hidden="true"></i>{{ __('تقويم Google') }}
            </a>
            <a href="{{ route('defenses.ics', $defense->id) }}" class="btn btn-outline-secondary" title="{{ __('Outlook وتقويم الجوال') }}">
                <i class="ti ti-calendar-down me-1" aria-hidden="true"></i>{{ __('ملف التقويم') }}
            </a>
            <a href="{{ route('defenses.minutes', $defense->id) }}" class="btn btn-outline-secondary" title="{{ $done ? __('المحضر المكتمل — طباعة أو PDF') : __('مسودة المحضر — تُطبع للتوقيع يوم المناقشة') }}">
                <i class="ti ti-file-certificate me-1" aria-hidden="true"></i>{{ __('المحضر') }}
            </a>
            @if ($isOwn)
                <a href="{{ route('supervisor.projects.show', $project->id) }}" class="btn btn-outline-secondary">{{ __('صفحة المشروع') }}</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="dsv-grid">
        <div class="dsv-main">

            {{-- ═══ العرض التقديمي ═══ --}}
            @if ($slides)
                <a href="{{ route('files.download', $slides->id) }}" class="dsv-slides is-ok">
                    <span class="dsv-slides-icon" aria-hidden="true"><i class="ti ti-presentation-analytics"></i></span>
                    <span class="dsv-slides-body">
                        <b>{{ __('العرض التقديمي') }}</b>
                        <small>{{ strtoupper(pathinfo($slides->path, PATHINFO_EXTENSION)) }} · {{ $slides->human_size }} · {{ $slides->uploader?->name ? __('رفعه :name', ['name' => $slides->uploader->name]) : __('رفعه الفريق') }} {{ $slides->created_at?->diffForHumans() }}</small>
                    </span>
                    <span class="btn btn-primary btn-sm"><i class="ti ti-download me-1" aria-hidden="true"></i>{{ __('تنزيل') }}</span>
                </a>
            @elseif (! $done)
                <div class="dsv-slides">
                    <span class="dsv-slides-icon" aria-hidden="true"><i class="ti ti-presentation"></i></span>
                    <span class="dsv-slides-body">
                        <b>{{ __('العرض التقديمي') }}</b>
                        <small>{{ __('لم يرفعه الفريق بعد — يصلك إشعار حين يُرفع.') }}</small>
                    </span>
                </div>
            @endif

            {{-- ═══ الدرجة ═══ --}}
            <section class="dsv-card dsv-grade" id="grade">
                <header class="dsv-card-head">
                    <span><i class="ti ti-award" aria-hidden="true"></i> {{ __('درجة اللجنة') }}</span>
                    <span class="dsv-progress">{{ __('رصد :n من :total', ['n' => $gradedCount, 'total' => $defense->members->count()]) }}</span>
                </header>

                @if ($done)
                    <div class="dsv-final">
                        <b>{{ $fmt($project->grade) }}</b>
                        <span>
                            <strong>{{ __('الدرجة النهائية') }} — {{ $weighted ? __('بأوزان أعضاء اللجنة') : __('متوسط درجات اللجنة') }}</strong>
                            <em>{{ $project->grade_label }} · {{ $project->isGradeLocked() ? __('معتمدة') : __('لم تُعتمد بعد') . ' — ' . ($isOwn ? __('اعتمدها من صفحة المشروع') : __('يعتمدها المشرف')) }}</em>
                        </span>
                    </div>
                @endif

                {{-- اللجنة: درجة الزميل تظهر لمن رصد درجته فقط --}}
                <ul class="dsv-members">
                    @foreach ($defense->members as $m)
                        <li class="{{ $m->id === $mine->id ? 'is-me' : '' }}">
                            <x-avatar :user="$m->supervisor" class="cell-avatar dsv-av" />
                            <span class="dsv-member-body">
                                <b>{{ $m->supervisor->name }} @if ($m->id === $mine->id)<em>{{ __('أنت') }}</em>@endif</b>
                                <small>{{ $defense->roleOf($m) }}@if ($weighted) · {{ __('وزنه :pct', ['pct' => $pct($weights[$m->id])]) }}@endif</small>
                            </span>
                            @if (is_null($m->grade))
                                <span class="dsv-state is-wait">{{ __('لم يرصد') }}</span>
                            @elseif ($m->id === $mine->id || $revealed)
                                <span class="dsv-state is-done">{{ $fmt($m->grade) }}</span>
                            @else
                                <span class="dsv-state is-done" title="{{ __('تظهر بعد أن ترصد درجتك') }}"><i class="ti ti-check" aria-hidden="true"></i> {{ __('رصد') }}</span>
                            @endif
                        </li>
                        @if (filled($m->comments) && ($m->id === $mine->id || $revealed))
                            <li class="dsv-comment"><i class="ti ti-quote" aria-hidden="true"></i>{{ $m->comments }}</li>
                        @endif
                    @endforeach
                </ul>

                @if ($blocker && is_null($mine->grade))
                    <p class="dsv-blocker"><i class="ti ti-clock" aria-hidden="true"></i> {{ $blocker }}</p>
                @elseif (! $blocker)
                    <form action="{{ route('supervisor.defenses.grade', $defense->id) }}" method="post" class="dsv-form">
                        @csrf
                        <div class="dsv-form-score">
                            <label class="form-label required" for="dsv-grade">{{ __('درجتك من 100') }}</label>
                            <input type="number" id="dsv-grade" name="grade" min="0" max="100" step="0.5" required
                                class="form-control @error('grade') is-invalid @enderror" value="{{ old('grade', $mine->grade) }}">
                            @error('grade')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="dsv-form-note">
                            <label class="form-label" for="dsv-comments">{{ __('ملاحظاتك') }} <small class="text-secondary">{{ __('(تصل للفريق مع الدرجة النهائية)') }}</small></label>
                            <textarea id="dsv-comments" name="comments" rows="2" maxlength="2000" class="form-control"
                                placeholder="{{ __('ما أحسنه الفريق في العرض والأسئلة، وما يُحسَّن') }}">{{ old('comments', $mine->comments) }}</textarea>
                        </div>
                        <div class="dsv-form-foot">
                            <span class="text-secondary small">
                                <i class="ti ti-eye-off" aria-hidden="true"></i>
                                {{ __('تقييم مستقلّ — لا ترى درجات بقية اللجنة قبل أن ترصد درجتك.') }} {{ $weighted ? __('النهائية بأوزان الأعضاء.') : __('النهائية متوسط درجات الأعضاء.') }}
                            </span>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1" aria-hidden="true"></i>{{ is_null($mine->grade) ? __('رصد درجتي') : __('تحديث درجتي') }}
                            </button>
                        </div>
                    </form>
                @endif
            </section>

            {{-- ═══ ملف المشروع للتحضير ═══ --}}
            <section class="dsv-card">
                <header class="dsv-card-head"><span><i class="ti ti-file-description" aria-hidden="true"></i> {{ __('عن المشروع') }}</span>
                    <span class="dsv-progress">{{ $project->project_type->name ?? '' }}</span></header>
                <p class="dsv-desc">{{ $project->description ?: __('لا وصف.') }}</p>
            </section>

            <section class="dsv-card">
                <header class="dsv-card-head"><span><i class="ti ti-list-check" aria-hidden="true"></i> {{ __('المراحل') }}</span>
                    <span class="dsv-progress">{{ __(':done من :total منجزة', ['done' => $project->milestones->where('is_done', true)->count(), 'total' => $project->milestones->count()]) }}</span></header>
                <ol class="dsv-ms">
                    @forelse ($project->milestones as $ms)
                        <li class="{{ $ms->is_done ? 'is-done' : '' }}">
                            <i class="ti {{ $ms->is_done ? 'ti-circle-check' : 'ti-circle-dashed' }}" aria-hidden="true"></i>
                            <span>{{ $ms->title }}</span>
                            <small>{{ $msLabel[$ms->status] ?? $ms->status }}@if ($ms->due_date) · {{ $ms->due_date->format('Y-m-d') }}@endif</small>
                        </li>
                    @empty
                        <li class="text-secondary">{{ __('لا مراحل.') }}</li>
                    @endforelse
                </ol>
            </section>

            <section class="dsv-card">
                <header class="dsv-card-head"><span><i class="ti ti-folder" aria-hidden="true"></i> {{ __('الملفات') }}</span>
                    <span class="dsv-progress">{{ $otherFiles->count() }}</span></header>
                <div class="dsv-files">
                    @forelse ($otherFiles->sortByDesc('created_at') as $file)
                        <a href="{{ route('files.download', $file->id) }}" class="dsv-file">
                            <span class="dsv-file-ext">{{ strtoupper(pathinfo($file->path, PATHINFO_EXTENSION)) ?: __('ملف') }}</span>
                            <span class="dsv-file-body"><b>{{ $file->title }}</b><small>{{ $file->created_at?->format('Y-m-d') }} · {{ $file->human_size }}</small></span>
                            <i class="ti ti-download" aria-hidden="true"></i>
                        </a>
                    @empty
                        <p class="text-secondary m-0">{{ __('لم يرفع الفريق ملفات.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="dsv-side">
            <section class="dsv-card">
                <header class="dsv-card-head"><span><i class="ti ti-calendar-event" aria-hidden="true"></i> {{ __('الموعد') }}</span></header>
                <dl class="dsv-facts">
                    <div><dt>{{ __('التاريخ') }}</dt><dd>{{ $defense->starts_at->translatedFormat('l j F Y') }}</dd></div>
                    <div><dt>{{ __('الوقت') }}</dt><dd dir="ltr">{{ $defense->starts_at->format('H:i') }}–{{ $defense->endsAt()->format('H:i') }}</dd></div>
                    <div><dt>{{ __('المكان') }}</dt><dd><i class="ti {{ $defense->mode_icon }}" aria-hidden="true"></i> {{ $defense->place_label }}</dd></div>
                    @if ($defense->room?->location)
                        <div><dt>{{ __('القاعة') }}</dt><dd>{{ $defense->room->location }}</dd></div>
                    @endif
                </dl>
                @if ($defense->notes)
                    <p class="dsv-notes"><i class="ti ti-info-circle" aria-hidden="true"></i> {{ $defense->notes }}</p>
                @endif
            </section>

            <section class="dsv-card">
                <header class="dsv-card-head"><span><i class="ti ti-users" aria-hidden="true"></i> {{ __('الفريق') }}</span>
                    <span class="dsv-progress">{{ __('المشرف :name', ['name' => $project->supervisor->name]) }}</span></header>
                <ul class="dsv-team">
                    @foreach ($project->group->sortBy(fn ($g) => $g->type === 'leader' ? 0 : 1) as $g)
                        <li>
                            <x-avatar :user="$g->student" class="cell-avatar dsv-av" />
                            <span>
                                <b>{{ $g->student?->name }} @if ($g->type === 'leader')<em>{{ __('قائد') }}</em>@endif</b>
                                <small>{{ $g->roles->pluck('label')->implode(__('، ')) ?: ($g->responsibility ?: __('بلا دور معلن')) }}</small>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>

@endsection
