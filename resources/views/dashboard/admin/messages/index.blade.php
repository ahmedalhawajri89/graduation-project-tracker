@extends('layouts.admin.admin')
@section('title', 'رسائل الاستفسار')

@section('crumbs')
    <x-crumb>رسائل الاستفسار</x-crumb>
@endsection

@section('content')

    @use('App\Support\ContactTopic')

    @php
        // الفلاتر تُحمل في كل رابط: فتح رسالة لا يُفقد البحث ولا التبويب ولا الموضوع
        $keep = request()->except(['open', 'page']);
        $link = fn (array $over = [], array $drop = []) => route('admin.contact.index', array_merge(array_diff_key($keep, array_flip($drop)), $over));
        $sender = fn ($m) => $senders[mb_strtolower($m->email)] ?? ['role' => 'guest', 'label' => 'زائر', 'meta' => [], 'link' => null, 'link_label' => null, 'link_hint' => null];

        // مجموعات القائمة بحسب اليوم
        $dayOf = function ($date) {
            if (! $date) return 'أقدم';
            if ($date->isToday()) return 'اليوم';
            if ($date->isYesterday()) return 'أمس';
            if ($date->gte(now()->subDays(7)->startOfDay())) return 'هذا الأسبوع';
            if ($date->gte(now()->subDays(30)->startOfDay())) return 'هذا الشهر';
            return 'أقدم';
        };
    @endphp

    <x-page-header title="رسائل الاستفسار"
        subtitle="{{ $totalCount }} رسالة · {{ $unreadCount }} غير مقروءة">
        <x-slot:actions>
            @if ($unreadCount > 0)
                {{-- فعل صريح بدل أن يقع تلقائياً بمجرد فتح الصفحة --}}
                <form action="{{ route('admin.contact.readAll') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="ti ti-mail-opened me-1" aria-hidden="true"></i>
                        تعليم الكل كمقروء
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ المؤشّرات: ما ينتظر، وما وصل هذا الأسبوع، ومن يكتب، وعمّ ═══ --}}
    <section class="cx-stats" aria-label="ملخّص الرسائل">
        <a href="{{ $link(['unread' => 1], ['topic']) }}" class="cx-stat {{ $unreadCount ? 'is-live' : '' }}">
            <span class="cx-stat-icon"><i class="ti ti-mail" aria-hidden="true"></i></span>
            <span><b>{{ $unreadCount }}</b><small>غير مقروءة</small></span>
        </a>
        <div class="cx-stat">
            <span class="cx-stat-icon"><i class="ti ti-calendar-week" aria-hidden="true"></i></span>
            <span><b>{{ $stats['week'] }}</b><small>هذا الأسبوع</small></span>
        </div>
        <div class="cx-stat">
            <span class="cx-stat-icon"><i class="ti ti-id-badge-2" aria-hidden="true"></i></span>
            <span><b>{{ $stats['accounts'] }}</b><small>من طلاب ومشرفين</small></span>
        </div>
        <div class="cx-stat">
            @if ($stats['top'])
                @php $tm = ContactTopic::meta($stats['top']['key']); @endphp
                <span class="cx-stat-icon is-{{ $tm['tone'] }}"><i class="ti {{ $tm['icon'] }}" aria-hidden="true"></i></span>
                <span><b class="cx-stat-text">{{ $tm['label'] }}</b><small>الأكثر سؤالاً · {{ $stats['top']['n'] }}</small></span>
            @else
                <span class="cx-stat-icon"><i class="ti ti-message-2" aria-hidden="true"></i></span>
                <span><b>—</b><small>الأكثر سؤالاً</small></span>
            @endif
        </div>
    </section>

    {{-- ═══ لوحان: قائمة وقارئ ═══
         القائمة ومكانها ثابتان بينما تُقرأ الرسالة، كبرامج البريد. --}}
    <div class="inbox-split {{ $openMessage ? 'has-open' : '' }}">

        {{-- ═══ القائمة ═══ --}}
        <aside class="inbox-list-pane">
            <div class="inbox-tools">
                <div class="inbox-tabs">
                    <a href="{{ $link([], ['unread']) }}" class="inbox-tab {{ ! $onlyUnread ? 'is-active' : '' }}">
                        الكل
                        <span class="inbox-tab-n">{{ $totalCount }}</span>
                    </a>
                    <a href="{{ $link(['unread' => 1]) }}" class="inbox-tab {{ $onlyUnread ? 'is-active' : '' }}">
                        غير المقروءة
                        <span class="inbox-tab-n {{ $unreadCount ? 'is-live' : '' }}">{{ $unreadCount }}</span>
                    </a>
                </div>

                <form action="{{ route('admin.contact.index') }}" method="get" class="inbox-search">
                    @if ($onlyUnread)
                        <input type="hidden" name="unread" value="1">
                    @endif
                    @if ($topic)
                        <input type="hidden" name="topic" value="{{ $topic }}">
                    @endif
                    <i class="ti ti-search" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control"
                        placeholder="ابحث في الاسم أو الموضوع أو النصّ…" aria-label="بحث في الرسائل">
                    @if ($q !== '')
                        <a href="{{ $link([], ['q']) }}" class="inbox-search-clear" aria-label="مسح البحث">
                            <i class="ti ti-x" aria-hidden="true"></i>
                        </a>
                    @endif
                </form>

                {{-- الموضوعات: تُستنتج من كلمات الرسالة، ولكلٍّ عدده ضمن البحث والتبويب --}}
                <div class="cx-topics" role="group" aria-label="تصفية حسب الموضوع">
                    <a href="{{ $link([], ['topic']) }}" class="cx-topic {{ ! $topic ? 'is-active' : '' }}">كل المواضيع</a>
                    @foreach ([...array_keys(ContactTopic::TOPICS), 'other'] as $key)
                        @continue(! $topicCounts[$key] && $topic !== $key)
                        @php $tm = ContactTopic::meta($key); @endphp
                        <a href="{{ $link(['topic' => $key]) }}" class="cx-topic is-{{ $tm['tone'] }} {{ $topic === $key ? 'is-active' : '' }}">
                            <i class="ti {{ $tm['icon'] }}" aria-hidden="true"></i>
                            {{ $tm['label'] }}
                            <span>{{ $topicCounts[$key] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($messages->count())
                <div class="inbox-list">
                    @foreach ($messages as $message)
                        @php
                            $isOpen = $openMessage && $openMessage->id === $message->id;
                            $who = $sender($message);
                            $tm = ContactTopic::meta(ContactTopic::of($message));
                            $day = $dayOf($message->created_at);
                        @endphp

                        @if ($loop->first || $day !== $dayOf($messages[$loop->index - 1]->created_at))
                            <div class="cx-day">{{ $day }}</div>
                        @endif

                        <a href="{{ $link(['open' => $message->id]) }}"
                            class="inbox-item {{ ! $message->is_read ? 'is-unread' : '' }} {{ $isOpen ? 'is-active' : '' }}"
                            @if ($isOpen) aria-current="true" @endif>
                            <span class="inbox-item-dot" aria-hidden="true"></span>
                            <span class="msg-avatar cx-av-{{ $who['role'] }}">{{ mb_substr($message->name, 0, 2) }}</span>

                            <span class="inbox-item-body">
                                <span class="inbox-item-top">
                                    <b>{{ $message->name }}</b>
                                    <span class="cx-role cx-role-{{ $who['role'] }}">{{ $who['label'] }}</span>
                                    <time datetime="{{ $message->created_at?->toIso8601String() }}"
                                        title="{{ $message->created_at?->format('Y-m-d H:i') }}">
                                        {{ $message->created_at?->diffForHumans(null, true) }}
                                    </time>
                                </span>
                                <span class="inbox-item-subject">{{ $message->subject }}</span>
                                <span class="inbox-item-preview">{{ \Illuminate\Support\Str::limit($message->message, 72) }}</span>
                                <span class="cx-tag is-{{ $tm['tone'] }}"><i class="ti {{ $tm['icon'] }}" aria-hidden="true"></i>{{ $tm['label'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                @if ($messages->hasPages())
                    <div class="inbox-pages">{!! $messages->links() !!}</div>
                @endif
            @else
                <x-empty-state icon="ti-mail-off"
                    title="{{ $q !== '' || $onlyUnread || $topic ? 'لا رسائل مطابقة' : 'لا توجد رسائل بعد' }}"
                    text="{{ $q !== '' || $onlyUnread || $topic
                        ? 'جرّب كلمة بحث أخرى أو موضوعاً آخر، أو اعرض كل الرسائل.'
                        : 'ستظهر هنا رسائل نموذج التواصل في الموقع العام.' }}"
                    class="py-5">
                    @if ($q !== '' || $onlyUnread || $topic)
                        <x-slot:action>
                            <a href="{{ route('admin.contact.index') }}" class="btn btn-outline-primary btn-sm">
                                عرض كل الرسائل
                            </a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @endif
        </aside>

        {{-- ═══ القارئ ═══ --}}
        <section class="inbox-reader" id="reader">
            @if ($openMessage)
                @php
                    $who = $sender($openMessage);
                    $topicKey = ContactTopic::of($openMessage);
                    $tm = ContactTopic::meta($topicKey);
                    $quote = "\n\n---\nبخصوص رسالتك «" . $openMessage->subject . "»:\n" . $openMessage->message;
                    $mailto = fn (string $body) => 'mailto:' . $openMessage->email
                        . '?subject=' . rawurlencode('رد: ' . $openMessage->subject)
                        . '&body=' . rawurlencode($body . $quote);
                    $replies = [$topicKey => $tm] + array_diff_key(ContactTopic::TOPICS, [$topicKey => 1]);
                @endphp

                <div class="cx-reader-bar">
                    {{-- على الهاتف يحلّ القارئ محلّ القائمة، فيلزم طريق عودة --}}
                    <a href="{{ route('admin.contact.index', $keep) }}" class="inbox-back">
                        <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        كل الرسائل
                    </a>
                    {{-- التنقّل ضمن القائمة كما تُعرض الآن، وبالأسهم ↑ ↓ --}}
                    <nav class="cx-nav" aria-label="التنقّل بين الرسائل">
                        <a @if ($prevId) href="{{ $link(['open' => $prevId]) }}" data-nav="prev" @else aria-disabled="true" @endif
                            class="cx-nav-btn" title="الأحدث (↑)" aria-label="الرسالة الأحدث">
                            <i class="ti ti-chevron-up" aria-hidden="true"></i>
                        </a>
                        <a @if ($nextId) href="{{ $link(['open' => $nextId]) }}" data-nav="next" @else aria-disabled="true" @endif
                            class="cx-nav-btn" title="الأقدم (↓)" aria-label="الرسالة الأقدم">
                            <i class="ti ti-chevron-down" aria-hidden="true"></i>
                        </a>
                    </nav>
                </div>

                <header class="reader-head">
                    <span class="cx-tag is-{{ $tm['tone'] }}"><i class="ti {{ $tm['icon'] }}" aria-hidden="true"></i>{{ $tm['label'] }}</span>
                    <h2 class="reader-subject">{{ $openMessage->subject }}</h2>
                    <time class="reader-time" datetime="{{ $openMessage->created_at?->toIso8601String() }}">
                        {{ $openMessage->created_at?->translatedFormat('l j F Y · H:i') }}
                    </time>
                </header>

                {{-- بطاقة المُرسِل: من هو في المنصّة، وأين تجد سياقه --}}
                <div class="cx-sender">
                    <span class="msg-avatar cx-av-{{ $who['role'] }}">{{ mb_substr($openMessage->name, 0, 2) }}</span>
                    <div class="cx-sender-body">
                        <div class="cx-sender-name">
                            <b>{{ $openMessage->name }}</b>
                            <span class="cx-role cx-role-{{ $who['role'] }}">{{ $who['label'] }}</span>
                        </div>
                        <div class="cx-sender-mail">
                            <a href="mailto:{{ $openMessage->email }}" dir="ltr" class="reader-mail">{{ $openMessage->email }}</a>
                            <button type="button" class="cx-copy" data-copy="{{ $openMessage->email }}" aria-label="نسخ البريد" title="نسخ البريد">
                                <i class="ti ti-copy" aria-hidden="true"></i>
                            </button>
                        </div>
                        @if ($who['meta'])
                            <div class="cx-sender-meta">
                                @foreach ($who['meta'] as $m)<span dir="auto">{{ $m }}</span>@endforeach
                            </div>
                        @endif
                    </div>
                    @if ($who['link'])
                        <a href="{{ $who['link'] }}" class="cx-sender-link">
                            <small>{{ $who['link_hint'] }}</small>
                            <b>{{ $who['link_label'] }}</b>
                            <i class="ti ti-arrow-left" aria-hidden="true"></i>
                        </a>
                    @elseif ($who['role'] === 'student')
                        <span class="cx-sender-link is-muted"><small>{{ $who['link_hint'] }}</small></span>
                    @elseif ($who['role'] === 'guest')
                        <span class="cx-sender-link is-muted"><small>لا حساب بهذا البريد في المنصّة</small></span>
                    @endif
                </div>

                {{-- \u200Enl2br\u200E مع \u200Ee()\u200E: أسطر المُرسِل تُحترم ولا يُنفَّذ ترميزه --}}
                <div class="reader-text cx-bubble">{!! nl2br(e($openMessage->message)) !!}</div>

                @if ($history->count())
                    <div class="cx-history">
                        <h3><i class="ti ti-history" aria-hidden="true"></i> رسائل سابقة من المُرسِل نفسه <span>{{ $history->count() }}</span></h3>
                        @foreach ($history as $old)
                            <a href="{{ $link(['open' => $old->id]) }}">
                                <span>{{ $old->subject }}</span>
                                <time>{{ $old->created_at?->diffForHumans() }}</time>
                            </a>
                        @endforeach
                    </div>
                @endif

                <footer class="reader-foot">
                    {{-- الردّ بالبريد لا بنموذج داخلي: المُرسِل قد لا يكون له حساب.
                         الردّ الجاهز بحسب موضوع الرسالة نقطة بداية تُعدَّل قبل الإرسال --}}
                    <div class="btn-group cx-reply">
                        <a class="btn btn-primary" href="{{ $mailto($tm['reply']) }}">
                            <i class="ti ti-mail-forward me-1" aria-hidden="true"></i>
                            الرد بالبريد
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="ردود جاهزة"></button>
                        <div class="dropdown-menu dropdown-menu-end cx-reply-menu">
                            <span class="dropdown-header">ردود جاهزة — تُفتح في بريدك للتعديل</span>
                            @foreach ($replies as $key => $r)
                                <a class="dropdown-item" href="{{ $mailto($r['reply']) }}">
                                    <i class="ti {{ $r['icon'] }} me-2" aria-hidden="true"></i>
                                    {{ $r['label'] }}
                                    @if ($loop->first)<span class="cx-suggest">مقترح</span>@endif
                                </a>
                            @endforeach
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ $mailto('مرحباً،') }}">
                                <i class="ti ti-pencil me-2" aria-hidden="true"></i> ردّ فارغ
                            </a>
                        </div>
                    </div>

                    <div class="reader-foot-end">
                        @if ($openMessage->is_read)
                            <form action="{{ route('admin.contact.unread', $openMessage->id) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-mail me-1" aria-hidden="true"></i>
                                    غير مقروءة
                                </button>
                            </form>
                        @endif

                        {{-- الفعل الأخطر أهدأ ما في الشريط --}}
                        <button type="button" class="btn-action btn-action--danger btn-delete" data-bs-toggle="modal"
                            data-bs-target="#deleteModal" data-id="{{ $openMessage->id }}"
                            data-name="{{ $openMessage->subject }}" title="حذف الرسالة" aria-label="حذف الرسالة">
                            <i class="ti ti-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </footer>
            @else
                {{-- لوح فارغ يقول ما يُفعل، لا مساحة بيضاء صامتة --}}
                <div class="reader-blank">
                    <span class="cx-blank-icon"><i class="ti ti-mail-opened" aria-hidden="true"></i></span>
                    <p>اختر رسالة من القائمة لقراءتها</p>
                    <span>تُعلَّم مقروءةً عند فتحها وحدها، لا بمجرد دخول الصفحة.</span>
                    @if ($unreadCount)
                        <a href="{{ $link(['unread' => 1]) }}" class="btn btn-outline-primary btn-sm mt-2">
                            ابدأ بغير المقروءة ({{ $unreadCount }})
                        </a>
                    @endif
                    <small class="cx-keys"><kbd>↑</kbd> <kbd>↓</kbd> للتنقّل بين الرسائل بعد فتح إحداها</small>
                </div>
            @endif
        </section>
    </div>

    @include('dashboard.component.delete_modal', [
        'delete_title' => 'الرسالة',
        'delete_controller_name' => 'admin.contact',
    ])

@endsection

@push('js')
    <script>
        (function () {
            var list = document.querySelector('.inbox-list');
            var KEY = 'inbox-scroll';

            if (list) {
                // فتح رسالة يُعيد تحميل الصفحة، فيعود تمرير القائمة إلى أوّلها
                // ويفقد الأدمن موضعه. نحفظه قبل المغادرة ونستعيده.
                list.addEventListener('scroll', function () {
                    try { sessionStorage.setItem(KEY, list.scrollTop); } catch (e) {}
                }, { passive: true });

                try {
                    var saved = parseInt(sessionStorage.getItem(KEY) || '0', 10);
                    if (saved > 0) list.scrollTop = saved;
                } catch (e) {}

                // وإن كانت المفتوحة خارج ما يُرى تُجلب إلى النظر
                var active = list.querySelector('.inbox-item.is-active');
                if (active) {
                    var top = active.offsetTop, bottom = top + active.offsetHeight;
                    if (top < list.scrollTop || bottom > list.scrollTop + list.clientHeight) {
                        active.scrollIntoView({ block: 'nearest' });
                    }
                }
            }

            // ↑ ↓ (أو k j) بين الرسائل — إلا أثناء الكتابة في حقل
            document.addEventListener('keydown', function (e) {
                if (e.altKey || e.ctrlKey || e.metaKey) return;
                var t = e.target;
                if (t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName))) return;
                if (document.querySelector('.modal.show, .dropdown-menu.show')) return;
                var dir = { ArrowUp: 'prev', k: 'prev', ArrowDown: 'next', j: 'next' }[e.key];
                var a = dir && document.querySelector('[data-nav="' + dir + '"]');
                if (a) { e.preventDefault(); location.href = a.href; }
            });

            // نسخ البريد بنقرة
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var done = function () {
                        btn.classList.add('is-done');
                        btn.querySelector('i').className = 'ti ti-check';
                        setTimeout(function () { btn.classList.remove('is-done'); btn.querySelector('i').className = 'ti ti-copy'; }, 1500);
                    };
                    if (navigator.clipboard) navigator.clipboard.writeText(btn.dataset.copy).then(done, function () {});
                });
            });
        })();
    </script>
@endpush
