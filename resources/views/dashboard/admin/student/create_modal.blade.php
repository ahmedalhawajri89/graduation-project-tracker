<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabel">إضافة طالب جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name_create" class="form-label required">اسم الطالب</label>
                        <input id="name_create" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" placeholder="اسم الطالب" required autocomplete="off"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="university_id_create" class="form-label required">الرقم الجامعي</label>
                        <input id="university_id_create" type="text"
                            class="form-control @error('university_id') is-invalid @enderror" name="university_id"
                            value="{{ old('university_id') }}" placeholder="الرقم الجامعي" required autocomplete="off">
                        @error('university_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="specialize_id_create" class="form-label required">التخصص</label>
                        <select id="specialize_id_create" class="form-select @error('specialize_id') is-invalid @enderror"
                            name="specialize_id">
                            @foreach ($specializes as $specialize)
                                <option @if (old('specialize_id') == $specialize->id) selected @endif value="{{ $specialize->id }}">
                                    {{ $specialize->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('specialize_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email_create" class="form-label required">البريد الالكتروني</label>
                        <input id="email_create" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" placeholder="البريد الالكتروني" required
                            autocomplete="off">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone_create" class="form-label required">رقم الجوال</label>
                        <input id="phone_create" type="text" class="form-control @error('phone') is-invalid @enderror"
                            name="phone" value="{{ old('phone') }}" placeholder="رقم الجوال" required autocomplete="off">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_create" class="form-label required">كلمة السر</label>
                        <div class="password-wrapper">
                            <input id="password_create" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                placeholder="8 أحرف على الأقل" required autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                                data-target="password_create">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="gender_create" class="form-label required">الجنس</label>
                        <select id="gender_create" class="form-select @error('gender') is-invalid @enderror"
                            name="gender">
                            <option @if (old('gender') == 'male') selected @endif value="male">ذكر</option>
                            <option @if (old('gender') == 'female') selected @endif value="female">أنثى</option>
                        </select>
                        @error('gender')
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
                modal.find('input[name="password"]').val('');
                modal.find('input[name="name"]').focus();
            }, 500);
        });
    </script>
@endpush
