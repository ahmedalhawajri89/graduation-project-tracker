@extends('layouts.admin.admin')
@section('title', 'الصفحة الرئيسية')

@section('content')
    @php
        $pending = (int) ($project_status['request'] ?? 0);
        $unreadMsgs = \App\Models\Contact::where('is_read', 0)->count();

        // مصدر واحد للحالات: config/statuses.php
        $statusOrder = config('statuses.order');
        $statusMap = config('statuses.map');
        $statusTotal = 0;
        $statusRows = [];
        foreach ($statusOrder as $st) {
            $count = (int) ($project_status[$st] ?? 0);
            $statusTotal += $count;
            $statusRows[] = [
                'key' => $st,
                'label' => __('site.' . $st),
                'count' => $count,
                'hex' => $statusMap[$st]['hex'],
            ];
        }

        $specMax = max(1, (int) $specializes->max('students_count'));
        $typeMax = max(1, (int) $project_types->max('projects_count'));
    @endphp

    {{-- الفصل الدراسي انتقل إلى السايدبار — كان يظهر هنا وفي شريحة
         الهيدر معاً، أي مرتين في كل صفحة. --}}
    <x-page-header title="لوحة التحكم" subtitle="نظرة عامة على الفصل الحالي">
        <x-slot:actions>
            <a href="{{ route('site.home') }}" class="btn btn-outline-primary" target="_blank">
                <i class="ti ti-world me-1"></i>
                عرض الموقع
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ لوح القيادة ═══
         كان قسمين منفصلين: «يحتاج إجراءً» و«حالة الفصل». دُمجا لأن
         الإجراء ناتج عن الحالة لا منفصل عنها — والطلبات المعلّقة جزء
         من توزيع الحالات نفسه. ولوح داكن واحد وسط الفاتح يصنع البؤرة
         التي كانت تنقص الصفحة. --}}
    @php
        $todos = [];
        if ($pending > 0) {
            // يقود إلى الطلبات المعلّقة نفسها لا إلى قائمة تستثنيها
            $todos[] = ['n' => $pending, 'label' => 'طلب بانتظار مراجعة المشرف',
                        'href' => route('admin.groups.index', ['status' => 'request']), 'icon' => 'ti-clock-hour-4'];
        }
        if ($not_has_group > 0) {
            // يقود إلى الطلاب بلا فريق أنفسهم لا إلى قائمة الـ٥٠٠ كاملة
            $todos[] = ['n' => $not_has_group, 'label' => 'طالب لم ينضمّ إلى فريق',
                        'href' => route('admin.students.index', ['group' => 'none']), 'icon' => 'ti-user-exclamation'];
        }
        if ($unreadMsgs > 0) {
            $todos[] = ['n' => $unreadMsgs, 'label' => 'رسالة لم تُقرأ',
                        'href' => route('admin.contact.index'), 'icon' => 'ti-mail'];
        }

        $barLabel = 'توزيع حالات المشاريع: ' . collect($statusRows)
            ->map(fn ($r) => $r['label'] . ' ' . $r['count'])
            ->implode('، ');
    @endphp

    <section class="cmd-panel mb-4">
        <div class="cmd-context">
            <i class="ti ti-calendar-stats" aria-hidden="true"></i>
            {{ $semester->label }}
        </div>

        <div class="cmd-main">
            {{-- الأرقام الحيوية --}}
            <div class="cmd-vitals">
                <div class="vital">
                    <span class="vital-n">{{ $student_count }}<x-trend :value="$trends['students'] ?? null" /></span>
                    <span class="vital-l">طالب مسجَّل</span>
                    <span class="vital-s">{{ $has_group }} في فرق هذا الفصل</span>
                </div>
                <div class="vital">
                    <span class="vital-n">{{ $supervisor_count }}<x-trend :value="$trends['supervisors'] ?? null" /></span>
                    <span class="vital-l">مشرف أكاديمي</span>
                    <span class="vital-s">&nbsp;</span>
                </div>
                <div class="vital">
                    <span class="vital-n">{{ $project_count }}<x-trend :value="$trends['groups'] ?? null" /></span>
                    <span class="vital-l">مجموعة نشطة</span>
                    <span class="vital-s">من {{ $statusTotal }} مشروعاً مسجَّلاً</span>
                </div>
            </div>

            {{-- ما يحتاج إجراءً — عمود في ذيل الصفّ يفصله خط --}}
            <div class="cmd-actions">
                @forelse ($todos as $t)
                    <a href="{{ $t['href'] }}" class="cmd-action">
                        <i class="ti {{ $t['icon'] }}" aria-hidden="true"></i>
                        <span class="cmd-action-n">{{ $t['n'] }}</span>
                        <span class="cmd-action-l">{{ $t['label'] }}</span>
                        <i class="ti ti-chevron-left cmd-action-go" aria-hidden="true"></i>
                    </a>
                @empty
                    <p class="cmd-clear">
                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                        لا شيء ينتظر إجراءً
                    </p>
                @endforelse
            </div>
        </div>

        @if ($statusTotal > 0)
            <div class="cmd-status">
                <div class="cmd-status-head">
                    <span>توزيع حالات المشاريع</span>
                    <b>{{ $statusTotal }} مشروعاً</b>
                </div>

                {{-- شريط مكدّس بـ CSS، بألوان hex_dark لأنه على لوح داكن --}}
                <div class="stack-bar" role="img" aria-label="{{ $barLabel }}">
                    @foreach ($statusRows as $r)
                        @if ($r['count'] > 0)
                            <span class="stack-seg"
                                style="width: {{ round($r['count'] / $statusTotal * 100, 2) }}%; background: {{ $r['hex'] }}"
                                title="{{ $r['label'] }}: {{ $r['count'] }}"></span>
                        @endif
                    @endforeach
                </div>

                <ul class="stack-key">
                    @foreach ($statusRows as $r)
                        <li>
                            <span class="key-dot" style="background: {{ $r['hex'] }}"></span>
                            {{ $r['label'] }}
                            <b>{{ $r['count'] }}</b>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    {{-- ═══ متابعة الفرق ═══
         الأرقام فوق تقول كم فريقاً، لا أيّها متعثّر. أربعة مؤشّرات من مراحل
         الفرق وتسليماتها وأدوارها، كلٌّ يفتح قائمة الفرق المعنيّة نفسها. --}}
    <section class="dash-block mb-4">
        <h2 class="dash-block-title">
            <i class="ti ti-heartbeat" aria-hidden="true"></i>
            متابعة الفرق
        </h2>

        <div class="health-grid">
            @foreach (\App\Support\TeamHealth::issues() as $key => [$title, $text, $icon])
                @php $n = $health[$key] ?? 0; @endphp
                <a href="{{ route('admin.groups.index', ['issue' => $key]) }}"
                    class="health-cell {{ $n > 0 ? 'is-alert' : 'is-clear' }}">
                    <span class="health-top">
                        <span class="health-icon"><i class="ti {{ $icon }}" aria-hidden="true"></i></span>
                        <span class="health-n">{{ $n }}</span>
                    </span>
                    <span class="health-title">{{ $title }}</span>
                    <span class="health-text">{{ $n > 0 ? $text : 'لا شيء هنا — جيد.' }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ═══ ٣) الاتجاه ═══
         هنا يستحق الرسم مكانه: الرقم يقول أين أنت، والخط يقول إلى أين
         تتجه — والثاني هو ما يُتخذ عليه قرار. مبني SVG مباشرةً، بلا
         مكتبة ولا CDN، فيبقى عمل الصفحة بلا إنترنت قائماً. --}}
    <section class="dash-block mb-4">
        <h2 class="dash-block-title">
            <i class="ti ti-trending-up" aria-hidden="true"></i>
            الاتجاه خلال الفصل
        </h2>

        <x-trend-chart :series="$trendSeries" />
    </section>

    {{-- ═══ ٤) التوزيع ═══
         الرسم العمودي وجدول الأنواع كانا يقولان الشيء نفسه بطريقتين.
         الجدول بأشرطة أدقّ (رقم ونسبة معاً) وأكثف (بلا محاور وشبكة). --}}
    <section class="dash-block mb-4">
        <h2 class="dash-block-title">
            <i class="ti ti-layout-distribute-horizontal" aria-hidden="true"></i>
            التوزيع
        </h2>

        <div class="dist-grid">
            <div class="dist-panel">
                <div class="dist-head">
                    <span>الطلاب حسب التخصص</span>
                    <a href="{{ route('admin.specialize.index') }}">إدارة التخصصات</a>
                </div>
                @forelse ($specializes->sortByDesc('students_count') as $spec)
                    <div class="dist-row {{ $spec->students_count === 0 ? 'is-zero' : '' }}">
                        <span class="dist-fill" style="width: {{ round($spec->students_count / $specMax * 100) }}%"></span>
                        <span class="dist-name" title="{{ $spec->name }}">
                            {{ $spec->name }}@if ($spec->isArchived())<small class="dist-archived">موقوف</small>@endif
                        </span>
                        <span class="dist-n">{{ $spec->students_count }}</span>
                    </div>
                @empty
                    <p class="dist-empty">لم تُضَف تخصصات بعد.</p>
                @endforelse

                @if ($specializes->count())
                    <div class="dist-foot">
                        <span>{{ $specializes->count() }} تخصصاً</span>
                        <span>المجموع <b>{{ $specializes->sum('students_count') }}</b> طالباً</span>
                    </div>
                @endif
            </div>

            <div class="dist-panel">
                <div class="dist-head">
                    <span>المشاريع حسب النوع</span>
                    <a href="{{ route('admin.groups.index') }}">عرض المجموعات</a>
                </div>
                @forelse ($project_types->sortByDesc('projects_count') as $type)
                    <div class="dist-row {{ $type->projects_count === 0 ? 'is-zero' : '' }}">
                        <span class="dist-fill" style="width: {{ round($type->projects_count / $typeMax * 100) }}%"></span>
                        <span class="dist-name" title="{{ $type->name }}">{{ $type->name }}</span>
                        <span class="dist-n">{{ $type->projects_count }}</span>
                    </div>
                @empty
                    <p class="dist-empty">لم تُضَف أنواع مشاريع بعد.</p>
                @endforelse

                @if ($project_types->count())
                    <div class="dist-foot">
                        <span>{{ $project_types->count() }} أنواع</span>
                        <span>المجموع <b>{{ $project_types->sum('projects_count') }}</b> مشروعاً</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══ ٥) آخر النشاط ═══ --}}
    <section class="dash-block">
        <h2 class="dash-block-title">
            <i class="ti ti-activity" aria-hidden="true"></i>
            آخر النشاط
        </h2>

        <div class="dist-grid">
            <div class="dist-panel">
                <div class="dist-head">
                    <span>أحدث طلبات المشاريع</span>
                    <a href="{{ route('admin.groups.index') }}">الكل</a>
                </div>
                @forelse ($recent_projects as $project)
                    <a href="{{ route('admin.groups.show', $project->id) }}" class="feed-row">
                        <span class="feed-body">
                            <span class="feed-title">{{ $project->title }}</span>
                            <span class="feed-meta">
                                {{ $project->supervisor->name ?: 'بلا مشرف' }}
                                · {{ $project->created_at?->diffForHumans() }}
                            </span>
                        </span>
                        <x-status-badge :status="$project->status" />
                        <i class="ti ti-chevron-left feed-go" aria-hidden="true"></i>
                    </a>
                @empty
                    <p class="dist-empty">لا توجد طلبات في هذا الفصل بعد.</p>
                @endforelse
            </div>

            <div class="dist-panel">
                <div class="dist-head">
                    <span>آخر رسائل الاستفسار</span>
                    <a href="{{ route('admin.contact.index') }}">الكل</a>
                </div>
                @forelse ($recent_messages as $message)
                    <a href="{{ route('admin.contact.index') }}" class="feed-row">
                        <span class="feed-body">
                            <span class="feed-title">{{ $message->subject }}</span>
                            <span class="feed-meta">
                                {{ $message->name }} · {{ $message->created_at?->diffForHumans() }}
                            </span>
                        </span>
                        @if (! $message->is_read)
                            <span class="feed-new">جديد</span>
                        @endif
                        <i class="ti ti-chevron-left feed-go" aria-hidden="true"></i>
                    </a>
                @empty
                    <p class="dist-empty">لا توجد رسائل بعد.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
