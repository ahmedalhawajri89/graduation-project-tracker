{{--
    «ماذا عليّ الآن».

    الصفحة كانت تجيب «ما حالة مشروعي؟» وتدفن السؤال الذي يفتحها
    الطالب من أجله. مرحلة متأخّرة، أو موعد يقترب، أو ردّ من المشرف
    بلا قراءة — كلّها كانت مبعثرة في بطاقات متتالية يُمرَّر عليها.

    كل سطر رابط إلى موضعه في الصفحة، فالإجابة والعلاج في نقرة واحدة.
--}}

@php
    $todos = [];

    // طلب تعديل من المشرف: أعلى من المتأخّر — فيه ما يجب فعله بالضبط
    $revisions = $project->milestones->filter(fn ($m) => $m->needsRevision());

    if ($revisions->count()) {
        $todos[] = [
            'icon' => 'ti-pencil',
            'tone' => 'is-warn',
            'n' => $revisions->count(),
            'label' => $revisions->count() === 1
                ? 'مطلوب تعديل في: ' . $revisions->first()->title
                : 'مراحل أعادها المشرف بطلب تعديل',
            'href' => '#milestone-' . $revisions->first()->id,
        ];
    }

    // المتأخّر أولاً: هو ما يُكلِّف إن أُهمل
    // المعادة بطلب تعديل في سطرها أعلاه — لا تُعدّ مرّتين
    $overdue = $project->milestones->filter(
        fn ($m) => $m->isLate() && ! $m->needsRevision()
    );

    if ($overdue->count()) {
        $todos[] = [
            'icon' => 'ti-alert-triangle',
            'tone' => 'is-danger',
            'n' => $overdue->count(),
            'label' => $overdue->count() === 1
                ? 'مرحلة متأخّرة: ' . $overdue->first()->title
                : 'مراحل فات موعد استحقاقها',
            'href' => '#milestones',
        ];
    }

    $daysLeft = $project->days_left;

    if (! is_null($daysLeft) && $daysLeft >= 0 && $daysLeft <= 7) {
        $todos[] = [
            'icon' => 'ti-alarm',
            'tone' => 'is-warn',
            'n' => $daysLeft,
            'label' => $daysLeft === 0 ? 'الموعد النهائي اليوم' : 'يوماً حتى الموعد النهائي',
            'href' => '#milestones',
        ];
    }

    // رسائل لم تقرأها — النقاش صار تبويباً مستقلّاً
    $unreadMsgs = \App\Support\Discussion::unreadFor(auth('student')->user(), [$project->id])[$project->id] ?? 0;

    if ($unreadMsgs) {
        $todos[] = [
            'icon' => 'ti-message-dots',
            'tone' => '',
            'n' => $unreadMsgs,
            'label' => $unreadMsgs === 1 ? 'رسالة جديدة في النقاش' : 'رسائل جديدة في النقاش',
            'href' => route('student.discussion'),
        ];
    }

    if ($project->status === 'request') {
        $todos[] = [
            'icon' => 'ti-clock-hour-4',
            'tone' => '',
            'n' => null,
            'label' => 'طلبك بانتظار مراجعة المشرف',
            'href' => '#project-facts',
        ];
    }

    if ($project->status === 'accept' && $project->files->count() === 0) {
        $todos[] = [
            'icon' => 'ti-file-upload',
            'tone' => '',
            'n' => null,
            'label' => 'لم تُرفع أي ملفات للمشروع بعد',
            'href' => '#files',
        ];
    }
@endphp

<section class="todo-panel mb-4">
    <h2 class="todo-title">
        <i class="ti ti-target-arrow" aria-hidden="true"></i>
        ماذا عليّ الآن
    </h2>

    @forelse ($todos as $todo)
        <a href="{{ $todo['href'] }}" class="todo-row {{ $todo['tone'] }}">
            <i class="ti {{ $todo['icon'] }}" aria-hidden="true"></i>
            @if (! is_null($todo['n']))
                <span class="todo-n">{{ $todo['n'] }}</span>
            @endif
            <span class="todo-label">{{ $todo['label'] }}</span>
            <i class="ti ti-chevron-left todo-go" aria-hidden="true"></i>
        </a>
    @empty
        {{-- لوح فارغ يطمئن، لا فراغ صامت --}}
        <p class="todo-clear">
            <i class="ti ti-circle-check" aria-hidden="true"></i>
            لا شيء ينتظرك — المشروع يسير كما ينبغي.
        </p>
    @endforelse
</section>
