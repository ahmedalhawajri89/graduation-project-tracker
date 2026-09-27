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

                    <label class="form-label required">حجم الفريق المسموح</label>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text">من</span>
                                <input id="min" type="number" min="1" step="1"
                                    class="form-control @error('min') is-invalid @enderror" name="min"
                                    value="{{ old('min') }}" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text">إلى</span>
                                <input id="max" type="number" min="1" step="1"
                                    class="form-control @error('max') is-invalid @enderror" name="max"
                                    value="{{ old('max') }}" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    {{-- الفرق المسجَّلة سابقاً لا تُعاد مراجعتها: التحقّق
                         يقع لحظة التسجيل وحدها --}}
                    <div class="form-hint mb-3">
                        التعديل يسري على التسجيلات الجديدة فقط، ولا يمسّ الفرق القائمة.
                    </div>
                    @error('min')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    @error('max')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror

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
