{{--
    تأكيد تفعيل فصل دراسي.

    كان زرّاً في خليّة جدول يسأل بـ \u200Econfirm()\u200E المتصفّح: نافذة بلا
    تنسيق، لا تقول ما الفصل الحالي ولا ماذا يتغيّر بعد التبديل.

    وهذا أخطر مفتاح في النظام: التقديم واللوحات والإحصائيات كلها
    تُقرأ من الفصل النشط وحده.
--}}
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
                    {{-- الانتقال معروضاً لا موصوفاً: من أين وإلى أين --}}
                    <div class="term-switch">
                        <div class="term-switch-side">
                            <span class="term-switch-label">الآن</span>
                            <b>{{ $active?->name ?? 'لا يوجد' }}</b>
                        </div>
                        <i class="ti ti-arrow-left term-switch-arrow" aria-hidden="true"></i>
                        <div class="term-switch-side is-next">
                            <span class="term-switch-label">بعد التفعيل</span>
                            <b id="activate-name"></b>
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
                            يمكن التبديل مرة أخرى في أي وقت
                        </li>
                    </ul>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
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

            modal.find('#activate-name').text(button.data('name'));

            // المسار يحمل المُعرِّف في عنوانه، فيُبنى عند كل فتح
            modal.find('#activate-form').attr(
                'action',
                '{{ route('admin.semesters.activate', 'placeholder') }}'.replace('placeholder', button.data('id'))
            );
        });
    </script>
@endpush
