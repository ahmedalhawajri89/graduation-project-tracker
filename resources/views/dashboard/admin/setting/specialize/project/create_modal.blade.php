<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">
                    إضافة نوع مشروع جديد لتخصص
                    <span class="text-primary">{{ $specialize->name }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('admin.specialize.projects.store', $specialize->id) }}" method="POST">
                @csrf
                <input type="hidden" value="{{ $specialize->id }}" name="specialize_id">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name_create" class="form-label required">نوع المشروع</label>
                        <input id="name_create" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" placeholder="نوع المشروع" required autocomplete="off"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="min_create" class="form-label required">الحد الأدنى لعدد أعضاء الفريق</label>
                        <input id="min_create" type="text" class="form-control @error('min') is-invalid @enderror"
                            name="min" value="{{ old('min') }}" placeholder="الحد الأدنى لعدد أعضاء الفريق" required
                            autocomplete="off">
                        @error('min')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="max_create" class="form-label required">الحد الأقصى لعدد أعضاء الفريق</label>
                        <input id="max_create" type="text" class="form-control @error('max') is-invalid @enderror"
                            name="max" value="{{ old('max') }}" placeholder="الحد الأقصى لعدد أعضاء الفريق" required
                            autocomplete="off">
                        @error('max')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary" name="submit" value="create">
                        <i class="ti ti-device-floppy me-1"></i>
                        حفظ
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
