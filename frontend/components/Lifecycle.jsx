"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Lightbulb, ClipboardList, Boxes, Rocket, ChevronDown } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";

const ICONS = [Lightbulb, ClipboardList, Boxes, Rocket];

export default function Lifecycle() {
  const { t } = useLang();
  const [openIdx, setOpenIdx] = useState(0);

  return (
    <section id="lifecycle" className="relative py-24">
      <div className="mx-auto max-w-4xl px-4 md:px-6">
        <SectionHeader kicker={t.lifecycle.kicker} title={t.lifecycle.title} text={t.lifecycle.text} />

        <div className="relative mt-14 flex flex-col gap-4">
          {/* Connecting line */}
          <div
            className="pointer-events-none absolute top-6 bottom-6 w-px bg-gradient-to-b from-brand-300 via-violet-300 to-cyan-300 ltr:left-[27px] rtl:right-[27px]"
            aria-hidden="true"
          />

          {t.lifecycle.steps.map((step, i) => {
            const Icon = ICONS[i % ICONS.length];
            const open = openIdx === i;
            return (
              <Reveal key={i} delay={i * 0.08}>
                <div
                  className={`glass relative rounded-3xl transition-all duration-400 ease-out-expo ${
                    open ? "shadow-lift" : "hover:-translate-y-0.5 hover:shadow-soft"
                  }`}
                >
                  <button
                    onClick={() => setOpenIdx(open ? -1 : i)}
                    aria-expanded={open}
                    aria-controls={`lifecycle-panel-${i}`}
                    className="flex w-full items-center gap-4 rounded-3xl p-4 text-start md:p-5"
                  >
                    <span
                      className={`relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl transition-all duration-400 ${
                        open
                          ? "bg-gradient-to-br from-brand-500 to-violet-500 text-white shadow-glow"
                          : "bg-white text-brand-600 shadow-soft"
                      }`}
                    >
                      <Icon size={24} aria-hidden="true" />
                    </span>
                    <div className="flex-1">
                      <span className="mb-0.5 block text-xs font-bold text-brand-500">
                        {String(i + 1).padStart(2, "0")}
                      </span>
                      <h3 className="text-base font-bold text-ink md:text-lg">{step.title}</h3>
                    </div>
                    <ChevronDown
                      size={20}
                      className={`shrink-0 text-ink-mute transition-transform duration-300 ${open ? "rotate-180" : ""}`}
                      aria-hidden="true"
                    />
                  </button>

                  <AnimatePresence initial={false}>
                    {open && (
                      <motion.div
                        id={`lifecycle-panel-${i}`}
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: "auto", opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.35, ease: [0.16, 1, 0.3, 1] }}
                        className="overflow-hidden"
                      >
                        <p className="px-5 pb-5 text-sm leading-relaxed text-ink-mute md:ps-[4.75rem] md:pe-6">
                          {step.text}
                        </p>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              </Reveal>
            );
          })}
        </div>
      </div>
    </section>
  );
}
