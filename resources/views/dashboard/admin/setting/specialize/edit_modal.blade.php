<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabel">{{ __('تعديل بيانات التخصص') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
            </div>

            <form action="{{ route('admin.specialize.update', 'test') }}" method="POST">
                @csrf
                @method('put')
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name" class="form-label required">{{ __('التخصص') }}</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" placeholder="{{ __('التخصص') }}" required autocomplete="off"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">{{ __('إغلاق') }}</button>
                    <button type="submit" class="btn btn-primary" name="submit" value="update">
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
        $('body').on('click', '.btn-edit', function(event) {
            event.preventDefault();
            var button = $(this);
            var name = button.data('name');


            var edit_id = button.data('id');
            var modal = $('#editModal');
            // var modal = $(this)
            modal.find('#edit-id').val(edit_id);
            modal.find('input[name="name"]').val(name);

            modal.find('.modal-footer').show();
            $('.jquer-valid').remove();
            $("#editModal form").find('*').removeClass('border-danger');

            setTimeout(function() {
                modal.find('input[name="name"]').focus()
            }, 500);

        })
    </script>
@endpush
