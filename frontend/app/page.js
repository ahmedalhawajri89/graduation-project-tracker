"use client";

import SmoothScroll from "@/components/ui/SmoothScroll";
import CursorGlow from "@/components/ui/CursorGlow";
import Navbar from "@/components/Navbar";
import Hero from "@/components/Hero";
import Stats from "@/components/Stats";
import About from "@/components/About";
import Services from "@/components/Services";
import Features from "@/components/Features";
import Roles from "@/components/Roles";
import Lifecycle from "@/components/Lifecycle";
import Contact from "@/components/Contact";
import Footer from "@/components/Footer";

export default function Home() {
  return (
    <SmoothScroll>
      <CursorGlow />
      <Navbar />
      <main className="relative">
        <Hero />
        <Stats />
        <About />
        <Services />
        <Features />
        <Roles />
        <Lifecycle />
        <Contact />
      </main>
      <Footer />
    </SmoothScroll>
  );
}
