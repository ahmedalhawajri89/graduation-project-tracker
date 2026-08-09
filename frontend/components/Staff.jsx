"use client";

import { useState } from "react";
import { GraduationCap } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";
import TiltCard from "./ui/TiltCard";

function Avatar({ member }) {
  const [failed, setFailed] = useState(false);
  const initials = member.name
    .replace(/^(د\.|Dr\.)\s*/i, "")
    .split(" ")
    .slice(0, 2)
    .map((w) => w[0])
    .join("");

  if (failed || !member.img) {
    return (
      <div className="flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-400 to-violet-500 text-2xl font-bold text-white shadow-glow">
        {initials}
      </div>
    );
  }
  return (
    // eslint-disable-next-line @next/next/no-img-element
    <img
      src={member.img}
      alt={member.name}
      loading="lazy"
      onError={() => setFailed(true)}
      className="h-24 w-24 rounded-3xl object-cover shadow-lift ring-4 ring-white"
    />
  );
}

export default function Staff() {
  const { t } = useLang();

  return (
    <section id="staff" className="relative py-24">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-200 to-transparent" aria-hidden="true" />
      <div className="mx-auto max-w-6xl px-4 md:px-6">
        <SectionHeader kicker={t.staff.kicker} title={t.staff.title} text={t.staff.text} />

        <div className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          {t.staff.members.map((m, i) => (
            <Reveal key={i} delay={i * 0.08} className="h-full">
              <TiltCard max={9} className="h-full">
                <article className="glass group flex h-full flex-col items-center gap-4 rounded-4xl p-6 text-center transition-all duration-500 ease-out-expo hover:-translate-y-1.5 hover:shadow-lift">
                  <div className="relative">
                    <div className="absolute inset-0 scale-90 rounded-3xl bg-gradient-to-br from-brand-400/40 to-violet-400/40 blur-xl transition-transform duration-500 group-hover:scale-110" aria-hidden="true" />
                    <div className="relative">
                      <Avatar member={m} />
                    </div>
                    <span className="absolute -bottom-2 flex h-8 w-8 items-center justify-center rounded-xl bg-white text-brand-600 shadow-soft ltr:-right-2 rtl:-left-2">
                      <GraduationCap size={15} aria-hidden="true" />
                    </span>
                  </div>
                  <div>
                    <h3 className="text-sm font-bold text-ink">{m.name}</h3>
                    <p className="mt-1 text-xs font-medium text-brand-600">{m.role}</p>
                  </div>
                </article>
              </TiltCard>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
