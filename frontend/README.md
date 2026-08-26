# تخرُّج (Takharruj) — Public Frontend

A premium, modern public website for **Takharruj**, a graduation project tracking platform, built with **Next.js + Tailwind CSS + Framer Motion + Lenis**.

Design language: light premium (Apple/Stripe/Linear style) — glassmorphism, layered 3D scenes, floating cards, ambient gradients, scroll reveals, magnetic buttons, card tilt, Arabic RTL + English LTR with a live language toggle.

## Requirements

- Node.js 18.17+ (LTS recommended)
- The Laravel app running (XAMPP) at `http://localhost/graduationProjectTraker/public`

## Setup

```bash
cd frontend
npm install
copy .env.local.example .env.local   # adjust URLs if your Laravel URL differs
npm run dev
```

Open http://localhost:3000

## How it connects to Laravel

- **Sign in / لوحة التحكم** buttons link to the Laravel `/login` page (`NEXT_PUBLIC_LARAVEL_URL`).
- **Contact form** POSTs JSON to `POST {NEXT_PUBLIC_API_URL}/send`. This endpoint was added to the Laravel app:
  - `routes/api.php` → `Route::post('/send', [HomeController::class, 'sendApi'])`
  - `HomeController::sendApi()` → stores the message and notifies admins, returns JSON.
  - CORS for `api/*` is already open in `config/cors.php`.

## Production build

```bash
npm run build
npm start        # serves on :3000
```

Deploy the Next.js app on any Node host (or Vercel) and set the two env vars to your public Laravel URL.

## Structure

```
app/            layout, page, global styles (design tokens, glass, mesh, noise, grid)
components/     Navbar, Hero (3D scene), Stats, About, Services, Features,
                Roles, Lifecycle, Contact, Footer
components/ui/  Reveal, TiltCard, MagneticButton, CountUp, CursorGlow,
                SmoothScroll, SectionHeader
lib/            i18n dictionaries (ar/en) + LanguageContext (RTL/LTR toggle)
```

## Accessibility & performance

- `prefers-reduced-motion` respected everywhere (parallax, floats, counters, smooth scroll all degrade).
- Semantic HTML, ARIA labels, visible focus rings, WCAG AA contrast.
- GPU-friendly animations only (`transform`/`opacity`), lazy-loaded map and images.
