{{--
    نصّ طويل يُطوى بعد أربعة أسطر. «المزيد» يظهر فقط حين يُقصّ النصّ
    فعلاً — يُقاس في المتصفّح لا يُخمَّن بعدد الأحرف.

    @param string $text
--}}

<div class="clamp" data-clamp>
    <p class="proj-desc clamp-text">{{ $text }}</p>
    <button type="button" class="clamp-toggle" hidden aria-expanded="false">المزيد</button>
</div>

@once
    @push('js')
        <script>
            document.querySelectorAll('[data-clamp]').forEach(function (box) {
                var text = box.querySelector('.clamp-text');
                var btn = box.querySelector('.clamp-toggle');
                if (text.scrollHeight <= text.clientHeight + 2) return;

                btn.hidden = false;
                btn.addEventListener('click', function () {
                    var open = box.classList.toggle('is-open');
                    btn.textContent = open ? 'أقلّ' : 'المزيد';
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });
        </script>
    @endpush
@endonce
