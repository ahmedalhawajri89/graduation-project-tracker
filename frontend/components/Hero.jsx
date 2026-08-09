"use client";

import { useRef } from "react";
import {
  motion,
  useMotionValue,
  useSpring,
  useTransform,
  useScroll,
  useReducedMotion,
} from "framer-motion";
import {
  CheckCircle2,
  Users,
  Bell,
  Sparkles,
  ArrowDown,
  GraduationCap,
  BarChart3,
} from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import MagneticButton from "./ui/MagneticButton";

const LARAVEL_URL = process.env.NEXT_PUBLIC_LARAVEL_URL || "http://localhost/graduationProjectTraker/public";
const EASE = [0.16, 1, 0.3, 1];

/* Deterministic pseudo-random particles (avoids hydration mismatch) */
const PARTICLES = Array.from({ length: 14 }, (_, i) => {
  const seed = (i * 9301 + 49297) % 233280;
  const rnd = seed / 233280;
  return {
    left: `${(rnd * 90 + 5).toFixed(2)}%`,
    top: `${(((i * 37) % 80) + 10).toFixed(2)}%`,
    size: 3 + (i % 3) * 2,
    delay: (i % 7) * 0.8,
    duration: 7 + (i % 5) * 2,
  };
});

export default function Hero() {
  const { t, dir } = useLang();
  const reduce = useReducedMotion();
  const sectionRef = useRef(null);

  /* Mouse parallax */
  const mx = useMotionValue(0);
  const my = useMotionValue(0);
  const smx = useSpring(mx, { stiffness: 60, damping: 20 });
  const smy = useSpring(my, { stiffness: 60, damping: 20 });

  const sceneX = useTransform(smx, [-1, 1], [-14, 14]);
  const sceneY = useTransform(smy, [-1, 1], [-10, 10]);
  const sceneRX = useTransform(smy, [-1, 1], [4, -4]);
  const sceneRY = useTransform(smx, [-1, 1], [-6, 6]);
  const farX = useTransform(smx, [-1, 1], [8, -8]);
  const farY = useTransform(smy, [-1, 1], [6, -6]);

  /* Scroll parallax */
  const { scrollYProgress } = useScroll({
    target: sectionRef,
    offset: ["start start", "end start"],
  });
  const sceneScrollY = useTransform(scrollYProgress, [0, 1], [0, 120]);
  const textScrollY = useTransform(scrollYProgress, [0, 1], [0, 60]);
  const fade = useTransform(scrollYProgress, [0, 0.7], [1, 0]);

  function onMouseMove(e) {
    if (reduce) return;
    const rect = sectionRef.current?.getBoundingClientRect();
    if (!rect) return;
    mx.set(((e.clientX - rect.left) / rect.width) * 2 - 1);
    my.set(((e.clientY - rect.top) / rect.height) * 2 - 1);
  }

  return (
    <section
      id="hero"
      ref={sectionRef}
      onMouseMove={onMouseMove}
      className="relative flex min-h-screen items-center overflow-hidden pt-32 pb-20"
    >
      {/* Ambient background */}
      <div className="mesh-bg" aria-hidden="true">
        <motion.div
          style={reduce ? {} : { x: farX, y: farY }}
          className="mesh-blob left-[-10%] top-[-15%] h-[520px] w-[520px]"
        >
          <div className="h-full w-full rounded-full bg-brand-300/60" />
        </motion.div>
        <motion.div
          style={reduce ? {} : { x: sceneX, y: sceneY }}
          className="mesh-blob right-[-12%] top-[10%] h-[460px] w-[460px]"
        >
          <div className="h-full w-full rounded-full bg-violet-300/50" />
        </motion.div>
        <div className="mesh-blob bottom-[-20%] left-[30%] h-[420px] w-[420px]">
          <div className="h-full w-full rounded-full bg-cyan-200/50" />
        </div>
        <div className="grid-overlay" />
        <div className="noise-overlay" />
      </div>

      {/* Ambient particles */}
      {!reduce && (
        <div className="pointer-events-none absolute inset-0" aria-hidden="true">
          {PARTICLES.map((p, i) => (
            <motion.span
              key={i}
              className="absolute rounded-full bg-brand-400/40"
              style={{ left: p.left, top: p.top, width: p.size, height: p.size }}
              animate={{ y: [0, -30, 0], opacity: [0.2, 0.7, 0.2] }}
              transition={{ duration: p.duration, delay: p.delay, repeat: Infinity, ease: "easeInOut" }}
            />
          ))}
        </div>
      )}

      <div className="relative z-10 mx-auto grid w-full max-w-6xl items-center gap-16 px-4 md:px-6 lg:grid-cols-2 lg:gap-8">
        {/* ---------- Copy ---------- */}
        <motion.div style={reduce ? {} : { y: textScrollY, opacity: fade }} className="flex flex-col items-start gap-6">
          <motion.span
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.1, ease: EASE }}
            className="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/70 px-4 py-1.5 text-sm font-semibold text-brand-700 backdrop-blur"
          >
            <Sparkles size={15} className="text-violet-500" aria-hidden="true" />
            {t.hero.badge}
          </motion.span>

          <motion.h1
            initial={{ opacity: 0, y: 24, filter: "blur(8px)" }}
            animate={{ opacity: 1, y: 0, filter: "blur(0px)" }}
            transition={{ duration: 0.7, delay: 0.2, ease: EASE }}
            className="text-4xl font-bold leading-[1.15] tracking-tight text-ink md:text-5xl lg:text-[3.4rem]"
          >
            {t.hero.title1}
            <br />
            <span className="text-gradient">{t.hero.title2}</span>
          </motion.h1>

          <motion.p
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, delay: 0.35, ease: EASE }}
            className="max-w-xl text-base leading-relaxed text-ink-mute md:text-lg"
          >
            {t.hero.subtitle}
          </motion.p>

          <motion.div
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, delay: 0.5, ease: EASE }}
            className="flex flex-wrap items-center gap-4"
          >
            <MagneticButton>
              <a
                href={`${LARAVEL_URL}/login`}
                className="shine flex items-center gap-2 rounded-2xl bg-gradient-to-r from-brand-600 to-violet-500 px-7 py-3.5 text-base font-semibold text-white shadow-glow transition-shadow duration-300 hover:shadow-lift"
              >
                <GraduationCap size={19} aria-hidden="true" />
                {t.hero.ctaPrimary}
              </a>
            </MagneticButton>
            <MagneticButton strength={0.15}>
              <a
                href="#about"
                className="flex items-center gap-2 rounded-2xl border border-brand-200 bg-white/70 px-7 py-3.5 text-base font-semibold text-brand-700 backdrop-blur transition-all duration-300 hover:border-brand-400 hover:shadow-soft"
              >
                {t.hero.ctaSecondary}
                <ArrowDown size={17} aria-hidden="true" />
              </a>
            </MagneticButton>
          </motion.div>
        </motion.div>

        {/* ---------- 3D scene ---------- */}
        <motion.div
          initial={{ opacity: 0, scale: 0.92, filter: "blur(10px)" }}
          animate={{ opacity: 1, scale: 1, filter: "blur(0px)" }}
          transition={{ duration: 0.9, delay: 0.4, ease: EASE }}
          style={reduce ? {} : { y: sceneScrollY, opacity: fade }}
          className="scene-3d relative mx-auto w-full max-w-[540px]"
          aria-hidden="true"
        >
          <motion.div
            style={reduce ? {} : { x: sceneX, y: sceneY, rotateX: sceneRX, rotateY: sceneRY }}
            className="preserve-3d relative"
          >
            {/* Dashboard card */}
            <div className="gradient-border glass-strong animate-float-slow relative z-10 rounded-4xl p-5 md:p-6" dir={dir}>
              {/* Window chrome */}
              <div className="mb-4 flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="h-3 w-3 rounded-full bg-rose-400" />
                  <span className="h-3 w-3 rounded-full bg-amber-400" />
                  <span className="h-3 w-3 rounded-full bg-emerald-400" />
                </div>
                <span className="text-xs font-semibold text-ink-mute">{t.hero.dashTitle}</span>
              </div>

              {/* Project row */}
              <div className="mb-4 flex items-center gap-3 rounded-2xl bg-brand-50/80 p-3.5">
                <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-violet-500 text-white">
                  <GraduationCap size={22} />
                </span>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-sm font-bold text-ink">{t.hero.dashProject}</p>
                  <p className="text-xs text-ink-mute">{t.hero.dashPhase}</p>
                </div>
                <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">78%</span>
              </div>

              {/* Progress bar */}
              <div className="mb-1 flex items-center justify-between text-xs font-semibold text-ink-mute">
                <span>{t.hero.cardProgress}</span>
                <span className="text-brand-600">78%</span>
              </div>
              <div className="mb-5 h-2.5 overflow-hidden rounded-full bg-brand-100">
                <motion.div
                  className="h-full rounded-full bg-gradient-to-r from-brand-500 via-violet-500 to-cyan-400"
                  initial={{ width: "0%" }}
                  animate={{ width: "78%" }}
                  transition={{ duration: 1.4, delay: 1, ease: EASE }}
                />
              </div>

              {/* Animated bar chart */}
              <div className="flex items-end justify-between gap-2 rounded-2xl bg-white/60 p-4">
                <div className="flex items-center gap-2 self-start text-xs font-semibold text-ink-mute">
                  <BarChart3 size={14} className="text-brand-500" />
                </div>
                {[42, 68, 35, 82, 58, 90, 72].map((h, i) => (
                  <motion.span
                    key={i}
                    className="w-6 rounded-t-lg bg-gradient-to-t from-brand-500/70 to-violet-400/80 md:w-8"
                    initial={{ height: 0 }}
                    animate={{ height: `${h * 0.8}px` }}
                    transition={{ duration: 0.8, delay: 1 + i * 0.08, ease: EASE }}
                  />
                ))}
              </div>
            </div>

            {/* Floating: approved notification */}
            <motion.div
              className="glass absolute -top-8 z-20 w-64 rounded-2.5xl p-4 ltr:-left-6 rtl:-right-6 md:ltr:-left-14 md:rtl:-right-14"
              style={{ z: 60 }}
              animate={reduce ? {} : { y: [0, -12, 0] }}
              transition={{ duration: 5.5, repeat: Infinity, ease: "easeInOut" }}
              dir={dir}
            >
              <div className="flex items-start gap-3">
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                  <CheckCircle2 size={18} />
                </span>
                <div>
                  <p className="text-sm font-bold text-ink">{t.hero.cardApproved}</p>
                  <p className="mt-0.5 text-xs text-ink-mute">{t.hero.cardApprovedSub}</p>
                </div>
              </div>
            </motion.div>

            {/* Floating: team card */}
            <motion.div
              className="glass absolute -bottom-10 z-20 w-56 rounded-2.5xl p-4 ltr:-right-4 rtl:-left-4 md:ltr:-right-12 md:rtl:-left-12"
              style={{ z: 80 }}
              animate={reduce ? {} : { y: [0, 10, 0] }}
              transition={{ duration: 6.5, repeat: Infinity, ease: "easeInOut", delay: 1 }}
              dir={dir}
            >
              <div className="mb-2 flex items-center gap-2 text-sm font-bold text-ink">
                <Users size={16} className="text-brand-500" />
                {t.hero.cardTeam}
              </div>
              <div className="flex items-center rtl:flex-row-reverse">
                {["from-brand-400 to-brand-600", "from-violet-400 to-violet-600", "from-cyan-400 to-cyan-600", "from-amber-400 to-amber-500"].map(
                  (g, i) => (
                    <span
                      key={i}
                      className={`flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br text-[11px] font-bold text-white ${g} ${
                        i > 0 ? "ltr:-ml-2.5 rtl:-mr-2.5" : ""
                      }`}
                    >
                      {String.fromCharCode(65 + i)}
                    </span>
                  )
                )}
                <span className="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-brand-50 text-[11px] font-bold text-brand-600 ltr:-ml-2.5 rtl:-mr-2.5">
                  +2
                </span>
              </div>
            </motion.div>

            {/* Floating: phone mockup */}
            <motion.div
              className="absolute top-[30%] z-0 hidden w-40 md:block ltr:-right-24 rtl:-left-24"
              style={{ z: 30 }}
              animate={reduce ? {} : { y: [0, -16, 0], rotate: [0, 1.5, 0] }}
              transition={{ duration: 8, repeat: Infinity, ease: "easeInOut", delay: 0.5 }}
            >
              <div className="gradient-border rounded-[2rem] bg-white/85 p-2 shadow-lift backdrop-blur" dir={dir}>
                <div className="rounded-[1.6rem] bg-gradient-to-b from-brand-50 to-white p-3">
                  <div className="mx-auto mb-3 h-1.5 w-12 rounded-full bg-ink/10" />
                  <div className="mb-3 flex items-center gap-2 rounded-xl bg-white p-2.5 shadow-soft">
                    <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-600">
                      <Bell size={13} />
                    </span>
                    <div className="min-w-0">
                      <p className="truncate text-[10px] font-bold text-ink">{t.hero.phoneNotify}</p>
                      <p className="truncate text-[9px] text-ink-mute">{t.hero.phoneNotifySub}</p>
                    </div>
                  </div>
                  <div className="space-y-2">
                    <div className="h-2 w-4/5 rounded-full bg-brand-100" />
                    <div className="h-2 w-3/5 rounded-full bg-violet-100" />
                    <div className="h-16 rounded-xl bg-gradient-to-br from-brand-100 to-violet-100" />
                    <div className="h-2 w-2/3 rounded-full bg-brand-100" />
                  </div>
                </div>
              </div>
            </motion.div>

            {/* Soft ground shadow */}
            <div className="absolute -bottom-16 left-1/2 h-10 w-3/4 -translate-x-1/2 rounded-[100%] bg-brand-900/10 blur-2xl" />
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
}
