<p align="center">
  <img src="docs/cover.png" alt="تخرُّج — Takharruj" width="900">
</p>

# تخرُّج — Takharruj

A platform that manages graduation projects from the first idea to the final grade — with three separate roles, each getting its own dashboard, its own permissions and its own workflow.

---

## The problem it solves

Graduation projects are usually run on spreadsheets, email threads and paper. Nobody has a single answer to "which groups have no supervisor yet", "which milestones are overdue" or "where is the latest version of that file". This system puts all of that in one place, and gives each role only the part that concerns them.

---

## The three roles

**Admin** runs the semester. Adds and manages students, supervisors, specialisations and academic terms — including bulk import from Excel, which matters when a new intake is 300 rows. Forms project groups, assigns supervisors, and maintains the catalogue of proposed project topics per specialisation. The dashboard is backed by periodic stat snapshots rather than recalculating everything on every page load.

**Supervisor** reviews the project requests that come in, accepts or rejects them, then tracks each supervised project: its milestones, its submitted files, and the discussion around them. Comments on student work, records the final evaluation, and can look back at previous semesters through the archive.

**Student** proposes a project, joins a group, follows the milestone timeline and its deadlines, uploads deliverables, and replies to supervisor comments — with a profile and notifications of their own.

---

## Features

- Three role-scoped dashboards with separate route groups and permissions
- Excel import for students and supervisors, plus data export
- Group formation and supervisor assignment
- Project proposal, review, acceptance and rejection flow
- Milestone tracking with deadlines
- File submissions per project, with a dedicated file controller
- Threaded comments between student and supervisor
- Final evaluation recorded against the project
- Semester management with an active-term flag and an archive of past terms
- Server-side data tables, so large lists stay fast
- Contact messages from the public site
- A separate Next.js marketing front end

---

## Design system

Every part of the product — the public site, the login screen and all three dashboards — is driven by a single documented token layer, written up in [`DESIGN_SYSTEM.md`](DESIGN_SYSTEM.md). Colours, radii, shadows and motion are defined once in two stylesheets and never hard-coded inside a page. Changing the brand means editing two files, not forty.

---

## Tech stack

**Back end** — Laravel 12, PHP 8.2, MySQL, Laravel Sanctum
**Tables and files** — Yajra DataTables (server-side), Maatwebsite/Excel (import and export)
**Views** — Blade, with a shared component layer (`page-header`, `kpi-card`, `status-badge`, `empty-state`, `attention-card`)
**Marketing front end** — Next.js 14, React 18, Framer Motion, Lenis, Tailwind CSS

---

## Getting started

**Requirements:** PHP 8.2+ with `gd`, `zip`, `xml`, `fileinfo`, `mbstring`, `pdo_mysql` · Composer · MySQL 8+ (or MariaDB 10.2+).
The front-end assets are static files in `public/` — there is no build step for the Laravel app.

### Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`, then:

```bash
php artisan migrate --seed
php artisan serve             # http://localhost:8000
```

Outside production, `--seed` loads demo data: one admin (`admin@admin.com`), 300 supervisors
and 500 students, **all with the password `adminadmin`**. It creates users only — no projects.
Never run the demo seed on a server that real people use (see *Deployment*).

For local mail and queues, `MAIL_MAILER=log` and `QUEUE_CONNECTION=sync` in `.env` avoid needing
a mail server or a worker: messages land in `storage/logs/laravel.log`.

### Tests

The tests run on a **separate copy** of your development database named `<DB_DATABASE>_testing`
(set in `phpunit.xml`). They depend on realistic data, so the copy is taken from your dev database:

```bash
php artisan test:prepare            # creates graduation_clc_testing from graduation_clc
php artisan test:prepare --force    # re-copy after new migrations or data changes
vendor/bin/phpunit
```

`tests/TestCase.php` refuses to run against any database whose name does not end in `_testing`.

### Deployment

1. **Environment.** In `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`
   (password-reset links are built from it), `LOG_LEVEL=warning`, `SESSION_SECURE_COOKIE=true`,
   real SMTP credentials and a real `MAIL_FROM_ADDRESS`.
