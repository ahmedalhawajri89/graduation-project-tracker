{{--
    ملفات المشروع — للطالب والمشرف.

    فارغة: البطاقة كلّها منطقة إفلات، لا حالة فارغة وزرّ صغير معزول في طرفها.
    فيها ملفات: شارة لكل نوع، والوقت نسبياً، والبطاقة كلّها تستقبل السحب.

    @param \App\Models\Project $project
    @param string              $role  student | supervisor — لأسماء المسارات ولمن يحذف:
                                      المشرف أيّ ملف في مشروعه، والطالب ما رفعه وحده
--}}

@php
    $uploaderClass = $role === 'supervisor' ? \App\Models\Supervisor::class : \App\Models\Student::class;
    $project->loadMissing(['files.notes.author', 'files.notes.mentioned', 'files.notes.resolver', 'group.student']);
    $n = $project->files->count();
    $locked = $project->is_locked;

    // الملاحظات: للفريق والمشرف في مشروع مقبول غير مؤرشف
    $me = auth($role)->user();
    $canNote = ! $locked && in_array($project->status, ['accept', 'complete'], true);
    $isLeader = $role === 'student' && $project->group->contains(fn ($g) => $g->type === 'leader' && (int) $g->student_id === (int) $me->id);
    $teamOptions = $project->group->filter(fn ($g) => $g->student)->sortBy(fn ($g) => $g->type === 'leader' ? 0 : 1);
    // النموذج الذي فشل تحقّقه يُفتح لملفّه
    $noteFor = (int) old('note_file');

    // تبقى مفتوحة إن رجع النموذج بخطأ، فلا يضيع ما كُتب
    $uploadOpen = $errors->has('file') || $errors->has('title');
    $showDrop = ! $locked && ($n === 0 || $uploadOpen);

    // الامتداد من المسار المخزَّن — شارة لكل نوع بدل أيقونة رمادية واحدة
    $typeOf = function (string $path): array {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf' => ['is-pdf', 'PDF'],
            in_array($ext, ['doc', 'docx'], true) => ['is-doc', 'DOC'],
            in_array($ext, ['ppt', 'pptx'], true) => ['is-ppt', 'PPT'],
            in_array($ext, ['xls', 'xlsx'], true) => ['is-xls', 'XLS'],
            in_array($ext, ['png', 'jpg', 'jpeg'], true) => ['is-img', 'IMG'],
            in_array($ext, ['zip', 'rar'], true) => ['is-zip', strtoupper($ext)],
            default => ['is-other', strtoupper($ext) ?: 'FILE'],
        };
    };
@endphp

