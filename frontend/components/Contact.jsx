"use client";

import { useState } from "react";
import { MapPin, Mail, Phone, Send, Loader2, CheckCircle2, AlertCircle } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";
import MagneticButton from "./ui/MagneticButton";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost/graduationProjectTraker/public/api";

const MAP_SRC =
  "https://www.google.com/maps/embed?pb=!1m19!1m8!1m3!1d1689.6688676903332!2d34.440154!3d31.510897000000003!3m2!1i1024!2i768!4f13.1!4m8!3e6!4m0!4m5!1s0x14fd7f418cfa8357%3A0x56d415183481113e!2z2KzYp9mF2LnYqSDYp9mE2KPZgti12YnYjCDYp9mE2LTYp9ix2Lkg2KfZhNi52YXZiNmF2Yog2YXYtdix2YEg2KfZhNiu2LXZiNi12Iwg2LrYstip!3m2!1d31.510886699999997!2d34.4407764!5e1!3m2!1sar!2s!4v1648203626448!5m2!1sar!2s";

const inputCls =
  "w-full rounded-2xl border border-white/80 bg-white/70 px-4 py-3.5 text-sm text-ink placeholder:text-ink-mute/70 shadow-soft backdrop-blur transition-all duration-300 focus:border-brand-400 focus:bg-white focus:shadow-glow focus:outline-none";

export default function Contact() {
  const { t } = useLang();
  const [status, setStatus] = useState("idle"); // idle | sending | success | error

  async function onSubmit(e) {
    e.preventDefault();
    setStatus("sending");
    const form = e.currentTarget;
    const data = Object.fromEntries(new FormData(form).entries());
    try {
      const res = await fetch(`${API_URL}/send`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(data),
      });
      if (!res.ok) throw new Error("Request failed");
      setStatus("success");
      form.reset();
    } catch {
      setStatus("error");
    }
  }

  const info = [
    { Icon: MapPin, title: t.contact.address, sub: t.contact.addressSub },
    { Icon: Mail, title: t.contact.email, sub: null, href: `mailto:${t.contact.email}` },
    { Icon: Phone, title: t.contact.phone, sub: null, href: `tel:${t.contact.phone}`, ltr: true },
  ];

  return (
    <section id="contact" className="relative py-24">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-200 to-transparent" aria-hidden="true" />
      <div className="mx-auto max-w-6xl px-4 md:px-6">
        <SectionHeader kicker={t.contact.kicker} title={t.contact.title} text={t.contact.text} />

        {/* Info cards */}
        <div className="mt-14 grid gap-5 md:grid-cols-3">
          {info.map(({ Icon, title, sub, href, ltr }, i) => (
            <Reveal key={i} delay={i * 0.1}>
              <a
                href={href || "#contact"}
                className="glass group flex items-center gap-4 rounded-3xl p-5 transition-all duration-400 ease-out-expo hover:-translate-y-1 hover:shadow-lift"
              >
                <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-violet-500 text-white shadow-glow transition-transform duration-400 group-hover:scale-110">
                  <Icon size={21} aria-hidden="true" />
                </span>
                <span>
                  <span className={`block text-sm font-bold text-ink ${ltr ? "[direction:ltr]" : ""}`}>{title}</span>
                  {sub && <span className="mt-0.5 block text-xs text-ink-mute">{sub}</span>}
                </span>
              </a>
            </Reveal>
          ))}
        </div>

        {/* Map + form */}
        <div className="mt-8 grid gap-6 lg:grid-cols-2">
          <Reveal delay={0.1} className="h-full">
            <div className="gradient-border glass h-full min-h-[320px] overflow-hidden rounded-4xl p-2">
              <iframe
                title="Al-Aqsa University map"
                src={MAP_SRC}
                loading="lazy"
                className="h-full min-h-[304px] w-full rounded-[1.65rem] border-0 grayscale-[30%] transition-all duration-500 hover:grayscale-0"
                allowFullScreen
              />
            </div>
          </Reveal>

          <Reveal delay={0.2}>
            <form onSubmit={onSubmit} className="gradient-border glass flex flex-col gap-4 rounded-4xl p-6 md:p-8">
              <div className="grid gap-4 sm:grid-cols-2">
                <label className="block">
                  <span className="sr-only">{t.contact.form.name}</span>
                  <input name="name" type="text" required placeholder={t.contact.form.name} className={inputCls} />
                </label>
                <label className="block">
                  <span className="sr-only">{t.contact.form.email}</span>
                  <input name="email" type="email" required placeholder={t.contact.form.email} className={inputCls} />
                </label>
              </div>
              <label className="block">
                <span className="sr-only">{t.contact.form.subject}</span>
                <input name="subject" type="text" required placeholder={t.contact.form.subject} className={inputCls} />
              </label>
              <label className="block">
                <span className="sr-only">{t.contact.form.message}</span>
                <textarea name="message" rows={5} required placeholder={t.contact.form.message} className={`${inputCls} resize-none`} />
              </label>

              {status === "success" && (
                <p role="status" className="flex items-center gap-2 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                  <CheckCircle2 size={17} aria-hidden="true" /> {t.contact.form.success}
                </p>
              )}
              {status === "error" && (
                <p role="alert" className="flex items-center gap-2 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                  <AlertCircle size={17} aria-hidden="true" /> {t.contact.form.error}
                </p>
              )}

              <MagneticButton className="self-center">
                <button
                  type="submit"
                  disabled={status === "sending"}
                  className="shine flex items-center gap-2 rounded-2xl bg-gradient-to-r from-brand-600 to-violet-500 px-9 py-3.5 text-base font-semibold text-white shadow-glow transition-all duration-300 hover:shadow-lift disabled:opacity-60"
                >
                  {status === "sending" ? (
                    <Loader2 size={18} className="animate-spin" aria-hidden="true" />
                  ) : (
                    <Send size={17} aria-hidden="true" />
                  )}
                  {status === "sending" ? t.contact.form.sending : t.contact.form.send}
                </button>
              </MagneticButton>
            </form>
          </Reveal>
        </div>
      </div>
    </section>
  );
}
