{{--
    تأكيد تفعيل فصل دراسي.

    كان زرّاً في خليّة جدول يسأل بـ \u200Econfirm()\u200E المتصفّح: نافذة بلا
    تنسيق، لا تقول ما الفصل الحالي ولا ماذا يتغيّر بعد التبديل.

    وهذا أخطر مفتاح في النظام: التقديم واللوحات والإحصائيات كلها
    تُقرأ من الفصل النشط وحده. فالنافذة تقول الانتقال بأرقامه، وتطلب
    تأكيداً صريحاً قبل أن يُفعَّل الزرّ، والتبديل يُسجَّل في سجلّ التدقيق.
--}}
@php
    $activeTidy = $active ? (fn ($p) => $p['year'] ? $p['term'] . ' · ' . "\u{2066}" . $p['year'] . "\u{2069}" : $p['term'])($active->parts()) : null;
@endphp

<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activateLabel">تفعيل فصل دراسي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form action="{{ route('admin.semesters.activate', 'placeholder') }}" method="POST" id="activate-form">
                @csrf

                <div class="modal-body">
                    {{-- الانتقال معروضاً لا موصوفاً: من أين وإلى أين، وبكم مشروع --}}
                    <div class="term-switch">
                        <div class="term-switch-side">
                            <span class="term-switch-label">الآن</span>
                            <b>{{ $activeTidy ?? 'لا يوجد' }}</b>
                            @if ($active)
                                <small>{{ $active->projects_count }} مشروعاً — ينتقل إلى الأرشيف</small>
                            @endif
                        </div>
                        <i class="ti ti-arrow-left term-switch-arrow" aria-hidden="true"></i>
                        <div class="term-switch-side is-next">
                            <span class="term-switch-label">بعد التفعيل</span>
                            <b id="activate-name"></b>
                            <small id="activate-projects"></small>
                        </div>
                    </div>

                    <ul class="archive-effects">
                        <li class="is-stop">
                            <i class="ti ti-arrow-right-circle" aria-hidden="true"></i>
                            تسجيل المشاريع الجديدة يصير على الفصل المُفعَّل
                        </li>
                        <li class="is-stop">
                            <i class="ti ti-arrow-right-circle" aria-hidden="true"></i>
                            لوحة التحكم والإحصائيات وأعباء المشرفين تُحسب منه
                        </li>
                        <li class="is-keep">
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            مشاريع الفصل السابق ودرجاته تبقى سليمة في أرشيفه
                        </li>
                        <li class="is-keep">
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            يمكن التبديل مرة أخرى في أي وقت، ويُسجَّل كل تبديل في سجلّ التدقيق
                        </li>
                    </ul>

                    <label class="tm-confirm">
                        <input type="checkbox" class="form-check-input" id="activate-confirm">
                        <span>فهمت أن النظام كله سينتقل إلى <b id="activate-name-confirm"></b></span>
                    </label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="activate-submit" disabled>
                        <i class="ti ti-player-play me-1" aria-hidden="true"></i>
                        تفعيل الفصل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
    <script>
        $('body').on('click', '.btn-activate', function () {
            var button = $(this);
            var modal = $('#activateModal');
            var projects = parseInt(button.data('projects'), 10) || 0;

            modal.find('#activate-name, #activate-name-confirm').text(button.data('name'));
            modal.find('#activate-projects').text(projects ? projects + ' مشروعاً في أرشيفه' : 'فصل جديد بلا مشاريع');

            // التأكيد يُعاد في كل فتح — لا يبقى مُعلَّماً من فتح سابق
            modal.find('#activate-confirm').prop('checked', false);
            modal.find('#activate-submit').prop('disabled', true);

            // المسار يحمل المُعرِّف في عنوانه، فيُبنى عند كل فتح
            modal.find('#activate-form').attr(
                'action',
                '{{ route('admin.semesters.activate', 'placeholder') }}'.replace('placeholder', button.data('id'))
            );
        });

        $('body').on('change', '#activate-confirm', function () {
            $('#activate-submit').prop('disabled', !this.checked);
        });
    </script>
@endpush
