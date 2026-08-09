# Graduation Project Tracker

A university system for managing graduation projects end to end — from proposing a project idea and forming a group, through supervisor assignment and milestone tracking, to file submissions, comments and final evaluation. Built around **three separate roles**, each with its own dashboard, permissions and workflow.

## ✨ Features

**Admin**
- Manage students, supervisors, specialisations and academic semesters
- Bulk-import students and supervisors from Excel
- Form and edit project groups, assign supervisors
- Manage the catalogue of proposed project topics per specialisation
- Dashboard KPIs backed by periodic stat snapshots

**Supervisor**
- Review and accept or reject project requests
- Track every supervised project, its milestones and submitted files
- Comment on student work and record the final evaluation
- Archive of past semesters

**Student**
- Submit a project proposal and join a group
- Follow milestone progress and deadlines
- Upload deliverables and reply to supervisor comments
- Personal profile and notifications

## 🛠 Tech Stack

**Laravel 12 · PHP 8.2 · MySQL · Blade · Sanctum**
Yajra DataTables for server-side tables, Maatwebsite/Excel for import & export.
A separate **Next.js 14** marketing front end lives in `frontend/` (React 18, Framer Motion, Lenis).

## 🎨 Design System

The whole product — public site, login and all three dashboards — is driven by one token layer, documented in [`DESIGN_SYSTEM.md`](DESIGN_SYSTEM.md). Colours, radii, shadows and motion are defined once and never hard-coded inside pages.

## 🚀 Getting Started

```bash
composer install
npm install && npm run dev

cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The Next.js front end:

```bash
cd frontend
npm install
cp .env.local.example .env.local
npm run dev
```

## 📁 Structure

```
app/Http/Controllers/Admin       admin area
app/Http/Controllers/Supervisor  supervisor area
app/Http/Controllers/Student     student area
resources/views/dashboard        role-scoped dashboards
public/assets/css/premium.css    public-site design tokens
public/css/dashboard.css         dashboard design tokens
frontend/                        Next.js marketing site
```

## 👤 Author

**Ahmed Al-Hawajiri** — Full-Stack Developer
[GitHub](https://github.com/ahmedalhawajri89)

---

<div dir="rtl">

### نبذة

نظام جامعي لإدارة مشاريع التخرّج من أولها لآخرها: اقتراح فكرة المشروع، تكوين المجموعة، إسناد المشرف، متابعة المراحل، رفع التسليمات والتعليق عليها، ثم التقييم النهائي. مبني على **ثلاثة أدوار منفصلة** لكل دور لوحة تحكم وصلاحيات ومسار عمل خاص به.

**الأدمن:** إدارة الطلاب والمشرفين والتخصصات والفصول الدراسية، استيراد جماعي من Excel، تكوين المجموعات، وإدارة قائمة المواضيع المقترحة.

**المشرف:** مراجعة طلبات المشاريع وقبولها أو رفضها، متابعة المراحل والملفات، التعليق على عمل الطلاب، وتسجيل التقييم النهائي.

**الطالب:** تقديم مقترح المشروع، متابعة المراحل والمواعيد، رفع التسليمات، والرد على تعليقات المشرف.

**التقنيات:** Laravel 12 و PHP 8.2 و MySQL و Blade مع Sanctum، إضافة إلى واجهة تعريفية منفصلة بـ Next.js 14 داخل مجلد `frontend/`.

</div>
