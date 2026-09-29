@props([
    'user',
    'role',          // نصّ الدور المعروض
    'action',        // مسار الحفظ
    'facts' => [],   // ['التخصص' => '...']  بيانات تصدرها الجامعة
    'editable' => ['phone'],
    'notice' => null,
    'avatarStore' => null,
    'avatarDestroy' => null,
])

{{--
    صفحة الملف الشخصي — للأدوار الثلاثة.

    كانت ثلاث Blade متشابهة ببطاقة يسار شبه فارغة (بريد وجنس فقط)
    ونموذج لا يتيح إلا رقم الجوال — بينما يستطيع المسؤول تعديل اسم
    أي مسؤول آخر من صفحة المسؤولين. معكوس.

    والحقول المقفلة تظهر مقفلةً لا مخفيّةً: من لا يجد اسمه في الصفحة
    يظنّ أن الصفحة ناقصة، لا أن الاسم ليس له.
--}}

@php
    $canName = in_array('name', $editable, true);
    $canEmail = in_array('email', $editable, true);
    $canGender = in_array('gender', $editable, true);
@endphp

<x-page-header title="الملف الشخصي" subtitle="حسابك وبياناتك وكلمة السر" />

@if ($notice)
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
        <i class="ti ti-alert-triangle fs-2" aria-hidden="true"></i>
        <div>{!! $notice !!}</div>
    </div>
@endif

{{-- لوح الهوية: من أنت، وبأي بريد تدخل، ومنذ متى.
     الشريط العلوي يعطي اللوح ثقلاً بصرياً يميّزه عن بطاقات النماذج
     تحته — هو تعريف لا نموذج إدخال. --}}
<section class="profile-hero mb-4">
    <div class="profile-hero-band" aria-hidden="true"></div>

    <div class="profile-hero-body">
        <x-avatar :user="$user" class="profile-avatar" />

        <div class="profile-identity">
            <h2 class="profile-name">{{ $user->name }}</h2>
            <span class="badge role-badge">{{ $role }}</span>

            {{-- معلومات التعريف في سطر واحد مفصول بنقاط، بدل بطاقة
                 ثانية تكرّر ما في النموذج تحت --}}
            <p class="profile-meta">
                <span dir="ltr">{{ $user->email }}</span>

                @foreach (array_slice($facts, 0, 2, true) as $label => $value)
                    <span class="profile-meta-sep" aria-hidden="true">·</span>
                    <span>{{ $label }}: <b>{{ $value }}</b></span>
                @endforeach

                @if ($user->created_at)
                    <span class="profile-meta-sep" aria-hidden="true">·</span>
                    <span>عضو منذ <b>{{ $user->created_at->translatedFormat('F Y') }}</b></span>
                @endif
            </p>
        </div>
    </div>
</section>

@error('avatar')
    <div class="alert alert-danger mb-3" role="alert">{{ $message }}</div>
@enderror

{{-- عمودان مقصودان لا \u200Eauto-fit\u200E: الصورة والأمان أقصر من المعلومات،
     وتركها للتوزيع التلقائي يُنتج ثلاثة أعمدة متفاوتة الارتفاع. --}}
