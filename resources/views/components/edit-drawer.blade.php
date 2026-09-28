@props([
    'id' => 'editDrawer',
    'title',
    'action',
    // السجلّ الذي فشل تحقّقه: \u200E['record' => EditRecord::…, 'old' => old()]\u200E — يُعاد فتح الدرج به
    'reopen' => null,
    // إزالة الصورة من الدرج — للطالب والمشرف (المتحكّم يقرأ \u200Eremove_avatar\u200E)
    'avatarRemoval' => false,
])

{{--
    درج التعديل — للطالب والمشرف والمسؤول.

    كان نافذة وسطية بسبعة حقول متساوية في عمود، لا تقول مَن يُعدَّل، و«حفظ»
    نشط دائماً، وتُغلق عند فشل التحقّق فتضيع رسالة الخطأ. الدرج يُبقي الجدول
    ظاهراً خلفه، ورأسه هوية السجلّ، والحقول في مجموعات، والتذييل يعدّ
    التغييرات. السلوك في \u200Epublic/js/edit-drawer.js\u200E.
--}}

<div class="offcanvas offcanvas-start ed-drawer" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}-title"
    data-edit-drawer
    @if ($reopen) data-reopen="{{ json_encode($reopen, JSON_UNESCAPED_UNICODE) }}" @endif>
    <form action="{{ $action }}" method="POST" class="ed-form" data-ed-form>
        @csrf
        @method('put')
        <input type="hidden" name="id">

        <header class="ed-head">
            <div class="ed-head-top">
                <h2 class="ed-title" id="{{ $id }}-title">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
            </div>

            <div class="ed-identity">
                <span class="ed-avatar" data-ed-avatar aria-hidden="true"></span>
                <span class="ed-who">
                    <b data-ed-name></b>
                    <span dir="ltr" data-ed-sub></span>
                </span>
                <span class="ed-status" data-ed-status hidden></span>
            </div>

            @if ($avatarRemoval)
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
            <span class="ed-changes" data-ed-changes aria-live="polite">لا تغييرات</span>
            <button type="button" class="btn" data-bs-dismiss="offcanvas">إلغاء</button>
            <button type="submit" class="btn btn-primary" data-ed-save disabled>
                <span class="ed-spinner" aria-hidden="true"></span>
                <i class="ti ti-device-floppy me-1" aria-hidden="true"></i>
                حفظ التغييرات
            </button>
        </footer>
    </form>
</div>

@once
    @push('js')
        <script src="{{ asset('js/edit-drawer.js') }}"></script>
    @endpush
@endonce
