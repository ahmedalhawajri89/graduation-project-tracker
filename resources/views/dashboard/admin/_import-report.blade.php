{{--
    تقرير الاستيراد — ما أُضيف وما تُخطّي ولماذا.

    كان الاستيراد يقول «بدأت عملية الرفع بنجاح» ثم يفشل بصمت. التنبيه
    المنبثق يحمل الخلاصة، والتفصيل هنا حيث يُقرأ ويُصلَح في الملف.
--}}

@php $report = session('import_report'); @endphp

@if ($report && (count($report['skipped']) || $report['errored']))
    <section class="dist-panel mb-4 import-report" role="status">
        <div class="dist-head">
            <span>
                <i class="ti ti-file-alert me-1" aria-hidden="true"></i>
                تقرير الاستيراد
            </span>
            <span class="dist-head-note">
                أُضيف {{ $report['added'] }} · تُخطّي {{ count($report['skipped']) + $report['errored'] }}
            </span>
        </div>

        <p class="import-report-hint">
            الصفوف أدناه لم تُضف. صحّحها في الملف ثم ارفعه مجدّداً — الصفوف المضافة لن تتكرّر، فالرقم الجامعي والبريد فريدان.
        </p>

        <ul class="import-report-list">
            @foreach (array_slice($report['skipped'], 0, 50, true) as $row => $reason)
                <li><b>الصف {{ $row }}</b> {{ $reason }}</li>
            @endforeach
            @if (count($report['skipped']) > 50)
                <li class="text-secondary">و{{ count($report['skipped']) - 50 }} صفّاً آخر.</li>
            @endif
            @if ($report['errored'])
                <li><b>{{ $report['errored'] }} صفّ</b> تعذّر حفظه لسبب غير متوقّع — التفصيل في سجلّ الخادم.</li>
            @endif
        </ul>
    </section>
@endif
