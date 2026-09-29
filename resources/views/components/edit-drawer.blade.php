@props([
    'id' => 'editDrawer',
    'title',
    'action',
    // edit: سجلّ قائم (\u200EPUT\u200E ومعرّف) — create: سجلّ جديد (\u200EPOST\u200E ومعاينة حيّة للرأس)
    'mode' => 'edit',
    // إعادة الفتح بعد فشل التحقّق أو بعد «حفظ وإضافة آخر»:
    // \u200E['record' => EditRecord::… أو [], 'old' => old()]\u200E
    'reopen' => null,
    // إزالة الصورة من الدرج — للطالب والمشرف في التعديل (المتحكّم يقرأ \u200Eremove_avatar\u200E)
    'avatarRemoval' => false,
    // اسم السجلّ في زرّ الإضافة: «إضافة طالب»
    'noun' => null,
])

{{--
    درج التعديل والإضافة — للطالب والمشرف والمسؤول.

    كانت نوافذ وسطية بسبعة حقول متساوية في عمود، لا تقول مَن يُعدَّل، و«حفظ»
    نشط دائماً، وتُغلق عند فشل التحقّق فتضيع رسالة الخطأ. الدرج يُبقي الجدول
    ظاهراً خلفه، ورأسه هوية السجلّ (في الإضافة: تتكوّن أثناء الكتابة)، والحقول
    في مجموعات، والتذييل يعدّ التغييرات. السلوك في \u200Epublic/js/edit-drawer.js\u200E.
--}}

@php $create = $mode === 'create'; @endphp

<div class="offcanvas offcanvas-start ed-drawer" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}-title"
    data-edit-drawer data-mode="{{ $mode }}"
    @if ($reopen) data-reopen="{{ json_encode($reopen, JSON_UNESCAPED_UNICODE) }}" @endif>
    <form action="{{ $action }}" method="POST" class="ed-form" data-ed-form>
        @csrf
        @unless ($create)
            @method('put')
            <input type="hidden" name="id">
        @endunless

        <header class="ed-head">
            <div class="ed-head-top">
                <h2 class="ed-title" id="{{ $id }}-title">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
            </div>

            <div class="ed-identity">
                <span class="ed-avatar {{ $create ? 'is-new' : '' }}" data-ed-avatar aria-hidden="true">
                    @if ($create)<i class="ti ti-user-plus"></i>@endif
                </span>
                <span class="ed-who">
                    <b data-ed-name @if ($create) data-placeholder="الاسم يظهر هنا" @endif>{{ $create ? 'الاسم يظهر هنا' : '' }}</b>
                    <span dir="ltr" data-ed-sub></span>
                </span>
                <span class="ed-status" data-ed-status hidden></span>
            </div>

            @if ($avatarRemoval && ! $create)
                <div class="ed-avatar-actions" data-ed-avatar-actions hidden>
                    <input type="hidden" name="remove_avatar" value="0" data-ed-remove>
                    <button type="button" class="ed-link is-danger" data-ed-remove-toggle>
                        <i class="ti ti-photo-x" aria-hidden="true"></i>
                        إزالة الصورة الشخصية
                    </button>
                    <span class="ed-remove-note" data-ed-remove-note hidden>
                        <i class="ti ti-info-circle" aria-hidden="true"></i>
                        ستُزال الصورة عند الحفظ ·
                        <button type="button" class="ed-link" data-ed-remove-undo>تراجع</button>
                    </span>
                </div>
            @endif
        </header>

        <div class="ed-body">
            {{ $slot }}
        </div>

        <footer class="ed-foot">
            <span class="ed-changes" data-ed-changes aria-live="polite">{{ $create ? 'الحقول المعلَّمة بـ * مطلوبة' : 'لا تغييرات' }}</span>
            <button type="button" class="btn" data-bs-dismiss="offcanvas">إلغاء</button>
            @if ($create)
                {{-- لإدخال دفعة يدوياً: يحفظ ثم يعيد فتح الدرج فارغاً --}}
                <button type="submit" class="btn btn-outline-primary" name="another" value="1" data-ed-save-another>
                    حفظ وإضافة آخر
                </button>
            @endif
            <button type="submit" class="btn btn-primary" data-ed-save @unless ($create) disabled @endunless>
                <span class="ed-spinner" aria-hidden="true"></span>
                <i class="ti {{ $create ? 'ti-plus' : 'ti-device-floppy' }} me-1" aria-hidden="true"></i>
                {{ $create ? 'إضافة' . ($noun ? ' ' . $noun : '') : 'حفظ التغييرات' }}
            </button>
        </footer>
    </form>
</div>

@once
    @push('js')
        <script src="{{ asset('js/edit-drawer.js') }}"></script>
    @endpush
@endonce
