"use client";

import { motion, useReducedMotion } from "framer-motion";
import { Users, Lightbulb, MessageCircle, BadgeCheck, Smartphone, Bell, CheckCircle2 } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";

const ICONS = [Users, Lightbulb, MessageCircle, BadgeCheck];

export default function Features() {
  const { t, dir } = useLang();
  const reduce = useReducedMotion();

  return (
    <section id="features" className="relative py-24">
      <div className="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div className="absolute left-[-8%] top-[20%] h-96 w-96 rounded-full bg-brand-100/70 blur-[100px]" />
        <div className="absolute right-[-8%] bottom-[10%] h-96 w-96 rounded-full bg-violet-100/70 blur-[100px]" />
      </div>

      <div className="relative mx-auto max-w-6xl px-4 md:px-6">
        <SectionHeader kicker={t.features.kicker} title={t.features.title} text={t.features.text} />

        <div className="mt-16 grid items-center gap-14 lg:grid-cols-2">
          {/* Feature list */}
          <div className="flex flex-col gap-5">
            {t.features.items.map((f, i) => {
              const Icon = ICONS[i % ICONS.length];
              return (
                <Reveal key={i} delay={i * 0.1}>
                  <article className="glass group flex gap-4 rounded-3xl p-5 transition-all duration-400 ease-out-expo hover:-translate-y-1 hover:shadow-lift md:p-6">
                    <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-violet-50 text-brand-600 shadow-soft transition-transform duration-400 ease-out-expo group-hover:scale-110 group-hover:rotate-3">
                      <Icon size={22} aria-hidden="true" />
                    </span>
                    <div>
                      <h3 className="mb-1.5 text-base font-bold text-ink">{f.title}</h3>
                      <p className="text-sm leading-relaxed text-ink-mute">{f.text}</p>
                    </div>
                  </article>
                </Reveal>
              );
            })}
          </div>

          {/* 3D phone showcase */}
          <Reveal delay={0.2}>
            <div className="scene-3d relative mx-auto w-fit" aria-hidden="true">
              <motion.div
                animate={reduce ? {} : { y: [0, -14, 0], rotateY: [0, 4, 0] }}
                transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }}
                className="preserve-3d relative"
              >
                {/* Phone */}
                <div className="gradient-border w-64 rounded-[2.6rem] bg-white/90 p-2.5 shadow-lift backdrop-blur md:w-72" dir={dir}>
                  <div className="rounded-[2.1rem] bg-gradient-to-b from-brand-50 via-white to-violet-50 p-4">
                    <div className="mx-auto mb-4 h-1.5 w-14 rounded-full bg-ink/10" />

                    {/* App header */}
                    <div className="mb-4 flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <span className="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-violet-500 text-white">
                          <Smartphone size={15} />
                        </span>
                        <span className="text-xs font-bold text-ink">{t.brand}</span>
                      </div>
                      <span className="relative flex h-8 w-8 items-center justify-center rounded-xl bg-white text-brand-600 shadow-soft">
                        <Bell size={14} />
                        <span className="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-rose-500" />
                      </span>
                    </div>

                    {/* Progress card */}
                    <div className="mb-3 rounded-2xl bg-white p-3.5 shadow-soft">
                      <div className="mb-2 flex items-center justify-between text-[11px] font-bold">
                        <span className="text-ink">{t.hero.cardProgress}</span>
                        <span className="text-brand-600">78%</span>
                      </div>
                      <div className="h-2 overflow-hidden rounded-full bg-brand-100">
                        <motion.div
                          className="h-full rounded-full bg-gradient-to-r from-brand-500 to-violet-500"
                          initial={{ width: "10%" }}
                          whileInView={{ width: "78%" }}
                          viewport={{ once: true }}
                          transition={{ duration: 1.2, ease: [0.16, 1, 0.3, 1] }}
                        />
                      </div>
                    </div>

                    {/* Task rows */}
                    {[0, 1, 2].map((r) => (
                      <div key={r} className="mb-2 flex items-center gap-2.5 rounded-2xl bg-white/80 p-3 shadow-soft">
                        <CheckCircle2 size={15} className={r < 2 ? "text-emerald-500" : "text-ink/20"} />
                        <div className="flex-1 space-y-1.5">
                          <div className={`h-1.5 rounded-full ${r === 0 ? "w-3/4" : r === 1 ? "w-2/3" : "w-4/5"} bg-brand-100`} />
                          <div className="h-1.5 w-1/3 rounded-full bg-ink/5" />
                        </div>
                      </div>
                    ))}

                    <div className="mt-3 h-24 rounded-2xl bg-gradient-to-br from-brand-500/15 via-violet-400/15 to-cyan-400/15" />
                  </div>
                </div>

                {/* Floating badge */}
                <motion.div
                  className="glass absolute top-10 w-40 rounded-2xl p-3 ltr:-right-16 rtl:-left-16"
                  style={{ z: 50 }}
                  animate={reduce ? {} : { y: [0, -10, 0] }}
                  transition={{ duration: 5, repeat: Infinity, ease: "easeInOut", delay: 0.8 }}
                  dir={dir}
                >
                  <div className="flex items-center gap-2">
                    <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                      <BadgeCheck size={16} />
                    </span>
                    <p className="text-[11px] font-bold leading-tight text-ink">{t.hero.cardApproved}</p>
                  </div>
                </motion.div>

                {/* Ground shadow */}
                <div className="absolute -bottom-10 left-1/2 h-8 w-3/4 -translate-x-1/2 rounded-[100%] bg-brand-900/10 blur-2xl" />
              </motion.div>
            </div>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
