"use client";

import { useState } from "react";
import { Send, Loader2, CheckCircle2, AlertCircle } from "lucide-react";
import { useLang } from "@/lib/LanguageContext";
import SectionHeader from "./ui/SectionHeader";
import Reveal from "./ui/Reveal";
import MagneticButton from "./ui/MagneticButton";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost/graduationProjectTraker/public/api";

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

  return (
    <section id="contact" className="relative py-24">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-200 to-transparent" aria-hidden="true" />
      <div className="mx-auto max-w-6xl px-4 md:px-6">
        <SectionHeader kicker={t.contact.kicker} title={t.contact.title} text={t.contact.text} />

        <div className="mx-auto mt-14 max-w-2xl">
          <Reveal delay={0.1}>
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
