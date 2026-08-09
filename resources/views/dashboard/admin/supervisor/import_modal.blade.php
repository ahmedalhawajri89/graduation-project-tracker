<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLabel">رفع ملف اكسل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('admin.supervisors.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="validatedCustomFile" class="form-label required">رفع ملف اكسل</label>
                        <input type="file" name="attachment"
                            class="form-control @error('attachment') is-invalid @enderror" id="validatedCustomFile">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <a class="btn btn-outline-info" href="{{ asset('example.xlsx') }}">
                        <i class="ti ti-download me-1"></i>
                        حمل نموذج البيانات
                    </a>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary" name="submit" value="import">
                        <i class="ti ti-file-spreadsheet me-1"></i>
                        رفع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('js')
    <script>
        $('body').on('click', '.btn-import', function(event) {

            event.preventDefault();

            var modal = $('#importModal');


            modal.find('.modal-footer').show();
            $('.jquer-valid').remove();
            $("#importModal form").find('*').removeClass('border-danger');


        });
    </script>
@endpush
