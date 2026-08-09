<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabel">تعديل بيانات المشرف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form action="{{ route('admin.supervisors.update', 'test') }}" method="POST">
                @csrf
                @method('put')
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name" class="form-label required">اسم المشرف</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" placeholder="اسم المشرف" required autocomplete="off"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="university_id" class="form-label required">الرقم الجامعي</label>
                        <input id="university_id" type="text"
                            class="form-control @error('university_id') is-invalid @enderror" name="university_id"
                            value="{{ old('university_id') }}" placeholder="الرقم الجامعي" required autocomplete="off">
                        @error('university_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="specialize_id" class="form-label required">التخصص</label>
                        <select id="specialize_id" class="form-select @error('specialize_id') is-invalid @enderror"
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
                        <label for="email" class="form-label required">البريد الالكتروني</label>
                        <input id="email" type="text" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" placeholder="البريد الالكتروني" required
                            autocomplete="off">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label required">رقم الجوال</label>
                        <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror"
                            name="phone" value="{{ old('phone') }}" placeholder="رقم الجوال" required autocomplete="off">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة السر</label>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            placeholder="كلمة السر" autocomplete="off">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label required">الجنس</label>
                        <select id="gender" class="form-select @error('gender') is-invalid @enderror" name="gender">
                            <option value="male">ذكر</option>
                            <option value="female">أنثى</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="max_group" class="form-label">الحد الاقصى للمجموعات</label>
                        <input id="max_group" type="text"
                            class="form-control @error('max_group') is-invalid @enderror" name="max_group"
                            value="{{ old('max_group') }}" placeholder="الحد الاقصى للمجموعات" autocomplete="off">
                        @error('max_group')
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
            var university_id = button.data('university_id');
            var specialize_id = button.data('specialize_id');
            var email = button.data('email');
            var gender = button.data('gender');
            var max_group = button.data('max_group');

            var phone = button.data('phone');
            var edit_id = button.data('id');
            var modal = $('#editModal');
            // var modal = $(this)
            modal.find('#edit-id').val(edit_id);
            modal.find('input[name="name"]').val(name);
            modal.find('input[name="university_id"]').val(university_id);
            modal.find('select[name="specialize_id"]').val(specialize_id);
            modal.find('input[name="email"]').val(email);
            modal.find('input[name="phone"]').val(phone);
            modal.find('input[name="max_group"]').val(max_group);
            modal.find('select[name="gender"]').val(gender);

            modal.find('.modal-footer').show();
            $('.jquer-valid').remove();
            $("#editModal form").find('*').removeClass('border-danger');

            setTimeout(function() {
                modal.find('input[name="password"]').val('');
                modal.find('input[name="name"]').focus();
            }, 500);

        })
    </script>
@endpush
