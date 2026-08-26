"use client";

import { motion, useReducedMotion } from "framer-motion";
import { CheckCircle2, ArrowLeft, ArrowRight, GraduationCap, Users, ListChecks, Award } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import Reveal from "./ui/Reveal";
import TiltCard from "./ui/TiltCard";

const PILLAR_ICONS = [Users, ListChecks, Award];

export default function About() {
  const { t, dir } = useLang();
  const reduce = useReducedMotion();
  const Arrow = dir === "rtl" ? ArrowLeft : ArrowRight;

  return (
    <section id="about" className="relative py-24">
      <div className="mx-auto grid max-w-6xl items-center gap-14 px-4 md:px-6 lg:grid-cols-2">
        {/* Illustration: layered platform card */}
        <div className="order-2 lg:order-1">
          <TiltCard max={7}>
            <div className="relative">
              <div className="gradient-border glass-strong relative overflow-hidden rounded-4xl p-8">
                <div className="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-violet-200/50 blur-3xl" />
                <div className="pointer-events-none absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-brand-200/50 blur-3xl" />

                {/* Platform illustration */}
                <div className="relative mx-auto flex max-w-sm flex-col items-center gap-4 py-6">
                  <motion.div
                    animate={reduce ? {} : { y: [0, -10, 0] }}
                    transition={{ duration: 7, repeat: Infinity, ease: "easeInOut" }}
                    className="flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-500 to-violet-600 text-white shadow-lift"
                  >
                    <GraduationCap size={44} aria-hidden="true" />
                  </motion.div>

                  <div className="h-8 w-px bg-gradient-to-b from-brand-300 to-transparent" />

                  <div className="grid w-full grid-cols-3 gap-3">
                    {t.about.pillars.map((p, i) => {
                      const Icon = PILLAR_ICONS[i % PILLAR_ICONS.length];
                      return (
                        <motion.div
                          key={i}
                          animate={reduce ? {} : { y: [0, -6, 0] }}
                          transition={{
                            duration: 5 + i,
                            repeat: Infinity,
                            ease: "easeInOut",
                            delay: i * 0.6,
                          }}
                          className="glass flex flex-col items-center gap-2 rounded-2xl p-3 text-center"
                        >
                          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <Icon size={17} aria-hidden="true" />
                          </span>
                          <span className="text-[11px] font-semibold leading-snug text-ink-soft">{p}</span>
                        </motion.div>
                      );
                    })}
                  </div>
                </div>
              </div>
              <div className="absolute -bottom-8 left-1/2 h-8 w-2/3 -translate-x-1/2 rounded-[100%] bg-brand-900/10 blur-2xl" aria-hidden="true" />
            </div>
          </TiltCard>
        </div>

        {/* Copy */}
        <div className="order-1 flex flex-col items-start gap-5 lg:order-2">
          <Reveal>
            <span className="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-1.5 text-sm font-semibold text-brand-700">
              <span className="h-1.5 w-1.5 rounded-full bg-brand-500" />
              {t.about.kicker}
            </span>
          </Reveal>
          <Reveal delay={0.08}>
            <h2 className="text-3xl font-bold leading-tight tracking-tight text-ink md:text-4xl">
              {t.about.title}
            </h2>
          </Reveal>
          <Reveal delay={0.16}>
            <p className="text-base leading-relaxed text-ink-mute md:text-lg">{t.about.text}</p>
          </Reveal>

          <Reveal delay={0.24} className="w-full">
            <p className="mb-3 text-sm font-bold uppercase tracking-wide text-brand-600">
              {t.about.pillarsTitle}
            </p>
            <ul className="flex flex-col gap-3">
              {t.about.pillars.map((p, i) => (
                <li
                  key={i}
                  className="glass flex items-center gap-3 rounded-2xl px-4 py-3.5 transition-all duration-300 ease-out-expo hover:-translate-y-0.5 hover:shadow-lift"
                >
                  <CheckCircle2 size={19} className="shrink-0 text-emerald-500" aria-hidden="true" />
                  <span className="text-sm font-semibold text-ink-soft">{p}</span>
                </li>
              ))}
            </ul>
          </Reveal>

          <Reveal delay={0.32}>
            <a
              href="#services"
              className="group inline-flex items-center gap-2 text-sm font-bold text-brand-600 transition-colors hover:text-brand-700"
            >
              {t.about.more}
              <Arrow size={16} className="transition-transform duration-300 ltr:group-hover:translate-x-1 rtl:group-hover:-translate-x-1" aria-hidden="true" />
            </a>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
