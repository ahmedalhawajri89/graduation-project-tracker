@extends('layouts.admin.admin')
@section('title', 'رسائل الاستفسار')

@section('crumbs')
    <x-crumb>رسائل الاستفسار</x-crumb>
@endsection

@section('content')

    @php
        // الفلاتر تُحمل في كل رابط: فتح رسالة لا يُفقد البحث ولا التبويب
        $keep = request()->except(['open', 'page']);
    @endphp

    <x-page-header title="رسائل الاستفسار"
        subtitle="{{ $totalCount }} رسالة · {{ $unreadCount }} غير مقروءة">
        <x-slot:actions>
            @if ($unreadCount > 0)
                {{-- فعل صريح بدل أن يقع تلقائياً بمجرد فتح الصفحة --}}
                <form action="{{ route('admin.contact.readAll') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-ghost-secondary">
                        <i class="ti ti-mail-opened me-1" aria-hidden="true"></i>
                        تعليم الكل كمقروء
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ لوحان: قائمة وقارئ ═══
         كان أكورديون: لقراءة رسالة تُعاد الصفحة، وواحدة فقط تُفتح،
         والقائمة تبقى طويلة مهما قرأت. اللوحان يبقيان القائمة ومكانها
         فيها ثابتين بينما تُقرأ الرسالة — وهو ما تفعله كل برامج
         البريد لأنه الصحيح لهذه المهمة. --}}
    <div class="inbox-split {{ $openMessage ? 'has-open' : '' }}">

        {{-- ═══ القائمة ═══ --}}
        <aside class="inbox-list-pane">
            <div class="inbox-tools">
                <div class="inbox-tabs">
                    <a href="{{ route('admin.contact.index', request()->except(['unread', 'page', 'open'])) }}"
                        class="inbox-tab {{ ! $onlyUnread ? 'is-active' : '' }}">
                        الكل
                        <span class="inbox-tab-n">{{ $totalCount }}</span>
                    </a>
                    <a href="{{ route('admin.contact.index', array_merge(request()->except(['page', 'open']), ['unread' => 1])) }}"
                        class="inbox-tab {{ $onlyUnread ? 'is-active' : '' }}">
                        غير المقروءة
                        <span class="inbox-tab-n {{ $unreadCount ? 'is-live' : '' }}">{{ $unreadCount }}</span>
                    </a>
                </div>

                {{-- البحث كان كتلة كاملة العرض بزرّ منفصل — هنا حقل
                     واحد يُرسل بـ Enter، والأيقونة تقول وظيفته --}}
                <form action="{{ route('admin.contact.index') }}" method="get" class="inbox-search">
                    @if ($onlyUnread)
                        <input type="hidden" name="unread" value="1">
                    @endif
                    <i class="ti ti-search" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control"
                        placeholder="ابحث في الاسم أو الموضوع أو النصّ…" aria-label="بحث في الرسائل">
                    @if ($q !== '')
                        <a href="{{ route('admin.contact.index', $onlyUnread ? ['unread' => 1] : []) }}"
                            class="inbox-search-clear" aria-label="مسح البحث">
                            <i class="ti ti-x" aria-hidden="true"></i>
                        </a>
                    @endif
                </form>
            </div>

            @if ($messages->count())
                <div class="inbox-list">
                    @foreach ($messages as $message)
                        @php $isOpen = $openMessage && $openMessage->id === $message->id; @endphp

                        {{-- بلا \u200E#reader\u200E: على الشاشة الواسعة القارئ ظاهر
                             بجانب القائمة أصلاً، فالقفز إليه يُقلق العين
                             بلا فائدة. موضع القائمة يُستعاد بالسكربت
                             أسفل الصفحة. --}}
                        <a href="{{ route('admin.contact.index', array_merge($keep, ['open' => $message->id])) }}"
                            class="inbox-item {{ ! $message->is_read ? 'is-unread' : '' }} {{ $isOpen ? 'is-active' : '' }}">
                            <span class="inbox-item-dot" aria-hidden="true"></span>
                            <span class="msg-avatar">{{ mb_substr($message->name, 0, 2) }}</span>

                            <span class="inbox-item-body">
                                <span class="inbox-item-top">
                                    <b>{{ $message->name }}</b>
                                    <time datetime="{{ $message->created_at?->toIso8601String() }}"
                                        title="{{ $message->created_at?->format('Y-m-d H:i') }}">
                                        {{ $message->created_at?->diffForHumans(null, true) }}
                                    </time>
                                </span>
                                <span class="inbox-item-subject">{{ $message->subject }}</span>
                                <span class="inbox-item-preview">{{ \Illuminate\Support\Str::limit($message->message, 72) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                @if ($messages->hasPages())
                    <div class="inbox-pages">{!! $messages->links() !!}</div>
                @endif
            @else
                <x-empty-state icon="ti-mail-off"
                    title="{{ $q !== '' || $onlyUnread ? 'لا رسائل مطابقة' : 'لا توجد رسائل بعد' }}"
                    text="{{ $q !== '' || $onlyUnread
                        ? 'جرّب كلمة بحث أخرى أو اعرض كل الرسائل.'
                        : 'ستظهر هنا رسائل نموذج التواصل في الموقع العام.' }}"
                    class="py-5">
                    @if ($q !== '' || $onlyUnread)
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
                {{-- على الهاتف يحلّ القارئ محلّ القائمة، فيلزم طريق عودة --}}
                <a href="{{ route('admin.contact.index', $keep) }}" class="inbox-back">
                    <i class="ti ti-arrow-right" aria-hidden="true"></i>
                    كل الرسائل
                </a>

                <header class="reader-head">
                    <h2 class="reader-subject">{{ $openMessage->subject }}</h2>

                    <div class="reader-from">
                        <span class="msg-avatar">{{ mb_substr($openMessage->name, 0, 2) }}</span>
                        <div class="reader-from-body">
                            <b>{{ $openMessage->name }}</b>
                            <a href="mailto:{{ $openMessage->email }}" dir="ltr"
                                class="reader-mail">{{ $openMessage->email }}</a>
                        </div>
                        <time class="reader-time" datetime="{{ $openMessage->created_at?->toIso8601String() }}">
                            {{ $openMessage->created_at?->translatedFormat('j F Y · H:i') }}
                        </time>
                    </div>
                </header>

                {{-- \u200Enl2br\u200E مع \u200Ee()\u200E: أسطر المُرسِل تُحترم ولا يُنفَّذ ترميزه --}}
                <div class="reader-text">{!! nl2br(e($openMessage->message)) !!}</div>

                <footer class="reader-foot">
                    {{-- الردّ بالبريد لا بنموذج داخلي: المُرسِل ليس له
                         حساب في المنصّة، فلا مكان يقرأ فيه ردّاً --}}
                    <a class="btn btn-primary"
                        href="mailto:{{ $openMessage->email }}?subject={{ rawurlencode('رد: ' . $openMessage->subject) }}&body={{ rawurlencode("\n\n---\nبخصوص رسالتك:\n" . $openMessage->message) }}">
                        <i class="ti ti-mail-forward me-1" aria-hidden="true"></i>
                        الرد بالبريد
                    </a>

                    <div class="reader-foot-end">
                        @if ($openMessage->is_read)
                            <form action="{{ route('admin.contact.unread', $openMessage->id) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-ghost-secondary btn-sm">
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
                    <i class="ti ti-mail-opened" aria-hidden="true"></i>
                    <p>اختر رسالة من القائمة لقراءتها</p>
                    <span>تُعلَّم مقروءةً عند فتحها وحدها، لا بمجرد دخول الصفحة.</span>
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
            if (!list) return;

            var KEY = 'inbox-scroll';

            // فتح رسالة يُعيد تحميل الصفحة، فيعود تمرير القائمة إلى
            // أوّلها ويفقد الأدمن موضعه — وهو ما يُشعِر بأن الصفحة
            // «قفزت للأعلى». نحفظه قبل المغادرة ونستعيده.
            list.addEventListener('scroll', function () {
                try { sessionStorage.setItem(KEY, list.scrollTop); } catch (e) {}
            }, { passive: true });

            try {
                var saved = parseInt(sessionStorage.getItem(KEY) || '0', 10);
                if (saved > 0) list.scrollTop = saved;
            } catch (e) {}

            // وإن كانت المفتوحة خارج ما يُرى (قادمة من بحث أو رابط
            // مباشر) تُجلب إلى النظر
            var active = list.querySelector('.inbox-item.is-active');
            if (active) {
                var top = active.offsetTop;
                var bottom = top + active.offsetHeight;
                if (top < list.scrollTop || bottom > list.scrollTop + list.clientHeight) {
                    active.scrollIntoView({ block: 'nearest' });
                }
            }
        })();
    </script>
@endpush
