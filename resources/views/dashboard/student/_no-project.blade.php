{{--
    لا مشروع بعد.

    كان النموذج بطاقةً داخل الصفحة تحت ترويسة ومؤشّرات فارغة. وهو
    **الصفحة كلّها** في هذه الحالة — لا شيء آخر يفعله الطالب هنا.
--}}

@if ($lastRejected)
    {{-- سبب الرفض يتصدّر: هو ما يُبنى عليه الطلب التالي --}}
    <section class="reject-panel mb-4">
        <div class="reject-head">
            <i class="ti ti-circle-x" aria-hidden="true"></i>
            <div>
                <h2>{{ __('رُفض مقترحك السابق') }}</h2>
                <p>«{{ $lastRejected->title }}»</p>
            </div>
        </div>

        @if ($lastNotification && ! empty($lastNotification->data['msg']))
            <blockquote class="reject-reason">
                <span class="reject-reason-label">{{ __('ردّ المشرف') }}</span>
                {{ __($lastNotification->data['msg']) }}
            </blockquote>
        @endif

        <p class="reject-next">
            <i class="ti ti-arrow-down" aria-hidden="true"></i>
            {{ __('عالِج الملاحظة وقدّم مقترحاً جديداً من النموذج أدناه.') }}
        </p>
    </section>
@else
    <section class="start-panel mb-4">
        <i class="ti ti-rocket" aria-hidden="true"></i>
        <div>
            <h2>{{ __('لنبدأ مشروع تخرّجك') }}</h2>
            <p>
                {{ __('اختر نوع المشروع ومشرفاً وفريقك، واكتب فكرتك.') }}
                {{ __('يراجع المشرف المقترح ويردّ عليك، ثم تبدأ المتابعة من هذه الصفحة.') }}
            </p>
            <a href="{{ route('student.projects.explore') }}" class="start-panel-link">
                <i class="ti ti-telescope" aria-hidden="true"></i>
                {{ __('اطّلع أولاً على المشاريع المنجزة في تخصصك') }}
            </a>
        </div>
    </section>
@endif

<div class="dist-panel">
    <div class="dist-head">
        <span>{{ $lastRejected ? __('مقترح جديد') : __('تقديم مقترح المشروع') }}</span>
    </div>
    <div class="p-3">
        @include('dashboard.student.project_form')
    </div>
</div>
