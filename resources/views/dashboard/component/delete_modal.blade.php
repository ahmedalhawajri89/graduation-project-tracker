<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLabel">
                    حذف {{ $delete_title }}
                    (<span class="text-danger" id="delete-name"></span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route("{$delete_controller_name}.destroy", 'test') }}" method="POST">
                @csrf
                @method('delete')
                <input type="hidden" name="id" id="delete-id" value="">

                <div class="modal-body">
                    <div class="alert alert-danger mb-0">
                        <div class="d-flex">
                            <i class="ti ti-alert-triangle fs-2 me-2"></i>
                            <p class="mb-0">هل أنت متأكد من عملية الحذف؟ لا يمكن التراجع عنها.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger" name="submit" value="delete">
                        <i class="ti ti-trash me-1"></i>
                        حذف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('js')
    <script>
        $('body').on('click', '.btn-delete', function(event) {
            event.preventDefault();
            var button = $(this);
            var modal = $('#deleteModal');
            modal.find('#delete-id').val(button.data('id'));
            modal.find('#delete-name').html(button.data('name'));
            modal.find('.modal-footer').show();
            $('.jquer-valid').remove();
            $("#deleteModal form").find('*').removeClass('border-danger');
        });
    </script>
@endpush
