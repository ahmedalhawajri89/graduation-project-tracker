"use client";

import { UsersRound, Route, MessagesSquare } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";
import TiltCard from "./ui/TiltCard";

const ICONS = [UsersRound, Route, MessagesSquare];
const ACCENTS = [
  { icon: "from-brand-500 to-brand-600", glow: "bg-brand-200/60" },
  { icon: "from-violet-500 to-violet-600", glow: "bg-violet-200/60" },
  { icon: "from-cyan-500 to-cyan-600", glow: "bg-cyan-200/60" },
];

export default function Services() {
  const { t } = useLang();

  return (
    <section id="services" className="relative py-24">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-200 to-transparent" aria-hidden="true" />
      <div className="mx-auto max-w-6xl px-4 md:px-6">
        <SectionHeader kicker={t.services.kicker} title={t.services.title} text={t.services.text} />

        <div className="mt-14 grid gap-6 md:grid-cols-3">
          {t.services.items.map((s, i) => {
            const Icon = ICONS[i % ICONS.length];
            const accent = ACCENTS[i % ACCENTS.length];
            return (
              <Reveal key={i} delay={i * 0.12} className="h-full">
                <TiltCard max={6} className="h-full">
                  <article className="gradient-border glass group relative h-full overflow-hidden rounded-4xl p-7 transition-all duration-500 ease-out-expo hover:-translate-y-1.5 hover:shadow-lift">
                    <div
                      className={`pointer-events-none absolute -top-14 -right-14 h-40 w-40 rounded-full ${accent.glow} opacity-0 blur-3xl transition-opacity duration-500 group-hover:opacity-100`}
                      aria-hidden="true"
                    />
                    <span
                      className={`animate-float mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br ${accent.icon} text-white shadow-glow`}
                      style={{ animationDelay: `${i * 0.7}s` }}
                    >
                      <Icon size={26} aria-hidden="true" />
                    </span>
                    <h3 className="mb-3 text-lg font-bold text-ink">{s.title}</h3>
                    <p className="text-sm leading-relaxed text-ink-mute">{s.text}</p>
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