2. **Schema and first admin.** `--seed` in production plants only semesters and specializations —
   no accounts:
   ```bash
   php artisan migrate --force --seed
   php artisan admin:create          # prompts for name, email and password
   ```
   Activate the current semester from *Admin › Semesters* before students log in.
3. **Scheduler (required).** Mail is queued, and a daily stats snapshot feeds the dashboard trend.
   Add one cron entry:
   ```
   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
   ```
   It runs a queue worker that exits when the queue is empty. On a server with Supervisor or
   systemd, run a permanent `php artisan queue:work` there instead and remove the `queue:work`
   line from `app/Console/Kernel.php`.
4. **Writable paths:** `storage/`, `bootstrap/cache/` and `public/uploads/` (avatars).
   Project files are stored privately in `storage/app` and served only through an
   authorized download route — `storage:link` is not needed.
5. **PHP upload limits.** Project files accept up to 10 MB and Excel imports up to 5 MB:
   set `upload_max_filesize = 12M` and `post_max_size = 16M`.
6. **Caches.** `php artisan config:cache && php artisan route:cache && php artisan view:cache`.

### Next.js front end

```bash
cd frontend
npm install
cp .env.local.example .env.local
npm run dev                   # http://localhost:3000
```

---

## Project structure

```
app/Models/                        Project, Group, Student, Supervisor, Semester,
                                   Specialize, ProjectMilestone, ProjectFile,
                                   ProjectComment, StatSnapshot, Admin, Contact
app/Http/Controllers/Admin/        students, supervisors, groups, semesters,
                                   specialisations, project catalogue, contacts
app/Http/Controllers/Supervisor/   dashboard, project management, profile
app/Http/Controllers/Student/      dashboard, files, comments, profile
app/Http/Controllers/FileController.php   uploads and downloads
resources/views/dashboard/         role-scoped dashboards
resources/views/components/        shared Blade UI components
resources/views/layouts/admin/     shell, header, sidebars per role
public/assets/css/premium.css      design tokens — public site
public/css/dashboard.css           design tokens — dashboards
frontend/                          Next.js marketing site
DESIGN_SYSTEM.md                   the single source of visual truth
```

---

## Author

**Ahmed Al-Hawajiri** — Full-Stack Developer
[GitHub](https://github.com/ahmedalhawajri89) · [LinkedIn](https://www.linkedin.com/in/ahmedalhawajri)

Licensed under the MIT License.

---

<div dir="rtl">

## نبذة بالعربية

**تخرُّج** منصة لإدارة مشاريع التخرّج من أول فكرة حتى الدرجة النهائية، مبنية على **ثلاثة أدوار منفصلة** لكل واحد منها لوحة تحكم وصلاحيات ومسار عمل خاص.

**الأدمن** يدير الفصل الدراسي: الطلاب والمشرفون والتخصصات والفصول، مع استيراد جماعي من Excel، وتكوين المجموعات وإسناد المشرفين، وإدارة قائمة المواضيع المقترحة.

**المشرف** يراجع طلبات المشاريع ويقبلها أو يرفضها، ثم يتابع المراحل والملفات المسلَّمة، ويعلّق على عمل الطلاب ويسجّل التقييم النهائي، مع أرشيف للفصول السابقة.

**الطالب** يقدّم مقترح مشروعه، ينضم لمجموعة، يتابع المراحل ومواعيدها، يرفع التسليمات، ويرد على تعليقات المشرف.

**ملاحظة على التصميم:** الموقع العام وصفحة الدخول واللوحات الثلاث كلها مبنية على طبقة توكنز واحدة موثّقة في `DESIGN_SYSTEM.md` — الألوان والزوايا والظلال والحركة معرّفة مرة واحدة في ملفين، وممنوع كتابتها يدوياً داخل الصفحات. تغيير الهوية البصرية يعني تعديل ملفين لا أربعين.

**التقنيات:** Laravel 12 و PHP 8.2 و MySQL و Sanctum، مع Yajra DataTables و Maatwebsite Excel، وواجهة تعريفية منفصلة بـ Next.js 14.

</div>
