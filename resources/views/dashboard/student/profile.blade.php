@extends('layouts.admin.admin')
@section('title', 'الملف الشخصي')

@section('content')

    <x-page-header pretitle="لوحة الطالب" title="الملف الشخصي" />

    <div class="row row-deck row-cards">
        {{-- بيانات ثابتة --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-id me-2"></i>
                        البيانات الجامعية
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <span class="avatar avatar-lg bg-primary-lt text-primary rounded-3 me-3">
                            {{ mb_substr($student->name, 0, 2) }}
                        </span>
                        <div>
                            <div class="fw-bold">{{ $student->name }}</div>
                            <div class="text-secondary small">{{ $student->specialize->name }}</div>
                        </div>
                    </div>
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">الرقم الجامعي</div>
                            <div class="datagrid-content">{{ $student->university_id }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">البريد الإلكتروني</div>
                            <div class="datagrid-content">{{ $student->email }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">الجنس</div>
                            <div class="datagrid-content">{{ __("site.{$student->gender}") }}</div>
                        </div>
                    </div>
                    <div class="form-hint mt-3">
                        <i class="ti ti-lock me-1"></i>
                        هذه البيانات تُدار من قِبل إدارة الكلية ولا يمكن تعديلها.
                    </div>
                </div>
            </div>
        </div>

        {{-- بيانات قابلة للتعديل --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-user-edit me-2"></i>
                        تعديل البيانات
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="phone">رقم الجوال</label>
                            <input id="phone" type="text" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $student->phone) }}" required dir="ltr">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <h4 class="mt-4 mb-3 text-secondary">
                            <i class="ti ti-key me-1"></i>
                            تغيير كلمة السر (اختياري)
                        </h4>

                        <div class="mb-3">
                            <label class="form-label" for="current_password">كلمة السر الحالية</label>
                            <div class="password-wrapper">
                                <input id="current_password" type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password" placeholder="••••••••">
                                <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                                    data-target="current_password"><i class="ti ti-eye"></i></button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="password">كلمة السر الجديدة</label>
                                <div class="password-wrapper">
                                    <input id="password" type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        autocomplete="new-password" placeholder="6 أحرف على الأقل">
                                    <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                                        data-target="password"><i class="ti ti-eye"></i></button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="password_confirmation">تأكيد كلمة السر</label>
                                <div class="password-wrapper">
                                    <input id="password_confirmation" type="password" name="password_confirmation"
                                        class="form-control" autocomplete="new-password" placeholder="••••••••">
                                    <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                                        data-target="password_confirmation"><i class="ti ti-eye"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" data-loading-text="جارٍ الحفظ..">
                                <i class="ti ti-device-floppy me-1"></i>
                                حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
