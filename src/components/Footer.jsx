import React from 'react';
import { 
  Phone, 
  Mail, 
  MapPin, 
  Clock, 
  ShieldCheck, 
  LogIn, 
  ArrowUpRight, 
  Heart 
} from 'lucide-react';
import logoImg from '../assets/ASCLEPIUS.jpg';

export default function Footer({ onOpenAppointment }) {
  return (
    <footer id="contact" className="bg-slate-950 text-slate-400 text-sm border-t border-slate-800">
      
      {/* Top Banner / Emergency Call Strip */}
      <div className="bg-cyan-950/60 border-b border-cyan-900/50 py-4 px-4 sm:px-8">
        <div className="max-w-350 mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-3 text-center sm:text-left">
            <div className="w-3 h-3 rounded-full bg-red-500 animate-ping"></div>
            <span className="text-white text-xs sm:text-sm font-medium">
              Medical Inquiries & Ambulance Assistance Hotline:
            </span>
            <a href="tel:0287654321" className="text-cyan-300 font-extrabold text-sm hover:underline">
              (02) 8765-4321
            </a>
          </div>

          <div className="flex items-center gap-4">
            <button
              onClick={onOpenAppointment}
              className="text-xs font-bold text-white bg-cyan-600 hover:bg-cyan-500 px-4 py-2 rounded-lg transition cursor-pointer"
            >
              Book Consultation Now
            </button>
          </div>
        </div>
      </div>

      {/* Main Footer Directory */}
      <div className="max-w-350 mx-auto px-4 sm:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
          
          {/* Col 1 & 2: Brand Profile */}
          <div className="lg:col-span-2 space-y-4 text-left">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-full overflow-hidden bg-white p-0.5 border border-slate-700 shrink-0">
                <img 
                  src={logoImg} 
                  alt="Asclepius Emblem" 
                  className="w-full h-full object-contain rounded-full"
                />
              </div>
              <div>
                <span className="text-white font-black text-lg tracking-tight block">
                  ASCLEPIUS
                </span>
                <span className="text-xs font-semibold text-cyan-400 uppercase tracking-wider block">
                  Medical & Diagnostic Group Inc.
                </span>
              </div>
            </div>

            <p className="text-xs sm:text-sm text-slate-400 leading-relaxed pr-4">
              Your dependable partner in accurate medical diagnostics, multi-specialty clinical consultations, and preventative healthcare. DOH licensed and PhilHealth accredited.
            </p>

            <div className="pt-2 flex items-center gap-2 text-xs text-emerald-400 font-medium">
              <ShieldCheck className="w-4 h-4 shrink-0" />
              <span>DOH License No. AMG-2024-0891</span>
            </div>

            {/* Admin Portal Shortcut in Footer */}
            <div className="pt-3">
              <a
                href="https://admin-portal-link.here"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 text-xs font-semibold text-slate-300 hover:text-white bg-slate-900 hover:bg-slate-800 px-3.5 py-2 rounded-xl border border-slate-800 transition"
              >
                <LogIn className="w-3.5 h-3.5 text-cyan-400" />
                <span>Hospital Admin & Staff Portal Login</span>
                <ArrowUpRight className="w-3 h-3 text-slate-500" />
              </a>
            </div>
          </div>

          {/* Col 3: Navigation Links */}
          <div className="space-y-3 text-left">
            <h4 className="text-xs font-bold text-white uppercase tracking-wider">
              Quick Links
            </h4>
            <ul className="space-y-2 text-xs sm:text-sm">
              <li><a href="#home" className="hover:text-white transition">Home</a></li>
              <li><a href="#about" className="hover:text-white transition">About Our Clinic</a></li>
              <li><a href="#services" className="hover:text-white transition">Medical Specialties</a></li>
              <li><a href="#diagnostics" className="hover:text-white transition">Laboratory & Imaging</a></li>
              <li><a href="#doctors" className="hover:text-white transition">Our Specialists</a></li>
              <li><a href="#hmo" className="hover:text-white transition">Accredited HMOs</a></li>
            </ul>
          </div>

          {/* Col 4: Clinic Hours */}
          <div className="space-y-3 text-left">
            <h4 className="text-xs font-bold text-white uppercase tracking-wider">
              Operating Hours
            </h4>
            <div className="space-y-2.5 text-xs">
              <div className="flex items-start gap-2">
                <Clock className="w-4 h-4 text-cyan-400 shrink-0 mt-0.5" />
                <div>
                  <p className="font-semibold text-slate-200">Outpatient & Lab:</p>
                  <p className="text-slate-400">Monday – Saturday: 6:00 AM – 7:00 PM</p>
                  <p className="text-slate-400">Sunday: 6:00 AM – 12:00 PM</p>
                </div>
              </div>

              <div className="pt-2 border-t border-slate-900">
                <p className="font-semibold text-slate-200">Laboratory Blood Extractions:</p>
                <p className="text-slate-400">Daily starts at 6:00 AM</p>
              </div>

              <div className="pt-2 border-t border-slate-900">
                <p className="font-semibold text-slate-200">Ultrasound Schedule:</p>
                <p className="text-slate-400">Mon - Sat by appointment</p>
              </div>
            </div>
          </div>

          {/* Col 5: Location & Contacts */}
          <div className="space-y-3 text-left">
            <h4 className="text-xs font-bold text-white uppercase tracking-wider">
              Clinic Location
            </h4>
            <div className="space-y-2.5 text-xs">
              <div className="flex items-start gap-2">
                <MapPin className="w-4 h-4 text-cyan-400 shrink-0 mt-0.5" />
                <span className="text-slate-300 leading-relaxed">
                  Asclepius Medical Building, Main Highway / Healthcare Blvd, Metro Manila, Philippines
                </span>
              </div>
              <div className="flex items-center gap-2">
                <Phone className="w-4 h-4 text-cyan-400 shrink-0" />
                <span className="text-slate-300">(02) 8765-4321 / 0917-888-ASCL</span>
              </div>
              <div className="flex items-center gap-2">
                <Mail className="w-4 h-4 text-cyan-400 shrink-0" />
                <span className="text-slate-300">inquiry@asclepiusmedical.ph</span>
              </div>
            </div>
          </div>

        </div>

        {/* Bottom Copyright */}
        <div className="mt-14 pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
          <p>© {new Date().getFullYear()} Asclepius Medical & Diagnostic Group Inc. All rights reserved.</p>
          <p className="flex items-center gap-1">
            Carefully crafted for healthcare quality and patient trust.
          </p>
        </div>

      </div>
    </footer>
  );
}