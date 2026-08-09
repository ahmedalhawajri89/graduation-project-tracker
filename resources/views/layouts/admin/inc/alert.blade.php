{{-- تنبيهات منبثقة (Toasts) لا تزحزح محتوى الصفحة --}}
<div class="toast-container position-fixed top-0 start-0 p-3">

    @if (Session::get('success'))
        <div class="toast align-items-center border-0 mb-2 app-toast" role="status" aria-live="polite"
            aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center text-success">
                    <i class="ti ti-circle-check fs-3 me-2"></i>
                    <span>{{ Session::get('success') }}</span>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="إغلاق"></button>
            </div>
        </div>
    @endif

    @if (Session::get('fail'))
        <div class="toast align-items-center border-0 mb-2 app-toast" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-delay="6000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center text-danger">
                    <i class="ti ti-alert-circle fs-3 me-2"></i>
                    <span>{{ Session::get('fail') }}</span>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="إغلاق"></button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="toast align-items-center border-0 mb-2 app-toast" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-autohide="false">
            <div class="d-flex">
                <div class="toast-body text-danger">
                    <div class="d-flex align-items-center mb-1">
                        <i class="ti ti-alert-triangle fs-3 me-2"></i>
                        <strong>يرجى تصحيح الأخطاء التالية</strong>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="إغلاق"></button>
            </div>
        </div>
    @endif

</div>

@if (Session::get('success') || Session::get('fail') || $errors->any())
    @push('js')
        <script>
            document.querySelectorAll('.app-toast').forEach(function (el) {
                new bootstrap.Toast(el).show();
            });
        </script>
    @endpush
@endif
