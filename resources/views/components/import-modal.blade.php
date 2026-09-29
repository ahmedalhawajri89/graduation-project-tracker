@props([
    'action',
    'template',
    // الأعمدة: \u200E[اسم العمود، مطلوب؟، الشرح]\u200E
    'columns' => [],
    'noun' => 'السجلات',
    'specializes' => collect(),
])

{{--
    استيراد من Excel — منطقة إفلات، وقالب يُولَّد بأعمدته، ودليل أعمدة.

    كانت حقل ملف عاديّاً وزرّ «حمل نموذج البيانات» لملف ثابت واحد للطلاب
    والمشرفين، بلا قول ما المطلوب في كل عمود — فيُرفض الصفّ ويُكتشف السبب
    في تقرير الاستيراد بعد الرفع. تقرير الاستيراد نفسه باقٍ كما هو.
--}}

@php $failed = $errors->has('attachment'); @endphp

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content im-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="importLabel">استيراد {{ $noun }} من Excel</h5>
                    <p class="im-sub">تُنشأ الحسابات دفعة واحدة، ويُعرض بعد الرفع تقرير بما أُضيف وما تُخطّي ولماذا.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" data-import-form>
                @csrf
                <div class="modal-body">
                    <ol class="im-steps">
                        <li>
                            <span class="im-step-n">1</span>
                            <div>
                                <b>نزّل القالب</b>
                                <span>بأعمدته الصحيحة وصفّ مثال — احذف المثال واملأ بياناتك.</span>
                            </div>
                            <a href="{{ $template }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-file-download me-1" aria-hidden="true"></i>
                                القالب
                            </a>
                        </li>
                        <li>
                            <span class="im-step-n">2</span>
                            <div>
                                <b>ارفع الملف</b>
                                <span>xlsx أو xls، حتى 5MB.</span>
                            </div>
                        </li>
                    </ol>

                    {{-- منطقة الإفلات: الـ input نفسه يغطّيها، فالنقر والإفلات ولوحة المفاتيح تعمل بلا سكربت --}}
                    <label class="im-drop {{ $failed ? 'is-invalid' : '' }}" data-drop>
                        <input type="file" name="attachment" accept=".xlsx,.xls" required data-drop-input
                            aria-describedby="im-drop-hint">
                        <span class="im-drop-empty">
                            <i class="ti ti-cloud-upload" aria-hidden="true"></i>
                            <b>اسحب الملف إلى هنا</b>
                            <span id="im-drop-hint">أو انقر لاختياره من جهازك</span>
                        </span>
                        <span class="im-drop-file" hidden>
                            <span class="im-file-badge" aria-hidden="true">XLS</span>
                            <span class="im-file-meta">
                                <b data-drop-name></b>
                                <small data-drop-size dir="ltr"></small>
                            </span>
                            <button type="button" class="ed-link" data-drop-clear>تغيير</button>
                        </span>
                    </label>
                    @if ($failed)
                        <div class="invalid-feedback d-block">{{ $errors->first('attachment') }}</div>
                    @endif
                    <div class="im-drop-error" data-drop-error hidden>الملف يجب أن يكون xlsx أو xls وحجمه حتى 5MB.</div>

                    <details class="im-guide">
                        <summary>
                            <i class="ti ti-table" aria-hidden="true"></i>
                            الأعمدة وما يُكتب فيها
                        </summary>
                        <table>
                            <thead><tr><th>العمود</th><th></th><th>ما يُكتب فيه</th></tr></thead>
                            <tbody>
                                @foreach ($columns as [$col, $required, $text])
                                    <tr>
                                        <td><code dir="ltr">{{ $col }}</code></td>
                                        <td>
                                            @if ($required)
                                                <span class="im-req">مطلوب</span>
                                            @else
                                                <span class="im-opt">اختياري</span>
                                            @endif
                                        </td>
                                        <td>{{ $text }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($specializes->count())
                            <p class="im-specs">
                                <span>أسماء التخصصات كما تُكتب في عمود <code dir="ltr">specialization</code>:</span>
                                @foreach ($specializes as $specialize)
                                    <span class="im-spec">{{ $specialize->name }}</span>
                                @endforeach
                            </p>
                        @endif
                    </details>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" data-import-submit disabled>
                        <span class="ed-spinner" aria-hidden="true"></span>
                        <i class="ti ti-upload me-1" aria-hidden="true"></i>
                        استيراد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
    <script>
        (function () {
            var form = document.querySelector('[data-import-form]');
            if (!form) return;
            var drop = form.querySelector('[data-drop]');
            var input = form.querySelector('[data-drop-input]');
            var submit = form.querySelector('[data-import-submit]');
            var error = form.querySelector('[data-drop-error]');
            var MAX = 5 * 1024 * 1024;

            function size(bytes) {
                return bytes < 1024 * 1024 ? Math.max(1, Math.round(bytes / 1024)) + ' KB' : (bytes / 1024 / 1024).toFixed(1) + ' MB';
            }

            // الملف المختار يحلّ محلّ الدعوة — باسمه وحجمه، ويُرفض الخطأ قبل الرفع
            function show() {
                var file = input.files && input.files[0];
                var ok = !!file && /\.xlsx?$/i.test(file.name) && file.size <= MAX;
                drop.querySelector('.im-drop-empty').hidden = !!file;
                drop.querySelector('.im-drop-file').hidden = !file;
                drop.classList.toggle('has-file', !!file);
                drop.classList.toggle('is-invalid', !!file && !ok);
                error.hidden = !file || ok;
                submit.disabled = !ok;
                if (file) {
                    form.querySelector('[data-drop-name]').textContent = file.name;
                    form.querySelector('[data-drop-size]').textContent = size(file.size);
                }
            }

            input.addEventListener('change', show);
            ['dragenter', 'dragover'].forEach(function (t) {
                drop.addEventListener(t, function () { drop.classList.add('is-over'); });
            });
            ['dragleave', 'drop'].forEach(function (t) {
                drop.addEventListener(t, function () { drop.classList.remove('is-over'); });
            });
            form.querySelector('[data-drop-clear]').addEventListener('click', function (e) {
                e.preventDefault();
                input.value = '';
                show();
                input.click();
            });
            form.addEventListener('submit', function () {
                submit.classList.add('is-saving');
            });

            // فشل التحقّق (نوع الملف أو حجمه): تُعاد فتح النافذة بالرسالة
            @if ($failed)
                new bootstrap.Modal(document.getElementById('importModal')).show();
            @endif
        })();
    </script>
@endpush
