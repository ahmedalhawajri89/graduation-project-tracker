"use client";

import { GraduationCap, UserCheck, SlidersHorizontal, Check } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";
import TiltCard from "./ui/TiltCard";

const ROLE_ICONS = {
  student: GraduationCap,
  supervisor: UserCheck,
  admin: SlidersHorizontal,
};

export default function Roles() {
  const { t } = useLang();

  return (
    <section id="roles" className="relative py-24">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-200 to-transparent" aria-hidden="true" />
      <div className="mx-auto max-w-6xl px-4 md:px-6">
        <SectionHeader kicker={t.roles.kicker} title={t.roles.title} text={t.roles.text} />

        <div className="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3">
          {t.roles.items.map((r, i) => {
            const Icon = ROLE_ICONS[r.key] ?? GraduationCap;
            return (
              <Reveal key={r.key} delay={i * 0.1} className="h-full">
                <TiltCard max={7} className="h-full">
                  <article className="glass group flex h-full flex-col gap-5 rounded-4xl p-7 transition-all duration-500 ease-out-expo hover:-translate-y-1.5 hover:shadow-lift">
                    <div className="relative w-fit">
                      <div className="absolute inset-0 scale-90 rounded-3xl bg-gradient-to-br from-brand-400/40 to-violet-400/40 blur-xl transition-transform duration-500 group-hover:scale-110" aria-hidden="true" />
                      <span className="relative flex h-14 w-14 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-500 to-violet-600 text-white shadow-glow">
                        <Icon size={24} aria-hidden="true" />
                      </span>
                    </div>

                    <div>
                      <h3 className="text-lg font-bold text-ink">{r.name}</h3>
                      <p className="mt-1 text-sm font-medium text-brand-600">{r.role}</p>
                    </div>

                    <ul className="flex flex-col gap-2.5">
                      {r.points.map((p, j) => (
                        <li key={j} className="flex items-start gap-2.5">
                          <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <Check size={12} strokeWidth={3} aria-hidden="true" />
                          </span>
                          <span className="text-sm leading-relaxed text-ink-mute">{p}</span>
                        </li>
                      ))}
                    </ul>
                  </article>
                </TiltCard>
              </Reveal>
            );
          })}
        </div>
      </div>
    </section>
  );
}
