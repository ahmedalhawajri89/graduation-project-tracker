"use client";

import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { GraduationCap, Menu, X, Languages, LogIn } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";

const LARAVEL_URL = process.env.NEXT_PUBLIC_LARAVEL_URL || "http://localhost/graduationProjectTraker/public";

export default function Navbar() {
  const { t, toggle, locale } = useLang();
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const links = [
    { href: "#hero", label: t.nav.home },
    { href: "#about", label: t.nav.about },
    { href: "#services", label: t.nav.services },
    { href: "#features", label: t.nav.features },
    { href: "#staff", label: t.nav.staff },
    { href: "#contact", label: t.nav.contact },
  ];

  return (
    <motion.header
      initial={{ y: -80, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
      className="fixed inset-x-0 top-0 z-50 px-3 pt-3 md:px-6 md:pt-4"
    >
      <nav
        aria-label="Main navigation"
        className={`mx-auto flex max-w-6xl items-center justify-between rounded-2xl px-4 py-3 transition-all duration-300 ease-out-expo md:px-6 ${
          scrolled ? "glass-strong shadow-soft" : "bg-transparent"
        }`}
      >
        {/* Brand */}
        <a href="#hero" className="flex items-center gap-2.5">
          <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-violet-500 text-white shadow-glow">
            <GraduationCap size={22} aria-hidden="true" />
          </span>
          <span className="flex flex-col leading-tight">
            <span className="text-base font-bold text-ink">{t.brand}</span>
            <span className="hidden text-[11px] text-ink-mute sm:block">{t.brandSub}</span>
          </span>
        </a>

        {/* Desktop links */}
        <ul className="hidden items-center gap-1 lg:flex">
          {links.map((l) => (
            <li key={l.href}>
              <a
                href={l.href}
                className="rounded-lg px-3.5 py-2 text-sm font-medium text-ink-soft transition-colors hover:bg-brand-50 hover:text-brand-700"
              >
                {l.label}
              </a>
            </li>
          ))}
        </ul>

        {/* Actions */}
        <div className="flex items-center gap-2">
          <button
            onClick={toggle}
            aria-label={locale === "ar" ? "Switch to English" : "التبديل إلى العربية"}
            className="flex items-center gap-1.5 rounded-xl border border-brand-100 bg-white/70 px-3 py-2 text-sm font-semibold text-brand-700 transition-all hover:border-brand-300 hover:shadow-soft"
          >
            <Languages size={16} aria-hidden="true" />
            <span>{locale === "ar" ? "EN" : "عربي"}</span>
          </button>

          <a
            href={`${LARAVEL_URL}/login`}
            className="shine hidden items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition-transform duration-300 ease-out-expo hover:-translate-y-0.5 sm:flex"
          >
            <LogIn size={16} aria-hidden="true" />
            {t.nav.login}
          </a>

          <button
            className="flex h-10 w-10 items-center justify-center rounded-xl border border-brand-100 bg-white/70 text-ink lg:hidden"
            onClick={() => setOpen((o) => !o)}
            aria-label={open ? "Close menu" : "Open menu"}
            aria-expanded={open}
          >
            {open ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>
      </nav>

      {/* Mobile menu */}
      <AnimatePresence>
        {open && (
          <motion.div
            initial={{ opacity: 0, y: -12, scale: 0.98 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -12, scale: 0.98 }}
            transition={{ duration: 0.25, ease: [0.16, 1, 0.3, 1] }}
            className="glass-strong mx-auto mt-2 max-w-6xl rounded-2xl p-4 lg:hidden"
          >
            <ul className="flex flex-col gap-1">
              {links.map((l) => (
                <li key={l.href}>
                  <a
                    href={l.href}
                    onClick={() => setOpen(false)}
                    className="block rounded-lg px-4 py-3 text-sm font-medium text-ink-soft transition-colors hover:bg-brand-50 hover:text-brand-700"
                  >
                    {l.label}
                  </a>
                </li>
              ))}
              <li>
                <a
                  href={`${LARAVEL_URL}/login`}
                  className="mt-2 flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-violet-500 px-5 py-3 text-sm font-semibold text-white"
                >
                  <LogIn size={16} aria-hidden="true" />
                  {t.nav.login}
                </a>
              </li>
            </ul>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.header>
  );
}
