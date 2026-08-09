<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabel">تعديل نوع المشروع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form action="{{ route('admin.specialize.projects.update', $specialize->id) }}" method="POST">
                @csrf
                @method('put')
                <input type="hidden" value="{{ $specialize->id }}" name="specialize_id">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name" class="form-label required">نوع المشروع</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" placeholder="نوع المشروع" required autocomplete="off"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="min" class="form-label required">الحد الأدنى لعدد أعضاء الفريق</label>
                        <input id="min" type="text" class="form-control @error('min') is-invalid @enderror"
                            name="min" value="{{ old('min') }}" placeholder="الحد الأدنى لعدد أعضاء الفريق" required
                            autocomplete="off">
                        @error('min')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="max" class="form-label required">الحد الأقصى لعدد أعضاء الفريق</label>
                        <input id="max" type="text" class="form-control @error('max') is-invalid @enderror"
                            name="max" value="{{ old('max') }}" placeholder="الحد الأقصى لعدد أعضاء الفريق" required
                            autocomplete="off">
                        @error('max')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary" name="submit" value="update">
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
        $('body').on('click', '.btn-edit', function(event) {
            event.preventDefault();
            var button = $(this);
            var name = button.data('name');
            var min = button.data('min');
            var max = button.data('max');


            var edit_id = button.data('id');
            var modal = $('#editModal');
            // var modal = $(this)
            modal.find('#edit-id').val(edit_id);
            modal.find('input[name="name"]').val(name);
            modal.find('input[name="min"]').val(min);
            modal.find('input[name="max"]').val(max);

            modal.find('.modal-footer').show();
            $('.jquer-valid').remove();
            $("#editModal form").find('*').removeClass('border-danger');

            setTimeout(function() {
                modal.find('input[name="name"]').focus()
            }, 500);

        })
    </script>
@endpush
