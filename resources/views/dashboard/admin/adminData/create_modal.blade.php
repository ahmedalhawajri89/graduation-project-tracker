{{-- قيم ‎old()‎ وأخطاؤها لهذه النافذة حين فشلت الإضافة وحدها: خطأ درج
     التعديل (‎old('id')‎) كان يملؤها بقيم سجلّ آخر ويعلّم حقولها بالأحمر --}}
@php $mine = ! old('id'); @endphp
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">إضافة مسؤول جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('admin.administrators.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name_create" class="form-label required">اسم الآدمن</label>
                        <input id="name_create" type="text" class="form-control {{ $mine && $errors->has('name') ? 'is-invalid' : '' }}"
                            name="name" value="{{ ($mine ? old('name') : null) }}" placeholder="اسم الآدمن" required autocomplete="off"
                            autofocus>
                        @if ($mine) @error('name')
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
                            placeholder="٨ أحرف على الأقل" required minlength="8" autocomplete="new-password">
                        @if ($mine) @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror @endif
                        <div class="form-hint">حساب المسؤول يملك صلاحية كاملة على النظام.</div>
                    </div>

                    {{-- التأكيد يمنع خطأً مطبعياً يقفل الحساب الجديد
                         صامتاً: لا أحد يعرف كلمة المرور التي كُتبت --}}
                    <div class="mb-3">
                        <label for="password_confirmation_create" class="form-label required">تأكيد كلمة السر</label>
                        <input id="password_confirmation_create" type="password" class="form-control"
                            name="password_confirmation" placeholder="أعد كتابتها" required autocomplete="new-password">
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
                modal.find('input[name="name"]').focus()
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
