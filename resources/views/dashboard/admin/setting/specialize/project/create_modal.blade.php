<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">
                    {{ __('إضافة نوع مشروع جديد لتخصص') }}
                    <span class="text-primary">{{ $specialize->name }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
            </div>
            <form action="{{ route('admin.specialize.projects.store', $specialize->id) }}" method="POST" data-range-form>
                @csrf
                <input type="hidden" value="{{ $specialize->id }}" name="specialize_id">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name_create" class="form-label required">{{ __('نوع المشروع') }}</label>
                        <input id="name_create" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" placeholder="{{ __('نوع المشروع') }}" required autocomplete="off"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الحدّان مفهوم واحد — مدى حجم الفريق — فيقفان في
                         صفّ واحد. و\u200Etype="number"\u200E لا \u200Etext\u200E: لوحة أرقام
                         على الهاتف، وحدّ في المتصفّح قبل الخادم. --}}
                    <label class="form-label required">{{ __('حجم الفريق المسموح') }}</label>
                    <div class="pt-range-edit">
                        <div class="pt-step-field">
                            <span>{{ __('من') }}</span>
                            <button type="button" data-step="-1" data-field="min" aria-label="{{ __('إنقاص الحدّ الأدنى') }}"><i class="ti ti-minus" aria-hidden="true"></i></button>
                            <input type="number" min="1" max="20" step="1" inputmode="numeric" name="min"
                                class="form-control @error('min') is-invalid @enderror" value="{{ old('min', 2) }}" required aria-label="{{ __('الحدّ الأدنى') }}">
                            <button type="button" data-step="1" data-field="min" aria-label="{{ __('زيادة الحدّ الأدنى') }}"><i class="ti ti-plus" aria-hidden="true"></i></button>
                        </div>
                        <div class="pt-step-field">
                            <span>{{ __('إلى') }}</span>
                            <button type="button" data-step="-1" data-field="max" aria-label="{{ __('إنقاص الحدّ الأعلى') }}"><i class="ti ti-minus" aria-hidden="true"></i></button>
                            <input type="number" min="1" max="20" step="1" inputmode="numeric" name="max"
                                class="form-control @error('max') is-invalid @enderror" value="{{ old('max', 3) }}" required aria-label="{{ __('الحدّ الأعلى') }}">
                            <button type="button" data-step="1" data-field="max" aria-label="{{ __('زيادة الحدّ الأعلى') }}"><i class="ti ti-plus" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="pt-range-preview">
                        <span class="pt-dots is-lg" data-range-dots aria-hidden="true"></span>
                        <span class="pt-range-live" data-range-text aria-live="polite"></span>
                    </div>
                    <div class="form-hint mb-3">{{ __('فريق خارج هذا المدى يُرفض عند تسجيل الطالب لمشروعه.') }}</div>
                    @error('min')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    @error('max')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">{{ __('إغلاق') }}</button>
                    <button type="submit" class="btn btn-primary" name="submit" value="create">
                        <i class="ti ti-device-floppy me-1"></i>
                        {{ __('حفظ') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('js')
    <script>
        $('body').on('click', '.btn-create', function(event) {

            event.preventDefault();

            var modal = $('#createModal');


            modal.find('.modal-footer').show();
            $('.jquer-valid').remove();
            $("#createModal form").find('*').removeClass('border-danger');

            setTimeout(function() {
                modal.find('input[name="name"]').focus()
            }, 500);

        });
    </script>
@endpush
