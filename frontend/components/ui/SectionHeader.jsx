"use client";

import Reveal from "./Reveal";

export default function SectionHeader({ kicker, title, text, align = "center" }) {
  const alignCls =
    align === "center" ? "text-center mx-auto items-center" : "items-start";

  return (
    <div className={`flex max-w-2xl flex-col gap-4 ${alignCls}`}>
      {kicker && (
        <Reveal>
          <span className="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-1.5 text-sm font-semibold text-brand-700">
            <span className="h-1.5 w-1.5 rounded-full bg-brand-500" />
            {kicker}
          </span>
        </Reveal>
      )}
      <Reveal delay={0.08}>
        <h2 className="text-3xl font-bold leading-tight tracking-tight text-ink md:text-4xl">
          {title}
        </h2>
      </Reveal>
      {text && (
        <Reveal delay={0.16}>
          <p className="text-base leading-relaxed text-ink-mute md:text-lg">{text}</p>
        </Reveal>
      )}
    </div>
  );
}
