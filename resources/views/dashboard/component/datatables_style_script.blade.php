@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@push('js')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        var table = $('#dataTable-1').DataTable({
            // autoWidth: true كان يقيس الرؤوس ويكتب عليها عرضاً ثابتاً
            // لا يطابق عرض خلايا الجسم — وفي RTL يظهر الانزياح بوضوح،
            // فتبدو البيانات مزاحة عن عناوين أعمدتها.
            autoWidth: false,
            // dom يحدّد أين تُرسم أدوات المكتبة. البحث يُنقل إلى شريط
            // التصفية أعلى الصفحة (انظر searchInto أدناه)، وقائمة عدد
            // السجلات تُحذف — نادراً ما تُلمس. فلا يبقى شريط فوق
            // الجدول، ويتطابق مع جدول المجموعات.
            dom: "<'dt-top'f>t<'dt-bottom'ip>",
            pageLength: 15,
            processing: true,
            serverSide: true,
            // يُطبع JSON لا نصّاً مُهرَّباً: التهريب يحوّل الفاصل بين معاملات الرابط
            // إلى كيان HTML داخل نصّ JS، فيصل كل فلتر بعد الأول باسم خاطئ ويُتجاهَل
            ajax: @json($urlData),
            columns: {!! $columnsData !!},
            language: {
                processing: "جارٍ التحميل...",
                // التسمية تُفرَّغ: الحقل ينتقل إلى شريط التصفية بأيقونة
                // بحث ونصّ إرشادي، فتصير كلمة «بحث:» تكراراً
                search: "",
                // كان ثابتاً «ابحث بالاسم أو الرقم أو البريد» فظهر في
                // صفحات لا بريد فيها. كل صفحة تصف ما يُبحث فيه عندها.
                searchPlaceholder: "{{ $searchPlaceholder ?? 'ابحث…' }}",
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

        // نقل حقل البحث إلى شريط التصفية إن وُجد فيه مكان مخصّص،
        // فيصير للصفحة مدخل بحث واحد لا اثنان
        (function () {
            var slot = document.getElementById('dt-search-slot');
            var box = document.querySelector('.dt-top');
            if (!slot || !box) return;

            slot.appendChild(box);
            box.classList.add('is-inline');

            // التسمية أُفرِغت أعلاه، فيبقى الحقل بلا اسم لقارئات الشاشة.
            // المُعرِّف هنا يربطه بالـ label الموجود في شريط التصفية.
            var input = box.querySelector('input');
            if (!input) return;

            input.id = 'dt-search-input';
            input.setAttribute('type', 'search');

            // الحقل صار داخل نموذج التصفية (بلا \u200Ename\u200E فلا يُرسل معه).
            // لكن Enter فيه كان يُرسل النموذج ويعيد تحميل الصفحة، وبحث
            // DataTables فوريّ أصلاً — فلا شيء ينتظر Enter.
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        })();

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
