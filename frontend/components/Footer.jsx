"use client";

import { GraduationCap, Facebook, Twitter, Instagram, Linkedin } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";

const SOCIALS = [
  { Icon: Facebook, href: "https://www.facebook.com/jamal.taroush", label: "Facebook" },
  { Icon: Twitter, href: "https://www.facebook.com/jamal.taroush", label: "Twitter" },
  { Icon: Instagram, href: "https://www.facebook.com/jamal.taroush", label: "Instagram" },
  { Icon: Linkedin, href: "https://www.facebook.com/jamal.taroush", label: "LinkedIn" },
];

export default function Footer() {
  const { t } = useLang();

  return (
    <footer className="relative mt-10 px-3 pb-4 md:px-6">
      <div className="glass-strong mx-auto flex max-w-6xl flex-col items-center gap-5 rounded-4xl px-6 py-8 md:flex-row md:justify-between">
        <div className="flex items-center gap-3">
          <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-violet-500 text-white">
            <GraduationCap size={20} aria-hidden="true" />
          </span>
          <div className="leading-tight">
            <p className="text-sm font-bold text-ink">{t.footer.madeFor}</p>
            <p className="text-xs text-ink-mute">{t.footer.rights}</p>
          </div>
        </div>

        <ul className="flex items-center gap-2">
          {SOCIALS.map(({ Icon, href, label }) => (
            <li key={label}>
              <a
                href={href}
                target="_blank"
                rel="noopener noreferrer"
                aria-label={label}
                className="flex h-10 w-10 items-center justify-center rounded-xl border border-brand-100 bg-white/70 text-ink-soft transition-all duration-300 ease-out-expo hover:-translate-y-1 hover:border-brand-300 hover:text-brand-600 hover:shadow-glow"
              >
                <Icon size={17} aria-hidden="true" />
              </a>
            </li>
          ))}
        </ul>
      </div>
    </footer>
  );
}
