"use client";

import { useLang } from "@/lib/LanguageContext";
import Reveal from "./ui/Reveal";
import CountUp from "./ui/CountUp";
import { BookOpen, Users, FolderKanban, UsersRound } from "lucide-react";

const ICONS = [BookOpen, Users, FolderKanban, UsersRound];

export default function Stats() {
  const { t } = useLang();

  return (
    <section className="relative py-14" aria-label={t.stats.title}>
      <div className="mx-auto max-w-6xl px-4 md:px-6">
        <Reveal>
          <div className="gradient-border glass-strong grid grid-cols-2 gap-6 rounded-4xl p-8 md:grid-cols-4 md:p-10">
            {t.stats.items.map((s, i) => {
              const Icon = ICONS[i % ICONS.length];
              return (
                <div key={i} className="flex flex-col items-center gap-2 text-center">
                  <span className="mb-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-violet-50 text-brand-600 shadow-soft">
                    <Icon size={22} aria-hidden="true" />
                  </span>
                  <span className="text-gradient text-3xl font-bold tabular-nums md:text-4xl">
                    <CountUp to={s.value} suffix={s.suffix} />
                  </span>
                  <span className="text-sm font-medium text-ink-mute">{s.label}</span>
                </div>
              );
            })}
          </div>
        </Reveal>
      </div>
    </section>
  );
}
