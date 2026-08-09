@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@push('js')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        var table = $('#dataTable-1').DataTable({
            autoWidth: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "الكل"]
            ],
            processing: true,
            serverSide: true,
            ajax: "{{ $urlData }}",
            columns: {!! $columnsData !!},
            language: {
                processing: "جارٍ التحميل...",
                search: "بحث:",
                lengthMenu: "أظهر _MENU_ سجلات",
                info: "عرض _START_ إلى _END_ من أصل _TOTAL_ سجل",
                infoEmpty: "لا توجد سجلات",
                infoFiltered: "(مرشّحة من أصل _MAX_ سجل)",
                zeroRecords: "لم يُعثر على نتائج",
                emptyTable: "لا توجد بيانات في الجدول",
                paginate: {
                    first: "الأول",
                    previous: "السابق",
                    next: "التالي",
                    last: "الأخير"
                }
            }
        });

        function refresh_tab() {
            table.ajax.reload();
        }

        @if ($errors->any() && old('submit') == 'create')
            new bootstrap.Modal(document.getElementById('createModal')).show();
        @endif
        @if ($errors->any() && old('submit') == 'update')
            new bootstrap.Modal(document.getElementById('editModal')).show();
        @endif
    </script>
@endpush
