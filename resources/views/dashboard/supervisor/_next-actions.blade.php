{{--
    «ما يحتاجني الآن» — لوح المشرف.

    الصفحة كانت تعرض كل المجموعات بالوزن نفسه، والمشرف يفتحها ليعرف
    أيّها يحتاجه. والطلبات المعلّقة كانت زرّاً في الترويسة لا عملاً في
    متن الصفحة.
--}}

@php
    $todos = [];

    if ($pendingRequests > 0) {
        $todos[] = [
            'icon' => 'ti-inbox',
            'tone' => 'is-warn',
            'n' => $pendingRequests,
            'label' => $pendingRequests === 1 ? 'طلب إشراف بانتظار ردّك — وطلبته ينتظرون' : 'طلبات إشراف بانتظار ردّك — وطلبتها ينتظرون',
            // البطاقات بزرّيها في هذه الصفحة نفسها، تحت هذا اللوح
            'href' => '#pending-title',
        ];
    }

    // مشروع اكتمل بلا درجة: لا شيء في النظام يذكّر المشرف برصدها
    $ungraded = $groups->filter(
        fn ($p) => $p->status === 'complete' && is_null($p->grade)
    );

    if ($ungraded->count()) {
        $todos[] = [
            'icon' => 'ti-award',
            'tone' => 'is-warn',
            'n' => $ungraded->count(),
            'label' => $ungraded->count() === 1
                ? 'مشروع مكتمل بلا تقييم: ' . $ungraded->first()->title
                : 'مشاريع مكتملة بلا تقييم',
            'href' => route('supervisor.projects.show', $ungraded->first()->id),
        ];
    }

    // تسليمات تنتظر المراجعة: الفريق لا يتقدّم قبل ردّك
    $reviewItems = $groups->flatMap(fn ($p) => $p->milestones->filter(fn ($m) => $m->isSubmitted()));

    if ($reviewItems->count()) {
        $first = $reviewItems->first();
        $todos[] = [
            'icon' => 'ti-inbox',
            'tone' => 'is-brand',
            'n' => $reviewItems->count(),
            'label' => $reviewItems->count() === 1
                ? 'تسليم بانتظار مراجعتك: ' . $first->title
                : 'تسليمات مراحل بانتظار مراجعتك',
            'href' => route('supervisor.projects.show', $first->project_id) . '#milestone-' . $first->id,
        ];
    }

    // المتأخّر أولاً: هو ما يُكلِّف إن أُهمل
    $late = $groups->filter(fn ($p) => $p->milestones->contains(
        fn ($m) => $m->isLate()
    ));

    if ($late->count()) {
        $todos[] = [
            'icon' => 'ti-alert-triangle',
            'tone' => 'is-danger',
            'n' => $late->count(),
            'label' => $late->count() === 1
                ? 'مجموعة عليها مرحلة متأخّرة: ' . $late->first()->title
                : 'مجموعات عليها مراحل متأخّرة',
            'href' => route('supervisor.projects.show', $late->first()->id),
        ];
    }

    // آخر كلمة من طالب بلا ردّ منك
    $waiting = $groups->filter(function ($p) {
        $last = $p->comments->first();

        return $last && ! $last->is_supervisor;
    });

    if ($waiting->count()) {
        $todos[] = [
            'icon' => 'ti-message-dots',
            'tone' => '',
            'n' => $waiting->count(),
            'label' => 'مجموعة تنتظر ردّك في النقاش',
            'href' => route('supervisor.discussion', $waiting->first()->id),
        ];
    }

    // موعد يقترب بلا اكتمال
    $soon = $groups->filter(function ($p) {
        $d = $p->days_left;

        return $p->status !== 'complete' && ! is_null($d) && $d >= 0 && $d <= 7;
    });

    if ($soon->count()) {
        $todos[] = [
            'icon' => 'ti-alarm',
            'tone' => 'is-warn',
            'n' => $soon->count(),
            'label' => 'مجموعة موعدها النهائي خلال أسبوع',
            'href' => route('supervisor.projects.show', $soon->first()->id),
        ];
    }
@endphp

<section class="todo-panel mb-4">
    <h2 class="todo-title">
        <i class="ti ti-target-arrow" aria-hidden="true"></i>
        ما يحتاجني الآن
    </h2>

    @forelse ($todos as $todo)
        <a href="{{ $todo['href'] }}" class="todo-row {{ $todo['tone'] }}">
            <i class="ti {{ $todo['icon'] }}" aria-hidden="true"></i>
            <span class="todo-n">{{ $todo['n'] }}</span>
            <span class="todo-label">{{ $todo['label'] }}</span>
            <i class="ti ti-chevron-left todo-go" aria-hidden="true"></i>
        </a>
    @empty
        <p class="todo-clear">
            <i class="ti ti-circle-check" aria-hidden="true"></i>
            @if ($groups->isEmpty())
                لا شيء ينتظرك — ستظهر هنا مهامّ مجموعاتك حين تقبل أولها.
            @else
                لا شيء ينتظرك — مجموعاتك تسير كما ينبغي.
            @endif
        </p>
    @endforelse
</section>
