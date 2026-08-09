import "./globals.css";
import { Outfit, IBM_Plex_Sans_Arabic } from "next/font/google";
import { LanguageProvider } from "@/lib/LanguageContext";

const outfit = Outfit({
  subsets: ["latin"],
  variable: "--font-en",
  display: "swap",
});

const plexArabic = IBM_Plex_Sans_Arabic({
  subsets: ["arabic"],
  weight: ["300", "400", "500", "600", "700"],
  variable: "--font-ar",
  display: "swap",
});

export const metadata = {
  title: "تتبع مشاريع التخرج | كلية الحاسبات وتكنولوجيا المعلومات",
  description:
    "منصة كلية الحاسبات وتكنولوجيا المعلومات — جامعة الأقصى لتتبع مشاريع التخرج، إدارة الفرق والمشرفين ومتابعة مراحل المشروع.",
};

export default function RootLayout({ children }) {
  return (
    <html lang="ar" dir="rtl" suppressHydrationWarning>
      <body className={`${outfit.variable} ${plexArabic.variable} font-sans`}>
        <LanguageProvider>{children}</LanguageProvider>
      </body>
    </html>
  );
}
