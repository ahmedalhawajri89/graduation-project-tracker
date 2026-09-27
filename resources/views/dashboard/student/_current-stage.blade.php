{{--
    المرحلة الحالية — ما يعمل عليه الفريق الآن، بحالته وما عليه فيها.

    «مطلوب تعديل» أولاً: فيها ما يجب فعله بالضبط. ثم أول مرحلة لم تُنجز.
    زرّ التسليم يقفز إلى صفّها في قائمة المراحل ويفتح نموذجه — لا نموذج ثانٍ.

    @param \App\Models\Project $project  بـ milestones.submissions و stage
--}}

@php
    $milestones = $project->milestones;
    $current = $milestones->first(fn ($m) => $m->needsRevision())
        ?? $milestones->first(fn ($m) => ! $m->is_done);
    $canSubmit = ! $project->is_locked;

    if ($current) {
        $latest = $current->submissions->first();
        $days = $current->due_date ? (int) today()->diffInDays($current->due_date, false) : null;
        $state = match (true) {
            $current->needsRevision() => 'is-revision',
            $current->isSubmitted() => 'is-submitted',
            $current->isLate() => 'is-late',
            default => 'is-open',
        };
        $label = [
            'is-revision' => 'مطلوب تعديل',
            'is-submitted' => 'بانتظار مراجعة المشرف',
            'is-late' => 'متأخّرة',
            'is-open' => 'المرحلة الحالية',
        ][$state];
    }
@endphp

@if ($milestones->isEmpty())
    <section class="spotlight is-empty mb-4">
        <span class="spotlight-icon" aria-hidden="true"><i class="ti ti-route"></i></span>
        <div class="spotlight-body">
            <span class="spotlight-kicker">مراحل المشروع</span>
            <h2>لم يضع مشرفك المراحل بعد</h2>
            <p>ستظهر هنا المرحلة التي تعملون عليها بموعدها وتعليماتها. ريثما تصل، ارفعوا ملفات المقترح، أو اسألوا المشرف عن الخطة.</p>
            <div class="spotlight-actions">
                <a href="{{ route('student.discussion') }}" class="btn btn-primary">
                    <i class="ti ti-messages me-1" aria-hidden="true"></i>
                    اسأل المشرف
                </a>
            </div>
        </div>
    </section>
@elseif (! $current)
    <section class="spotlight is-done mb-4">
        <span class="spotlight-icon" aria-hidden="true"><i class="ti ti-confetti"></i></span>
        <div class="spotlight-body">
            <span class="spotlight-kicker">مراحل المشروع</span>
            <h2>أنجزتم المراحل كلّها</h2>
            <p>{{ $project->status === 'complete' ? 'اكتمل المشروع — التقييم أدناه حين يُرصد.' : 'بقي تقييم المشرف. تابعوا النقاش لأي ملاحظة أخيرة.' }}</p>
        </div>
    </section>
@else
    <section class="spotlight {{ $state }} mb-4" aria-labelledby="spotlight-title">
        <span class="spotlight-icon" aria-hidden="true">
            <i class="ti {{ ['is-revision' => 'ti-pencil', 'is-submitted' => 'ti-inbox', 'is-late' => 'ti-alert-triangle', 'is-open' => 'ti-flag'][$state] }}"></i>
        </span>

        <div class="spotlight-body">
            <span class="spotlight-kicker">{{ $label }}</span>
            <h2 id="spotlight-title">{{ $current->title }}</h2>

            <div class="spotlight-meta">
                @if (! is_null($days))
                    <span class="{{ $days < 0 && ! $current->isSubmitted() ? 'is-late' : ($days <= 3 ? 'is-warn' : '') }}">
                        <i class="ti ti-calendar-due" aria-hidden="true"></i>
                        @if ($days < 0)
                            فات موعدها منذ {{ abs($days) }} {{ abs($days) === 1 ? 'يوم' : 'أيام' }}
                        @elseif ($days === 0)
                            موعدها اليوم
                        @elseif ($days === 1)
                            موعدها غداً
                        @else
                            بعد {{ $days }} أيام · {{ $current->due_date->translatedFormat('j F') }}
                        @endif
                    </span>
                @endif
                @if ($latest && $latest->round > 1)
                    <span><i class="ti ti-repeat" aria-hidden="true"></i> الجولة {{ $latest->round }}</span>
                @endif
            </div>

            @if ($current->needsRevision() && $latest?->feedback)
                <div class="spotlight-feedback">
                    <b>ملاحظة المشرف</b>
                    <span>{{ $latest->feedback }}</span>
                </div>
            @elseif ($current->isSubmitted())
                <p class="spotlight-note">
                    سلّمها {{ $latest?->student?->name }} {{ $latest?->created_at?->diffForHumans() }} — سيصلكم إشعار حين يعتمدها المشرف أو يطلب تعديلاً.
                </p>
            @elseif ($current->stage?->instructions)
                <p class="spotlight-note">{{ \Illuminate\Support\Str::limit($current->stage->instructions, 220) }}</p>
            @endif

            <div class="spotlight-actions">
                @if ($canSubmit && $current->canSubmit())
                    <a href="#milestone-{{ $current->id }}" class="btn {{ $current->needsRevision() ? 'btn-warning' : 'btn-primary' }}"
                        data-open-submit="{{ $current->id }}">
                        <i class="ti ti-upload me-1" aria-hidden="true"></i>
                        {{ $current->needsRevision() ? 'إعادة التسليم بعد التعديل' : 'تسليم المرحلة' }}
                    </a>
                @endif
                @if ($current->stage?->hasTemplate())
                    <a href="{{ route('stages.template', $current->stage_id) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-download me-1" aria-hidden="true"></i>
                        قالب المشرف
                    </a>
                @endif
                <a href="#milestone-{{ $current->id }}" class="spotlight-link">
                    التفاصيل في المراحل
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>
@endif

@push('js')
    <script>
        // زرّ التسليم في البطاقة يفتح نموذج مرحلته في القائمة — ومثله رابط بعلامة المرحلة
        (function () {
            function openSubmit(id) {
                var row = document.getElementById('milestone-' + id);
                if (!row) return;
                var form = row.querySelector('.ms-submit');
                if (form) form.open = true;
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var note = row.querySelector('.ms-submit textarea');
                if (note) setTimeout(function () { note.focus({ preventScroll: true }); }, 350);
            }
            document.querySelectorAll('[data-open-submit]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openSubmit(btn.dataset.openSubmit);
                });
            });
            var m = location.hash.match(/^#milestone-(\d+)$/);
            if (m) openSubmit(m[1]);
        })();
    </script>
@endpush