<div class="profile-grid">

    <div class="profile-col">

        {{-- ═══ الصورة الشخصية ═══
             اختيارية: الأحرف الأولى ليست حالة خطأ بل الأساس، والصورة
             تحلّ محلها إن وُجدت. --}}
        @if ($avatarStore)
            <section class="profile-section">
                <header class="profile-section-head">
                    <i class="ti ti-camera" aria-hidden="true"></i>
                    <div>
                        <h3>الصورة الشخصية</h3>
                        <p>اختيارية. تُعرَض لمسؤول النظام والمشرف الأكاديمي.</p>
                    </div>
                </header>

                {{-- الصورة تتصدّر مركزيةً لا مزاحمةً للأزرار في صفّ
                     ضيّق. ومنطقة الإفلات هي الصورة نفسها: النقر عليها
                     يفتح المستعرض، وسحب ملفّ فوقها يرفعه. --}}
                <div class="profile-form avatar-card">
                    <form action="{{ $avatarStore }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                        @csrf
                        <input type="file" name="avatar" id="avatar-input"
                            accept="image/jpeg,image/png,image/webp" class="visually-hidden">

                        <label for="avatar-input" class="avatar-drop" id="avatar-drop">
                            <x-avatar :user="$user" class="profile-avatar" id="avatar-preview" />
                            <span class="avatar-drop-overlay" aria-hidden="true">
                                <i class="ti ti-camera"></i>
                            </span>
                        </label>

                        <p class="avatar-card-hint" id="avatar-hint">
                            اسحب صورة إلى هنا أو انقر لاختيارها.<br>
                            <span>JPG أو PNG أو WebP · حتى 2 ميغابايت · تُقصّ مربّعة إلى 256 بكسل</span>
                        </p>

                        <button type="submit" class="btn btn-primary w-100 d-none" id="avatar-save">
                            <i class="ti ti-device-floppy me-1" aria-hidden="true"></i>
                            حفظ الصورة
                        </button>
                    </form>

                    @if ($user->avatar_url && $avatarDestroy)
                        <form action="{{ $avatarDestroy }}" method="POST" class="w-100">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost-secondary w-100">
                                <i class="ti ti-trash me-1" aria-hidden="true"></i>
                                إزالة الصورة
                            </button>
                        </form>
                    @endif
                </div>
            </section>
        @endif

        {{-- ═══ الأمان ═══
             نموذج منفصل: كان الحفظ واحداً، فتغيير الاسم يُرسل معه
             حقول كلمة مرور فارغة. --}}
        <section class="profile-section">
            <header class="profile-section-head">
                <i class="ti ti-shield-lock" aria-hidden="true"></i>
                <div>
                    <h3>الأمان</h3>
                    <p>كلمة السر لا تقلّ عن 8 أحرف.</p>
                </div>
            </header>

            <form action="{{ $action }}" method="POST" class="profile-form" id="password-form">
                @csrf
                @method('PUT')
                {{-- الحقول المطلوبة تُرسل كما هي: القواعد تسري على كل
                     تحديث، وهذا النموذج لا يقصد تغييرها --}}
                <input type="hidden" name="phone" value="{{ $user->phone }}">
                @if ($canName)
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                @endif
                @if ($canGender)
                    <input type="hidden" name="gender" value="{{ $user->gender }}">
                @endif

                <div class="mb-3">
                    <label class="form-label" for="p-current">كلمة السر الحالية</label>
                    <div class="password-wrapper">
                        <input id="p-current" type="password" name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                            data-target="p-current"><i class="ti ti-eye"></i></button>
                    </div>
                    @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="p-new">كلمة السر الجديدة</label>
                    <div class="password-wrapper">
                        <input id="p-new" type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" autocomplete="new-password"
                            minlength="8" placeholder="8 أحرف على الأقل">
                        <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                            data-target="p-new"><i class="ti ti-eye"></i></button>
                    </div>
                    {{-- مقياس القوة: تغذية راجعة أثناء الكتابة بدل
                         رسالة خطأ بعد الإرسال --}}
                    <div class="pw-meter" aria-hidden="true">
                        <span class="pw-meter-bar"><span id="pw-fill"></span></span>
                        <span class="pw-meter-label" id="pw-label">أدخل كلمة سر</span>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="p-confirm">تأكيد كلمة السر</label>
                    <div class="password-wrapper">
                        <input id="p-confirm" type="password" name="password_confirmation" class="form-control"
                            autocomplete="new-password" placeholder="أعد كتابة كلمة السر">
                        <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                            data-target="p-confirm"><i class="ti ti-eye"></i></button>
                    </div>
                    <div class="form-hint" id="pw-match"></div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-key me-1" aria-hidden="true"></i>
                    تغيير كلمة السر
                </button>
            </form>
        </section>
    </div>

    {{-- ═══ المعلومات الشخصية ═══ --}}
    <section class="profile-section">
        <header class="profile-section-head">
            <i class="ti ti-user" aria-hidden="true"></i>
            <div>
                <h3>المعلومات الشخصية</h3>
                <p>ما يظهر لك ولمن يراك في النظام.</p>
            </div>
        </header>

        <form action="{{ $action }}" method="POST" class="profile-form">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="p-name">الاسم</label>
                @if ($canName)
                    <input id="p-name" type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}" required maxlength="70">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @else
                    <div class="field-locked">
                        <span>{{ $user->name }}</span>
                        <i class="ti ti-lock" aria-hidden="true"></i>
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label" for="p-email">البريد الإلكتروني</label>
                @if ($canEmail)
                    <input id="p-email" type="email" name="email" dir="ltr"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">هو اسم الدخول. تغييره يتطلّب كلمة السر الحالية.</div>
                @else
                    <div class="field-locked" dir="ltr">
                        <span>{{ $user->email }}</span>
                        <i class="ti ti-lock" aria-hidden="true"></i>
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label" for="p-phone">رقم الجوال</label>
                <input id="p-phone" type="tel" name="phone" dir="ltr" inputmode="numeric" maxlength="10"
                    class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $user->phone) }}" required>
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-hint">10 أرقام، تبدأ بصفر.</div>
            </div>

            @if ($canGender)
                <div class="mb-3">
                    <label class="form-label" for="p-gender">الجنس</label>
                    <select id="p-gender" name="gender" class="form-select">
                        <option value="male" @selected(old('gender', $user->gender) === 'male')>ذكر</option>
                        <option value="female" @selected(old('gender', $user->gender) === 'female')>أنثى</option>
                    </select>
                </div>
            @endif

            {{-- البيانات التي تصدرها الجامعة: تظهر مقفلة لا مخفيّة --}}
            @foreach ($facts as $label => $value)
                <div class="mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <div class="field-locked">
                        <span>{{ $value }}</span>
                        <i class="ti ti-lock" aria-hidden="true"></i>
                    </div>
                </div>
            @endforeach

            @if (count($facts) || ! $canName)
                <p class="profile-locked-note">
                    <i class="ti ti-info-circle" aria-hidden="true"></i>
                    الحقول المقفلة تصدرها الجامعة. راجع مسؤول النظام لتعديلها.
                </p>
            @endif

            @if ($canEmail)
                <div class="mb-3">
                    <label class="form-label" for="p-current-info">كلمة السر الحالية</label>
                    <input id="p-current-info" type="password" name="current_password" class="form-control"
                        autocomplete="current-password" placeholder="تُطلب فقط عند تغيير البريد">
                    @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1" aria-hidden="true"></i>
                حفظ المعلومات
            </button>
        </form>
    </section>
