{{--
    تأكيد إيقاف التخصص.

    الإيقاف ليس حذفاً، لكنه يبدو كذلك للأدمن الذي يراه أول مرة. النافذة
    تقول بالأرقام ما سيحدث وما لن يحدث، فلا يتردّد ولا يندم.
--}}
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveLabel">
                    {{ __('إيقاف التخصص') }} (<span class="text-secondary" id="archive-name"></span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
            </div>

            <form action="{{ route('admin.specialize.archive', 'placeholder') }}" method="POST" id="archive-form">
                @csrf

                <div class="modal-body">
                    <p class="text-secondary mb-3">
                        {{ __('لا يمكن حذف تخصص عليه أشخاص: حذفه يترك طلابه ومشرفيه بلا تصنيف ويمحو أنواع مشاريعه نهائياً. الإيقاف يوقف التسجيل عليه ويُبقي كل شيء.') }}
                    </p>

                    <ul class="archive-effects">
                        <li class="is-stop">
                            <i class="ti ti-circle-x" aria-hidden="true"></i>
                            {{ __('لن يُسجَّل عليه طالب أو مشرف جديد، ولن يظهر في الاستيراد') }}
                        </li>
                        <li class="is-keep">
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            {!! __(':students و:supervisors يبقون كما هم', ['students' => '<b><span id="archive-students">0</span> ' . e(__('طالباً')) . '</b>', 'supervisors' => '<b><span id="archive-supervisors">0</span> ' . e(__('مشرفاً')) . '</b>']) !!}
                        </li>
                        <li class="is-keep">
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            {{ __('مشاريعه وأنواعه ودرجاته تبقى سليمة، وطلابه يواصلون العمل') }}
                        </li>
                        <li class="is-keep">
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            {{ __('يمكن استئنافه في أي وقت بنقرة') }}
                        </li>
                    </ul>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-archive me-1" aria-hidden="true"></i>
                        {{ __('إيقاف التخصص') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
    <script>
        $('body').on('click', '.btn-archive', function () {
            var button = $(this);
            var modal = $('#archiveModal');

            modal.find('#archive-name').text(button.data('name'));
            modal.find('#archive-students').text(button.data('students'));
            modal.find('#archive-supervisors').text(button.data('supervisors'));

            // المسار يحمل المُعرِّف في عنوانه، فيُبنى عند كل فتح
            modal.find('#archive-form').attr(
                'action',
                '{{ route('admin.specialize.archive', 'placeholder') }}'.replace('placeholder', button.data('id'))
            );
        });
    </script>
@endpush
