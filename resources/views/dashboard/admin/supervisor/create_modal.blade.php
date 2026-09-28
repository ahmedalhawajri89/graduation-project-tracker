{{-- قيم ‎old()‎ وأخطاؤها لهذه النافذة حين فشلت الإضافة وحدها: خطأ درج
     التعديل (‎old('id')‎) كان يملؤها بقيم سجلّ آخر ويعلّم حقولها بالأحمر --}}
@php $mine = ! old('id'); @endphp
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">إضافة مشرف جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('admin.supervisors.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name_create" class="form-label required">اسم المشرف</label>
                        <input id="name_create" type="text" class="form-control {{ $mine && $errors->has('name') ? 'is-invalid' : '' }}"
                            name="name" value="{{ ($mine ? old('name') : null) }}" placeholder="اسم المشرف" required autocomplete="off"
                            autofocus>
                        @if ($mine) @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="university_id_create" class="form-label required">الرقم الجامعي</label>
                        <input id="university_id_create" type="text"
                            class="form-control {{ $mine && $errors->has('university_id') ? 'is-invalid' : '' }}" name="university_id"
                            value="{{ ($mine ? old('university_id') : null) }}" placeholder="الرقم الجامعي" required autocomplete="off">
                        @if ($mine) @error('university_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="specialize_id_create" class="form-label required">التخصص</label>
                        <select id="specialize_id_create" class="form-select {{ $mine && $errors->has('specialize_id') ? 'is-invalid' : '' }}"
                            name="specialize_id">
                            @foreach ($specializes as $specialize)
                                <option @if (($mine ? old('specialize_id') : null) == $specialize->id) selected @endif value="{{ $specialize->id }}">
                                    {{ $specialize->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($mine) @error('specialize_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="email_create" class="form-label required">البريد الالكتروني</label>
                        <input id="email_create" type="text" class="form-control {{ $mine && $errors->has('email') ? 'is-invalid' : '' }}"
                            name="email" value="{{ ($mine ? old('email') : null) }}" placeholder="البريد الالكتروني" required
                            autocomplete="off">
                        @if ($mine) @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="phone_create" class="form-label required">رقم الجوال</label>
                        <input id="phone_create" type="text" class="form-control {{ $mine && $errors->has('phone') ? 'is-invalid' : '' }}"
                            name="phone" value="{{ ($mine ? old('phone') : null) }}" placeholder="رقم الجوال" required autocomplete="off">
                        @if ($mine) @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="password_create" class="form-label required">كلمة السر</label>
                        <input id="password_create" type="password"
                            class="form-control {{ $mine && $errors->has('password') ? 'is-invalid' : '' }}" name="password"
                            placeholder="كلمة السر" required autocomplete="off">
                        @if ($mine) @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="gender_create" class="form-label required">الجنس</label>
                        <select id="gender_create" class="form-select {{ $mine && $errors->has('gender') ? 'is-invalid' : '' }}" name="gender">
                            <option @if (($mine ? old('gender') : null) == 'male') selected @endif value="male">ذكر</option>
                            <option @if (($mine ? old('gender') : null) == 'female') selected @endif value="female">أنثى</option>
                        </select>
                        @if ($mine) @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                    </div>

                    <div class="mb-3">
                        <label for="max_group_create" class="form-label">الحد الاقصى للمجموعات</label>
                        <input id="max_group_create" type="text"
                            class="form-control {{ $mine && $errors->has('max_group') ? 'is-invalid' : '' }}" name="max_group"
                            value="{{ ($mine ? old('max_group') : null) }}" placeholder="الحد الاقصى للمجموعات" autocomplete="off">
                        @if ($mine) @error('max_group')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
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
                modal.find('input[name="password"]').val('');
                modal.find('input[name="name"]').focus();
            }, 500);

        });
    </script>
@endpush

@if ($errors->any() && ! old('id'))
    @push('js')
        <script>
            // فشلت الإضافة: تُعاد فتح النافذة بقيمها وأخطائها — كانت تُغلق فتضيع الرسالة
            new bootstrap.Modal(document.getElementById('createModal')).show();
        </script>
    @endpush
@endif