</div>

@push('js')
    <script>
        (function () {
            // معاينة حيّة قبل الرفع: يرى المستخدم ما سيُحفظ بدل أن
            // يرفع ثم يكتشف
            var input = document.getElementById('avatar-input');
            var preview = document.getElementById('avatar-preview');
            var save = document.getElementById('avatar-save');
            var drop = document.getElementById('avatar-drop');
            var hint = document.getElementById('avatar-hint');

            if (!input || !preview) return;

            function show(file) {
                if (!file || !/^image\//.test(file.type)) return;

                preview.classList.add('has-photo');
                preview.innerHTML = '<img alt="">';
                preview.querySelector('img').src = URL.createObjectURL(file);

                if (save) save.classList.remove('d-none');
                if (hint) hint.textContent = 'اضغط «حفظ الصورة» لتأكيد الرفع.';
            }

            input.addEventListener('change', function () {
                show(input.files && input.files[0]);
            });

            // السحب والإفلات: أسرع من فتح مستعرض الملفات، وهو ما
            // يتوقّعه المستخدم من أي واجهة حديثة
            if (drop) {
                ['dragenter', 'dragover'].forEach(function (e) {
                    drop.addEventListener(e, function (ev) {
                        ev.preventDefault();
                        drop.classList.add('is-over');
                    });
                });

                ['dragleave', 'drop'].forEach(function (e) {
                    drop.addEventListener(e, function (ev) {
                        ev.preventDefault();
                        drop.classList.remove('is-over');
                    });
                });

                drop.addEventListener('drop', function (ev) {
                    var file = ev.dataTransfer && ev.dataTransfer.files[0];
                    if (!file) return;

                    // الملف يُسند إلى الحقل نفسه حتى يُرسل مع النموذج
                    input.files = ev.dataTransfer.files;
                    show(file);
                });
            }
        })();

        (function () {
            // إظهار/إخفاء كلمة السر يتكفّل به مُلتقِط عام في القالب
            // (layouts/admin/admin.blade.php) — وربطٌ ثانٍ هنا كان
            // سيقلب الحقل مرتين فلا يتغيّر شيء.

            var pw = document.getElementById('p-new');
            var confirmField = document.getElementById('p-confirm');
            var fill = document.getElementById('pw-fill');
            var label = document.getElementById('pw-label');
            var match = document.getElementById('pw-match');
            if (!pw || !fill) return;

            // مقياس تقريبي: الطول والتنوّع. ليس تحقّقاً — الخادم هو
            // من يقرّر — بل تغذية راجعة أثناء الكتابة.
            function score(v) {
                if (!v) return 0;
                var s = 0;
                if (v.length >= 8) s++;
                if (v.length >= 12) s++;
                if (/[0-9]/.test(v) && /[a-zA-Z؀-ۿ]/.test(v)) s++;
                if (/[^a-zA-Z0-9؀-ۿ]/.test(v)) s++;
                return s;
            }

            var levels = [
                { w: '0%',   c: '',          t: 'أدخل كلمة سر' },
                { w: '25%',  c: 'is-weak',   t: 'ضعيفة' },
                { w: '50%',  c: 'is-fair',   t: 'مقبولة' },
                { w: '75%',  c: 'is-good',   t: 'جيدة' },
                { w: '100%', c: 'is-strong', t: 'قوية' }
            ];

            function checkMatch() {
                if (!confirmField || !match) return;
                if (!confirmField.value) { match.textContent = ''; match.className = 'form-hint'; return; }
                var ok = confirmField.value === pw.value;
                match.textContent = ok ? 'متطابقتان' : 'غير متطابقتين';
                match.className = 'form-hint ' + (ok ? 'text-success' : 'text-danger');
            }

            function render() {
                var lvl = levels[score(pw.value)];
                fill.style.width = lvl.w;
                fill.className = lvl.c;
                label.textContent = pw.value && pw.value.length < 8 ? 'قصيرة — 8 أحرف على الأقل' : lvl.t;
                checkMatch();
            }

            pw.addEventListener('input', render);
            if (confirmField) confirmField.addEventListener('input', checkMatch);
            render();
        })();
    </script>
@endpush
