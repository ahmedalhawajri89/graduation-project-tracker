{{--
    مسار المنصة خلف بطاقة الدخول — مشترك بين الدخول والاسترجاع.

    منحنى واحد يدخل من الحافة، يمرّ بمحطّتين تمّتا، ثم خلف البطاقة (حيث
    يقف الداخل الآن: لوحته)، ثم بمحطّتين قادمتين ويخرج. الجزء الذي مضى
    أزرق متصل، والقادم متقطّع رمادي، ونقطة ضوء تسري عليه.
    الإحداثيات بنظام 1440×900 والمحطّات بنسبها المئوية نفسها؛ في LTR
    يُعكس الرسم والمحطّات معاً (inset-inline-start).
--}}
<div class="auth-path" aria-hidden="true">
    <span class="auth-grid"></span>

    <svg class="auth-route" viewBox="0 0 1440 900" preserveAspectRatio="none">
        <defs>
            <linearGradient id="auth-route-lit" x1="1" y1="0" x2="0" y2="0">
                <stop offset="0" stop-color="#93c5fd" stop-opacity="0" />
                <stop offset=".25" stop-color="#60a5fa" />
                <stop offset="1" stop-color="#2563eb" />
            </linearGradient>
        </defs>
        @php($route = 'M1500,170 C1380,190 1290,230 1230,300 C1160,380 1150,560 1060,610 C940,680 820,470 720,450 C620,430 470,220 380,290 C300,350 270,560 200,600 C140,640 40,700 -60,720')
        <path class="route-todo" d="{{ $route }}" pathLength="100" />
        <path class="route-done" d="{{ $route }}" pathLength="100" stroke="url(#auth-route-lit)" />
        <circle class="route-spark" r="4">
            <animateMotion dur="7s" repeatCount="indefinite" keyPoints="0;1" keyTimes="0;1" calcMode="linear" path="{{ $route }}" />
        </circle>
    </svg>

    @foreach ([
        ['n' => 1, 'x' => 14.6, 'y' => 33.3, 'state' => 'done', 'label' => __('تقديم الطلب'),   'side' => 'below'],
        ['n' => 2, 'x' => 26.4, 'y' => 67.8, 'state' => 'done', 'label' => __('موافقة المشرف'), 'side' => 'below'],
        ['n' => 3, 'x' => 73.6, 'y' => 32.2, 'state' => 'next', 'label' => __('متابعة التنفيذ'), 'side' => 'above'],
        ['n' => 4, 'x' => 86.1, 'y' => 66.7, 'state' => 'todo', 'label' => __('المناقشة والتقييم'), 'side' => 'below'],
    ] as $st)
        {{-- المرساة بلا أبعاد عند نقطة المنحنى؛ الدائرة والبطاقة تتوسّطانها --}}
        <span class="station is-{{ $st['state'] }} is-{{ $st['side'] }} s{{ $st['n'] }}"
            style="inset-inline-start: {{ $st['x'] }}%; top: {{ $st['y'] }}%;">
            <i class="station-dot">
                @if ($st['state'] === 'done')
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                @else
                    {{ $st['n'] }}
                @endif
            </i>
            <span class="station-card">
                <b class="step">{{ $st['label'] }}</b>
                <small>{{ ['done' => __('تمّت'), 'next' => __('التالية'), 'todo' => __('لاحقاً')][$st['state'] ] }}</small>
            </span>
        </span>
    @endforeach

    {{-- شخصيات الصفحة الرئيسية على المسار نفسه: الفريق يمشي من الحافة حتى
         المشرف الواقف عند «موافقة المشرف»، واللجنة تنتظر عند «المناقشة».
         المواضع تُحسب على المنحنى في public/js/auth-cast.js. الـ defs داخل
         ‎.auth-path‎ لتتحرّك الأرجل حين يحمل الصنف is-walking. --}}
    <svg class="cast-defs" width="0" height="0" focusable="false"><defs>@include('partials.cast')</defs></svg>
    <div class="auth-cast" data-auth-cast data-route="{{ $route }}">
        <svg class="cast-actor is-supervisor" data-near="1060,610" data-dt=".045" viewBox="-30 -98 60 102"><use href="#hj-supervisor" /></svg>
        <svg class="cast-actor is-committee" data-near="200,600" data-dt="-.022" viewBox="-44 -70 88 74"><use href="#hj-committee" /></svg>
        {{-- الفريق: كل طالب على المنحنى بمفرده، واحداً خلف الآخر --}}
        <div class="cast-team" data-near="1060,610" data-dt="-.028">
            @foreach (['hj-girl', 'hj-boy', 'hj-boy2'] as $who)
                <svg class="cast-walker" viewBox="-30 -98 60 102"><use href="#{{ $who }}" /></svg>
            @endforeach
        </div>
    </div>
</div>
<script src="{{ asset('js/auth-cast.js') }}?v={{ filemtime(public_path('js/auth-cast.js')) }}" defer></script>
