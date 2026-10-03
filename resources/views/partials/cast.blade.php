{{--
    شخصيات «رحلة المشروع» — مصدر واحد للصفحة الرئيسية وصفحات الدخول واللوحات.
    عناصر <g> تُضمَّن داخل <defs> ثم تُستعمل بـ <use href="#hj-…">.
    القدمان عند (0,0) والجسم إلى الأعلى. أرجل ‎.hj-leg‎ وأذرع ‎.hj-arm‎
    تتحرّك حين يحمل عنصرٌ يحوي هذه الـ defs الصنفَ ‎.is-walking‎.
--}}
{{-- ===== الشخصيات: القدمان عند (0,0) والجسم إلى الأعلى ===== --}}
{{-- طالب بقميص أزرق وحاسوب --}}
<g id="hj-boy">
    <ellipse cx="0" cy="1" rx="13" ry="3" fill="#0f172a" opacity=".12" />
    <rect class="hj-leg is-a" x="-7" y="-26" width="6" height="26" rx="3" fill="#1e293b" />
    <rect class="hj-leg is-b" x="1" y="-26" width="6" height="26" rx="3" fill="#334155" />
    <rect x="-11" y="-53" width="22" height="31" rx="9" fill="#2563eb" />
    <rect class="hj-arm" x="-15" y="-50" width="6" height="21" rx="3" fill="#1d4ed8" />
    <rect x="6" y="-46" width="14" height="17" rx="2" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1" />
    <circle cx="13" cy="-37.5" r="2" fill="#93c5fd" />
    <circle cx="0" cy="-63" r="10" fill="#f1c7a3" />
    <path d="M-10.5 -63 a10.5 10.5 0 0 1 21 0 q-3 -4 -8 -3.5 q-6 .5 -13 3.5z" fill="#1f2937" />
    <circle cx="4" cy="-63" r="1.3" fill="#1f2937" />
</g>
{{-- طالبة بحجاب بنفسجي ودفتر --}}
<g id="hj-girl">
    <ellipse cx="0" cy="1" rx="13" ry="3" fill="#0f172a" opacity=".12" />
    <rect class="hj-leg is-a" x="-7" y="-22" width="6" height="22" rx="3" fill="#475569" />
    <rect class="hj-leg is-b" x="1" y="-22" width="6" height="22" rx="3" fill="#64748b" />
    <path d="M-13 -20 q0 -32 13 -34 q13 2 13 34z" fill="#0d9488" />
    <rect class="hj-arm" x="-15" y="-48" width="6" height="20" rx="3" fill="#0f766e" />
    <rect x="7" y="-44" width="11" height="14" rx="2" fill="#fde68a" stroke="#d97706" stroke-width="1" />
    <path d="M-12 -60 a12 12 0 0 1 24 0 v6 q-12 8 -24 0z" fill="#7c3aed" />
    <circle cx="1.5" cy="-61" r="7.5" fill="#f1c7a3" />
    <path d="M-12 -62 a12 12.5 0 0 1 24 0 q-4 -6 -12 -6.5 q-8 .5 -12 6.5z" fill="#7c3aed" />
    <circle cx="5" cy="-61" r="1.2" fill="#1f2937" />
</g>
{{-- طالب بشعر مجعّد وحقيبة ظهر --}}
<g id="hj-boy2">
    <ellipse cx="0" cy="1" rx="13" ry="3" fill="#0f172a" opacity=".12" />
    <rect class="hj-leg is-a" x="-7" y="-26" width="6" height="26" rx="3" fill="#0f172a" />
    <rect class="hj-leg is-b" x="1" y="-26" width="6" height="26" rx="3" fill="#1e293b" />
    <rect x="-17" y="-50" width="9" height="20" rx="3" fill="#b45309" />
    <rect x="-11" y="-53" width="22" height="31" rx="9" fill="#f59e0b" />
    <rect class="hj-arm" x="7" y="-50" width="6" height="21" rx="3" fill="#d97706" />
    <circle cx="0" cy="-63" r="10" fill="#c99a76" />
    <circle cx="-6" cy="-70" r="4.5" fill="#111827" /><circle cx="0" cy="-73" r="5" fill="#111827" />
    <circle cx="6" cy="-70" r="4.5" fill="#111827" /><circle cx="-9" cy="-64" r="3.5" fill="#111827" />
    <circle cx="4" cy="-63" r="1.3" fill="#1f2937" />
