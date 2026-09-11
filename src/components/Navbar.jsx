/**
 * @fileoverview Asclepius Medical & Diagnostic Group Inc.
 * @description Component currently populated with mock clinical data for design review and layout verification.
 * 
 * @todo Replace mock values with finalized copy and clinical schedule upon client approval.
 * @author Engineering Team
 * @status Development / Staging
 */

import React, { useState } from 'react';
import { 
  Phone, 
  Clock, 
  LogIn, 
  Calendar, 
  Menu, 
  X, 
  ShieldCheck 
} from 'lucide-react';
import logoImg from '../assets/ASCLEPIUS.jpg';

export default function Navbar({ onOpenAppointment }) {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const navLinks = [
    { name: 'Home', href: '#home' },
    { name: 'About Us', href: '#about' },
    { name: 'Services', href: '#services' },
    { name: 'Diagnostics', href: '#diagnostics' },
    { name: 'Doctors', href: '#doctors' },
    { name: 'HMO Partners', href: '#hmo' },
    { name: 'Contact', href: '#contact' },
  ];

  return (
    <header className="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md shadow-xs border-b border-slate-100 transition-all">
      {/* Staging / MVP Demo Banner */}
      <div className="bg-amber-500/10 border-b border-amber-500/20 text-amber-900 px-4 py-1 text-center text-xs font-semibold flex items-center justify-center gap-2">
        <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span>[STAGING PREVIEW] Content, doctor schedules, and contact numbers are mock placeholders for client review.</span>
      </div>

      {/* Top Utility Bar */}
      <div className="bg-slate-900 text-slate-300 text-xs py-2 px-4 sm:px-8 border-b border-slate-800">
        <div className="max-w-350 mx-auto flex flex-wrap justify-between items-center gap-3">
          
          {/* Emergency & Schedule Info */}
          <div className="flex items-center gap-6">
            <div className="flex items-center gap-1.5">
              <Phone className="w-3.5 h-3.5 text-cyan-400" />
              <span className="text-slate-400">Emergency:</span>
              <strong className="text-white font-medium hover:text-cyan-300 transition cursor-pointer">
                (02) 8765-4321
              </strong>
            </div>
            <div className="hidden lg:flex items-center gap-1.5 text-slate-400">
              <Clock className="w-3.5 h-3.5 text-cyan-400" />
              <span>Mon - Sat: 6:00 AM - 7:00 PM | Sun: 6:00 AM - 12:00 PM</span>
            </div>
          </div>

          {/* Accreditation & Admin Login Link */}
          <div className="flex items-center gap-5 ml-auto">
            <div className="flex items-center gap-1.5 text-emerald-400 font-medium">
              <ShieldCheck className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">DOH & PhilHealth Accredited</span>
            </div>
            <div className="h-3 w-px bg-slate-700 hidden sm:block"></div>
            
            {/* Direct Admin Portal Link */}
            <a
              href="https://admin-portal-link.here" 
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-950/80 hover:bg-cyan-900 text-cyan-300 hover:text-cyan-100 font-medium transition border border-cyan-800/50"
              title="Asclepius Admin & Staff Portal"
            >
              <LogIn className="w-3 h-3" />
              <span>Admin Portal</span>
            </a>
          </div>

        </div>
      </div>

      {/* Main Navigation Bar */}
      <div className="max-w-350 mx-auto px-4 sm:px-8">
        <div className="flex items-center justify-between h-20 gap-4">
          
          {/* Brand Logo & Name */}
          <a href="#home" className="flex items-center gap-3.5 shrink-0 group">
            <div className="w-12 h-12 sm:w-14 sm:h-14 rounded-full overflow-hidden flex items-center justify-center bg-white shadow-sm border border-slate-200 group-hover:border-cyan-500 transition shrink-0 p-0.5">
              <img 
                src={logoImg} 
                alt="Asclepius Logo" 
                className="w-full h-full object-contain rounded-full"
              />
            </div>
            <div className="flex flex-col">
              <span className="font-extrabold tracking-tight text-slate-900 text-lg sm:text-xl leading-tight flex items-center gap-1.5">
                ASCLEPIUS
                <span className="w-2 h-2 rounded-full bg-cyan-600 inline-block"></span>
              </span>
              <span className="text-[11px] sm:text-xs font-semibold text-cyan-800 tracking-wider uppercase">
                Medical & Diagnostic Group Inc.
              </span>
            </div>
          </a>

          {/* Nav Links (Clean, No wrap, Spacious) */}
          <nav className="hidden xl:flex items-center gap-1 2xl:gap-2">
            {navLinks.map((link) => (
              <a
                key={link.name}
                href={link.href}
                className="px-3.5 py-2 text-[14px] font-medium text-slate-600 hover:text-cyan-700 hover:bg-cyan-50/80 rounded-lg transition whitespace-nowrap"
              >
                {link.name}
              </a>
            ))}
          </nav>

          {/* Action CTA Button */}
          <div className="hidden sm:flex items-center gap-3 shrink-0">
            <button
              onClick={onOpenAppointment}
              className="inline-flex items-center gap-2.5 px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-cyan-600 via-sky-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 rounded-xl shadow-md hover:shadow-lg transition-all transform active:scale-95 cursor-pointer whitespace-nowrap"
            >
              <Calendar className="w-4 h-4" />
              <span>Book Appointment</span>
            </button>
          </div>

          {/* Mobile / Tablet Menu Button */}
          <div className="flex xl:hidden items-center gap-2">
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="p-2 text-slate-700 hover:text-cyan-700 hover:bg-cyan-50 rounded-lg transition"
              aria-label="Toggle navigation"
            >
              {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>

        </div>
      </div>

      {/* Mobile Drawer Menu */}
      {isMobileMenuOpen && (
        <div className="xl:hidden bg-white border-b border-slate-200 px-5 pt-3 pb-6 space-y-3 shadow-xl">
          <div className="flex flex-col space-y-1">
            {navLinks.map((link) => (
              <a
                key={link.name}
                href={link.href}
                onClick={() => setIsMobileMenuOpen(false)}
                className="px-3.5 py-2.5 text-base font-medium text-slate-700 hover:text-cyan-700 hover:bg-cyan-50 rounded-lg transition"
              >
                {link.name}
              </a>
            ))}
          </div>

          <div className="pt-4 border-t border-slate-100 flex flex-col gap-2.5">
            <button
              onClick={() => {
                setIsMobileMenuOpen(false);
                if (onOpenAppointment) onOpenAppointment();
              }}
              className="w-full flex justify-center items-center gap-2 py-3 px-4 text-sm font-semibold text-white bg-linear-to-r from-cyan-600 to-blue-600 rounded-xl shadow cursor-pointer"
            >
              <Calendar className="w-4 h-4" />
              <span>Book Appointment</span>
            </button>
          </div>
        </div>
      )}
    </header>
  );
}