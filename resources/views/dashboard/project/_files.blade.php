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
    $n = $project->files->count();
    $locked = $project->is_locked;

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
                @endphp
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

                    <div class="file-actions">
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