<section class="dist-panel mb-4 files-card" id="files" @unless ($locked) data-files-card @endunless>
    <div class="dist-head">
        <span class="dist-head-title">
            <i class="ti ti-folder" aria-hidden="true"></i>
            ملفات المشروع
            @if ($n)
                <span class="ctx-count">{{ $n }}</span>
            @endif
        </span>
        @if (! $locked && $n)
            <button type="button" class="btn btn-sm btn-outline-secondary" data-upload-toggle
                aria-controls="file-upload" aria-expanded="{{ $showDrop ? 'true' : 'false' }}">
                <i class="ti ti-upload me-1" aria-hidden="true"></i>
                رفع ملف
            </button>
        @endif
    </div>

    @if ($locked)
        <p class="hint-bar m-3">
            <i class="ti ti-archive" aria-hidden="true"></i>
            <span>أُغلق الرفع والحذف بعد رصد التقييم — الملفات أدناه للتنزيل فقط.</span>
        </p>
    @else
        <form action="{{ route($role . '.files.store', ['project' => $project->id]) }}" method="POST"
            enctype="multipart/form-data" class="file-upload {{ $showDrop ? '' : 'd-none' }}" id="file-upload">
            @csrf

            {{-- المنطقة نفسها هي الدعوة والفعل: نقرة أو إفلات --}}
            <label class="file-drop" id="file-drop" for="file-input">
                <input type="file" name="file" id="file-input" required class="file-drop-input"
                    accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">

                <span class="file-drop-idle" id="file-drop-idle">
                    <span class="file-drop-icon" aria-hidden="true"><i class="ti ti-cloud-upload"></i></span>
                    <b>اسحب ملفك هنا أو <u>اختره من جهازك</u></b>
                    <span class="file-chips" aria-label="الأنواع المقبولة">
                        <span class="file-chip is-pdf">PDF</span>
                        <span class="file-chip is-doc">Word</span>
                        <span class="file-chip is-ppt">PowerPoint</span>
                        <span class="file-chip is-xls">Excel</span>
                        <span class="file-chip is-zip">ZIP</span>
                        <span class="file-chip is-img">صور</span>
                    </span>
                    <small>حتى 10MB للملف —
                        {{ $role === 'supervisor' ? 'يراه الفريق كلّه' : 'المقترح والتقارير والعرض النهائي، ويراها المشرف' }}</small>
                </span>

                <span class="file-drop-picked d-none" id="file-drop-picked">
                    <span class="file-type" id="picked-type" aria-hidden="true"></span>
                    <span class="file-drop-picked-body">
                        <b id="picked-name"></b>
                        <small><bdi dir="ltr" id="picked-size"></bdi></small>
                    </span>
                    <span class="file-drop-change">تغيير</span>
                </span>
            </label>
            @error('file')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror

            {{-- يظهر بعد اختيار الملف، والعنوان مملوء من اسمه — يُعدَّل ولا يُكتب من الصفر --}}
            <div class="file-upload-row {{ $uploadOpen ? '' : 'd-none' }}" id="file-upload-row">
                <input type="text" name="title" id="file-title" required maxlength="120"
                    class="form-control @error('title') is-invalid @enderror"
                    placeholder="اسم الملف كما يراه الفريق" value="{{ old('title') }}" aria-label="اسم الملف">
                <button type="submit" class="btn btn-primary" data-loading-text="جارٍ الرفع..">
                    <i class="ti ti-upload me-1" aria-hidden="true"></i>
                    رفع
                </button>
            </div>
            @error('title')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </form>
    @endif

    @if ($n === 0 && $locked)
        <x-empty-state icon="ti-file-off" title="لا ملفات" text="لم يُرفع ملف لهذا المشروع قبل أرشفته." class="is-inline" />
    @elseif ($n > 0)
        <div class="file-list">
            @foreach ($project->files as $file)
                @php
                    $mine = $file->uploader_type === $uploaderClass
                        && (int) $file->uploader_id === (int) auth($role)->id();
                    [$typeClass, $typeLabel] = $typeOf((string) $file->path);
                    $canDelete = ! $locked && ($mine || $role === 'supervisor');
                    $open = $file->notes->filter->isOpen();
                    $forMe = $role === 'student' && $open->contains(fn ($x) => (int) $x->mentioned_id === (int) $me->id);
                @endphp
                <div class="file-item {{ $open->isNotEmpty() ? 'has-open' : '' }}" id="file-{{ $file->id }}">
                <div class="file-row">
                    <span class="file-type {{ $typeClass }}" aria-hidden="true">{{ $typeLabel }}</span>

                    <div class="file-body">
                        <span class="file-name">{{ $file->title }}</span>
                        <span class="file-meta">
                            {{-- bdi: الرقم ووحدته اللاتينية كانا ينقلبان «B 600» داخل نصّ عربي --}}
                            <bdi dir="ltr">{{ $file->human_size }}</bdi>
                            · <time datetime="{{ $file->created_at->toIso8601String() }}"
                                title="{{ $file->created_at->format('Y-m-d H:i') }}">{{ $file->created_at->diffForHumans() }}</time>
                            · {{ $mine ? 'أنت' : ($file->uploader_type === \App\Models\Supervisor::class ? 'المشرف' : ($file->uploader->name ?? 'طالب')) }}
                        </span>
                    </div>

                    {{-- ملاحظة مفتوحة = الملف بحاجة لتعديل؛ «لك» حين تكون أنت المنبَّه --}}
                    @if ($open->isNotEmpty())
                        <span class="file-flag {{ $forMe ? 'is-mine' : '' }}">
                            <i class="ti ti-alert-circle" aria-hidden="true"></i>
                            {{ $forMe ? 'تعديل مطلوب منك' : 'بحاجة لتعديل' }}
                        </span>
                    @endif

                    <div class="file-actions">
                        @if ($file->notes->isNotEmpty() || $canNote)
                            <button type="button" class="file-notes-btn {{ $open->isNotEmpty() ? 'has-open' : '' }}"
                                data-notes-toggle="{{ $file->id }}" aria-expanded="false" aria-controls="notes-{{ $file->id }}"
                                title="الملاحظات">
                                <i class="ti ti-message-2" aria-hidden="true"></i>
                                @if ($file->notes->isNotEmpty())
                                    <span>{{ $open->count() ?: $file->notes->count() }}</span>
                                @else
                                    <span class="visually-hidden">ملاحظة</span>
                                @endif
                            </button>
                        @endif

                        <a href="{{ route('files.download', ['file' => $file->id]) }}" class="btn-action"
                            title="تنزيل" aria-label="تنزيل {{ $file->title }}">
                            <i class="ti ti-download" aria-hidden="true"></i>
                        </a>

                        @if ($canDelete)
                            {{-- الحذف عند المرور على الحاسوب: فعل نادر لا يُعرض في كل صفّ دائماً --}}
                            <form action="{{ route($role . '.files.destroy', ['file' => $file->id]) }}" method="POST"
                                class="is-reveal" onsubmit="return confirm({{ Js::from('حذف «' . $file->title . '»؟ لا يمكن التراجع.') }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action--danger" title="حذف"
                                    aria-label="حذف {{ $file->title }}">
                                    <i class="ti ti-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- ===== الملاحظات على الملف ===== --}}
                <div class="file-notes" id="notes-{{ $file->id }}" @if ($noteFor !== $file->id) hidden @endif>
                    @foreach ($file->notes as $note)
                        @php
                            $byMe = $note->isBy($me);
                            $canToggle = ! $locked && ($byMe || $role === 'supervisor' || $isLeader
                                || ($role === 'student' && (int) $note->mentioned_id === (int) $me->id));
                        @endphp
                        <div class="file-note {{ $note->isOpen() ? '' : 'is-resolved' }}">
                            <x-avatar :user="$note->author" class="ctx-avatar file-note-avatar" />
                            <div class="file-note-body">
                                <div class="file-note-head">
                                    <b>{{ $byMe ? 'أنت' : ($note->author->name ?? 'مستخدم محذوف') }}</b>
                                    @if ($note->author_type === \App\Models\Supervisor::class)
                                        <span class="msg-role">مشرف</span>
                                    @endif
                                    @if ($note->mentioned)
                                        <span class="file-note-mention {{ (int) $note->mentioned_id === (int) $me->id && $role === 'student' ? 'is-me' : '' }}">
                                            <i class="ti ti-at" aria-hidden="true"></i>{{ (int) $note->mentioned_id === (int) $me->id && $role === 'student' ? 'أنت' : $note->mentioned->name }}
                                        </span>
                                    @endif
                                    <time datetime="{{ $note->created_at->toIso8601String() }}"
                                        title="{{ $note->created_at->format('Y-m-d H:i') }}">{{ $note->created_at->diffForHumans() }}</time>
                                </div>
                                <p class="file-note-text">{{ $note->body }}</p>
                                @unless ($note->isOpen())
                                    <span class="file-note-done">
                                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                                        عولجت{{ $note->resolver ? ' — ' . ($note->resolver->is($me) ? 'أنت' : $note->resolver->name) : '' }}
                                        · {{ $note->resolved_at->diffForHumans() }}
                                    </span>
                                @endunless
                            </div>
                            <div class="file-note-actions">
                                @if ($canToggle)
                                    <form action="{{ route('files.notes.toggle', $note->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="file-note-resolve {{ $note->isOpen() ? '' : 'is-reopen' }}">
                                            <i class="ti {{ $note->isOpen() ? 'ti-check' : 'ti-rotate' }}" aria-hidden="true"></i>
                                            {{ $note->isOpen() ? 'عولجت' : 'إعادة فتح' }}
                                        </button>
                                    </form>
                                @endif
                                @if ($byMe || $role === 'supervisor')
                                    <form action="{{ route('files.notes.destroy', $note->id) }}" method="POST"
                                        onsubmit="return confirm('حذف هذه الملاحظة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action--danger" title="حذف" aria-label="حذف الملاحظة">
                                            <i class="ti ti-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if ($canNote)
                        <form action="{{ route('files.notes.store', $file->id) }}" method="POST" class="file-note-form">
                            @csrf
                            <input type="hidden" name="note_file" value="{{ $file->id }}">
                            <textarea name="body" rows="2" maxlength="1000" required
                                class="form-control {{ $noteFor === $file->id && $errors->has('body') ? 'is-invalid' : '' }}"
                                placeholder="ما الذي يحتاج تعديلاً في هذا الملف؟ مثال: صفحة 3 ينقصها المرجع"
                                aria-label="ملاحظة على {{ $file->title }}">{{ $noteFor === $file->id ? old('body') : '' }}</textarea>
                            <div class="file-note-form-row">
                                <label class="file-note-to">
                                    <i class="ti ti-at" aria-hidden="true"></i>
                                    <select name="mentioned_id" class="form-select form-select-sm" aria-label="تنبيه عضو">
                                        <option value="">بلا تنبيه عضو</option>
                                        @foreach ($teamOptions as $g)
                                            @continue($role === 'student' && (int) $g->student_id === (int) $me->id)
                                            <option value="{{ $g->student_id }}" @selected($noteFor === $file->id && (int) old('mentioned_id') === (int) $g->student_id)>
                                                {{ $g->student->name }}{{ $g->type === 'leader' ? ' (القائد)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <button type="submit" class="btn btn-primary btn-sm" data-loading-text="…">
                                    <i class="ti ti-send me-1" aria-hidden="true"></i>
                                    إضافة ملاحظة
                                </button>
                            </div>
                            @if ($noteFor === $file->id)
                                @foreach (['body', 'mentioned_id'] as $field)
                                    @if ($errors->has($field))
                                        <div class="invalid-feedback d-block">{{ $errors->first($field) }}</div>
                                    @endif
                                @endforeach
                            @endif
                        </form>
                    @elseif ($file->notes->isEmpty())
                        <p class="file-notes-empty">لا ملاحظات على هذا الملف.</p>
                    @endif
                </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

@push('js')
    <script>
        (function () {
            var card = document.querySelector('[data-files-card]');
            var form = document.getElementById('file-upload');
            var input = document.getElementById('file-input');
            if (!card || !form || !input) return;

            var idle = document.getElementById('file-drop-idle');
            var picked = document.getElementById('file-drop-picked');
            var row = document.getElementById('file-upload-row');
            var title = document.getElementById('file-title');
            var drop = document.getElementById('file-drop');
            var autoTitle = '';

            var TYPES = {
                pdf: ['is-pdf', 'PDF'], doc: ['is-doc', 'DOC'], docx: ['is-doc', 'DOC'],
                ppt: ['is-ppt', 'PPT'], pptx: ['is-ppt', 'PPT'], xls: ['is-xls', 'XLS'], xlsx: ['is-xls', 'XLS'],
                png: ['is-img', 'IMG'], jpg: ['is-img', 'IMG'], jpeg: ['is-img', 'IMG'], zip: ['is-zip', 'ZIP'], rar: ['is-zip', 'RAR']
            };

            function humanSize(bytes) {
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
                return bytes + ' B';
            }

            function setOpen(open) {
                form.classList.toggle('d-none', !open);
                card.querySelectorAll('[data-upload-toggle]').forEach(function (b) {
                    b.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            }

            function showPicked() {
                var f = input.files && input.files[0];
                idle.classList.toggle('d-none', !!f);
                picked.classList.toggle('d-none', !f);
                drop.classList.toggle('has-file', !!f);
                if (!f) return;

                var dot = f.name.lastIndexOf('.');
                var ext = dot > 0 ? f.name.slice(dot + 1).toLowerCase() : '';
                var type = TYPES[ext] || ['is-other', (ext || 'FILE').toUpperCase()];
                var badge = document.getElementById('picked-type');
                badge.className = 'file-type ' + type[0];
                badge.textContent = type[1];
                document.getElementById('picked-name').textContent = f.name;
                document.getElementById('picked-size').textContent = humanSize(f.size);

                // العنوان من اسم الملف بلا امتداده — ما لم يكتب الطالب عنواناً بنفسه
                var base = (dot > 0 ? f.name.slice(0, dot) : f.name).replace(/[_\-]+/g, ' ').trim();
                if (title.value === '' || title.value === autoTitle) {
                    title.value = base.slice(0, 120);
                    autoTitle = title.value;
                }
                row.classList.remove('d-none');
                title.focus();
                title.select();
            }

            input.addEventListener('change', showPicked);

            card.querySelectorAll('[data-upload-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var open = form.classList.contains('d-none');
                    setOpen(open);
                    if (open) input.click();
                });
            });

            // البطاقة كلّها تستقبل السحب — لا حاجة للنقر على «رفع ملف» أولاً.
            // عدّاد لأن dragleave يُطلق عند المرور فوق كل عنصر داخلي
            var depth = 0;
            card.addEventListener('dragenter', function (e) {
                if (!e.dataTransfer || Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') === -1) return;
                e.preventDefault();
                depth++;
                card.classList.add('is-drag-over');
                setOpen(true);
            });
            card.addEventListener('dragover', function (e) {
                if (card.classList.contains('is-drag-over')) e.preventDefault();
            });
            card.addEventListener('dragleave', function () {
                depth = Math.max(0, depth - 1);
                if (depth === 0) card.classList.remove('is-drag-over');
            });
            card.addEventListener('drop', function (e) {
                e.preventDefault();
                depth = 0;
                card.classList.remove('is-drag-over');
                if (e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    showPicked();
                }
            });
        })();
    </script>
@endpush

@push('js')
    <script>
        // ملاحظات الملف: لوحة تنفتح تحته — ومن رابط إشعار (#file-<id>) تنفتح مباشرة
        (function () {
            function toggle(id, force) {
                var panel = document.getElementById('notes-' + id);
                var btn = document.querySelector('[data-notes-toggle="' + id + '"]');
                if (!panel) return;
                var open = typeof force === 'boolean' ? force : panel.hidden;
                panel.hidden = !open;
                if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    var box = panel.querySelector('textarea');
                    if (box && force !== true) box.focus({ preventScroll: true });
                }
            }

            document.querySelectorAll('[data-notes-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () { toggle(btn.dataset.notesToggle); });
            });

            // عند التحميل، وعند تغيّر العلامة في الصفحة نفسها (سطر «ماذا عليّ الآن»)
            function fromHash() {
                var m = location.hash.match(/^#file-(\d+)$/);
                if (!m) return;
                toggle(m[1], true);
                var item = document.getElementById('file-' + m[1]);
                if (item) item.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            fromHash();
            window.addEventListener('hashchange', fromHash);
        })();
    </script>
@endpush
