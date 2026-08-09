"use client";

import { createContext, useContext, useEffect, useState } from "react";
import { dictionaries } from "./i18n";

const LanguageContext = createContext(null);

export function LanguageProvider({ children }) {
  const [locale, setLocale] = useState("ar");

  useEffect(() => {
    const saved = typeof window !== "undefined" && localStorage.getItem("locale");
    if (saved === "ar" || saved === "en") setLocale(saved);
  }, []);

  useEffect(() => {
    const dict = dictionaries[locale];
    document.documentElement.lang = locale;
    document.documentElement.dir = dict.dir;
    localStorage.setItem("locale", locale);
  }, [locale]);

  const t = dictionaries[locale];
  const toggle = () => setLocale((l) => (l === "ar" ? "en" : "ar"));

  return (
    <LanguageContext.Provider value={{ locale, setLocale, toggle, t, dir: t.dir }}>
      {children}
    </LanguageContext.Provider>
  );
}

export function useLang() {
  const ctx = useContext(LanguageContext);
  if (!ctx) throw new Error("useLang must be used within LanguageProvider");
  return ctx;
}