</g>
{{-- المشرف: سترة كحلية ونظّارة ولوح ملاحظات --}}
<g id="hj-supervisor">
    <ellipse cx="0" cy="1" rx="15" ry="3.5" fill="#0f172a" opacity=".12" />
    <rect x="-8" y="-30" width="7" height="30" rx="3" fill="#1e293b" />
    <rect x="1" y="-30" width="7" height="30" rx="3" fill="#1e293b" />
    <rect x="-13" y="-62" width="26" height="36" rx="10" fill="#1e3a8a" />
    <path d="M-4 -62 l4 12 l4 -12z" fill="#fff" />
    <rect x="-17" y="-58" width="6" height="24" rx="3" fill="#1e3a8a" />
    <g class="hj-wave"><rect x="11" y="-60" width="6" height="22" rx="3" fill="#1e3a8a" /></g>
    <rect x="12" y="-46" width="14" height="18" rx="2" fill="#fff" stroke="#cbd5e1" stroke-width="1.2" />
    <path d="M15 -41h8M15 -37h8M15 -33h5" stroke="#94a3b8" stroke-width="1.2" />
    <circle cx="0" cy="-73" r="11" fill="#e0b58f" />
    <path d="M-11 -75 a11 11 0 0 1 22 0 q-5 -3 -11 -3 q-6 0 -11 3z" fill="#6b7280" />
    <path d="M-8 -68 q8 9 16 0 v3 q-8 9 -16 0z" fill="#6b7280" />
    <circle cx="-3.5" cy="-74" r="3" fill="none" stroke="#111827" stroke-width="1.2" />
    <circle cx="4.5" cy="-74" r="3" fill="none" stroke="#111827" stroke-width="1.2" />
</g>
{{-- مسؤول القسم: قميص بنفسجي وبطاقة معلّقة ولوح بيده --}}
<g id="hj-admin">
    <ellipse cx="0" cy="1" rx="15" ry="3.5" fill="#0f172a" opacity=".12" />
    <rect class="hj-leg is-a" x="-8" y="-29" width="7" height="29" rx="3" fill="#312e81" />
    <rect class="hj-leg is-b" x="1" y="-29" width="7" height="29" rx="3" fill="#3730a3" />
    <rect x="-13" y="-60" width="26" height="35" rx="10" fill="#7c3aed" />
    <path d="M-5 -60 l5 9 l5 -9z" fill="#ede9fe" />
    <path d="M-5 -59 L0 -44 L5 -59" fill="none" stroke="#facc15" stroke-width="1.6" />
    <rect x="-3" y="-45" width="6" height="7" rx="1.5" fill="#fff" stroke="#facc15" stroke-width="1" />
    <rect class="hj-arm" x="-17" y="-57" width="6" height="22" rx="3" fill="#6d28d9" />
    <rect x="11" y="-56" width="6" height="16" rx="3" fill="#6d28d9" />
    <rect x="10" y="-45" width="17" height="13" rx="2" fill="#1e293b" />
    <rect x="12" y="-43" width="13" height="9" rx="1" fill="#60a5fa" />
    <circle cx="0" cy="-71" r="10.5" fill="#d9a77e" />
    <path d="M-10.5 -72 a10.5 10.5 0 0 1 21 0 q-2 -6 -10.5 -6 q-8.5 0 -10.5 6z" fill="#3f2a1d" />
    <circle cx="4" cy="-71" r="1.3" fill="#1f2937" />
    <path d="M1 -66 q3 2 6 0" fill="none" stroke="#7c2d12" stroke-width="1.2" stroke-linecap="round" />
</g>
{{-- لجنة المناقشة: عضوان خلف طاولة --}}
<g id="hj-committee">
    <ellipse cx="0" cy="1" rx="40" ry="4" fill="#0f172a" opacity=".12" />
    <circle cx="-17" cy="-52" r="9" fill="#e0b58f" /><path d="M-26 -53 a9 9 0 0 1 18 0 q-9 -4 -18 0z" fill="#374151" />
    <rect x="-28" y="-43" width="22" height="22" rx="8" fill="#334155" />
    <circle cx="17" cy="-52" r="9" fill="#f1c7a3" /><path d="M7 -55 a10 10 0 0 1 20 0 v6 q-10 6 -20 0z" fill="#be185d" />
    <rect x="6" y="-43" width="22" height="22" rx="8" fill="#9d174d" />
    <rect x="-38" y="-26" width="76" height="26" rx="4" fill="#e2e8f0" stroke="#cbd5e1" />
    <rect x="-14" y="-21" width="28" height="9" rx="2" fill="#2563eb" opacity=".85" />
</g>
{{-- قبعة تخرّج --}}
<g id="hj-cap">
    <path d="M0 -8 L16 0 L0 8 L-16 0z" fill="#111827" />
    <rect x="-8" y="2" width="16" height="6" rx="2" fill="#1f2937" />
    <path d="M10 1 v9" stroke="#f59e0b" stroke-width="1.6" stroke-linecap="round" />
    <circle cx="10" cy="11" r="1.8" fill="#f59e0b" />
</g>
