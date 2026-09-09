import React from 'react';
import { 
  Calendar, 
  ArrowRight, 
  Activity, 
  Clock, 
  Award, 
  HeartHandshake, 
  CheckCircle2, 
  PhoneCall,
  ShieldCheck,
  Stethoscope
} from 'lucide-react';
import heroImg from '../assets/right.jpg';

export default function Hero({ onOpenAppointment }) {
  return (
    <section id="home" className="relative pt-28 sm:pt-32 pb-16 lg:pb-24 bg-linear-to-b from-slate-50 via-cyan-50/30 to-white overflow-hidden">
      {/* Background Decorative Blobs */}
      <div className="absolute top-20 left-1/2 -translate-x-1/2 w-250 h-125 bg-linear-to-tr from-cyan-200/30 via-sky-100/20 to-transparent blur-3xl -z-10 pointer-events-none rounded-full" />

      <div className="max-w-350 mx-auto px-4 sm:px-8">
        
        {/* Main Grid: Copywriting & Hero Image */}
        <div className="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center pt-4">
          
          {/* Left Column: Headlines & CTAs */}
          <div className="lg:col-span-7 flex flex-col items-start space-y-6 text-left">
            
            {/* Accreditation / Trust Badge */}
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-100/70 border border-cyan-200/80 text-cyan-900 text-xs sm:text-sm font-semibold shadow-2xs">
              <span className="flex h-2 w-2 rounded-full bg-cyan-600 animate-pulse"></span>
              <span>DOH & PhilHealth Accredited Medical Center</span>
            </div>

            {/* Main Headline */}
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
              Advanced Healthcare & <br />
              <span className="text-transparent bg-clip-text bg-linear-to-r from-cyan-600 via-sky-600 to-blue-700">
                Accurate Diagnostics
              </span> <br />
              You Can Truly Trust.
            </h1>

            {/* Subtext */}
            <p className="text-base sm:text-lg text-slate-600 max-w-2xl leading-relaxed">
              At <strong>Asclepius Medical & Diagnostic Group Inc.</strong>, we bring comprehensive outpatient care, state-of-the-art laboratory diagnostics, and board-certified medical specialists closer to your family.
            </p>

            {/* Value Highlights List */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 w-full">
              <div className="flex items-center gap-2 text-slate-700 text-sm font-medium">
                <CheckCircle2 className="w-4 h-4 text-cyan-600 shrink-0" />
                <span>Fast & Reliable Digital Lab Results</span>
              </div>
              <div className="flex items-center gap-2 text-slate-700 text-sm font-medium">
                <CheckCircle2 className="w-4 h-4 text-cyan-600 shrink-0" />
                <span>Major HMOs & Insurance Accepted</span>
              </div>
              <div className="flex items-center gap-2 text-slate-700 text-sm font-medium">
                <CheckCircle2 className="w-4 h-4 text-cyan-600 shrink-0" />
                <span>Modern Digital Imaging & 4D Ultrasound</span>
              </div>
              <div className="flex items-center gap-2 text-slate-700 text-sm font-medium">
                <CheckCircle2 className="w-4 h-4 text-cyan-600 shrink-0" />
                <span>Compassionate Board-Certified Doctors</span>
              </div>
            </div>

            {/* CTA Buttons */}
            <div className="flex flex-wrap items-center gap-4 pt-3 w-full sm:w-auto">
              <button
                onClick={onOpenAppointment}
                className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 text-base font-semibold text-white bg-linear-to-r from-cyan-600 via-sky-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 rounded-xl shadow-lg hover:shadow-cyan-500/20 transition-all transform active:scale-95 cursor-pointer"
              >
                <Calendar className="w-5 h-5" />
                <span>Book an Appointment</span>
              </button>

              <a
                href="#services"
                className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 text-base font-semibold text-slate-700 hover:text-cyan-700 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 shadow-xs transition"
              >
                <span>View Our Services</span>
                <ArrowRight className="w-4 h-4" />
              </a>
            </div>

            {/* Emergency Hotline Quick Strip */}
            <div className="pt-2 flex items-center gap-3 text-slate-500 text-xs sm:text-sm">
              <div className="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                <PhoneCall className="w-4 h-4" />
              </div>
              <div>
                <span className="text-slate-500">Need urgent consultation? Call: </span>
                <span className="font-bold text-slate-800 hover:text-red-600 transition cursor-pointer">(02) 8765-4321</span>
              </div>
            </div>

          </div>

          {/* Right Column: Hero Image with Floating Glass Cards */}
          <div className="lg:col-span-5 relative flex justify-center items-center">
            
            {/* Glow / Backdrop Circle */}
            <div className="absolute w-[320px] sm:w-105 h-80 sm:h-105 bg-linear-to-tr from-cyan-300/40 to-blue-300/40 rounded-full blur-2xl -z-10" />

            {/* Image Container */}
            <div className="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white bg-white">
              <img 
                src={heroImg} 
                alt="Asclepius Medical Staff & Patient Care" 
                className="w-full h-auto max-h-125 object-cover object-center"
              />
            </div>

            {/* Floating Card 1: Fast Results */}
            <div className="absolute -top-4 -left-4 sm:-left-6 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 max-w-52.5 sm:max-w-57.5">
              <div className="p-2.5 rounded-xl bg-cyan-50 text-cyan-600">
                <Clock className="w-5 h-5" />
              </div>
              <div>
                <p className="text-[11px] font-medium text-slate-400">Diagnostic Speed</p>
                <p className="text-xs sm:text-sm font-bold text-slate-800">Fast Same-Day Results</p>
              </div>
            </div>

            {/* Floating Card 2: Certified Specialists */}
            <div className="absolute -bottom-4 -right-4 sm:-right-6 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 max-w-55">
              <div className="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                <ShieldCheck className="w-5 h-5" />
              </div>
              <div>
                <p className="text-[11px] font-medium text-slate-400">Quality Assured</p>
                <p className="text-xs sm:text-sm font-bold text-slate-800">Licensed Specialists</p>
              </div>
            </div>

          </div>

        </div>

        {/* Bottom Feature / Quick Stats Bar */}
        <div className="mt-16 sm:mt-20 pt-8 border-t border-slate-200/70 grid grid-cols-2 md:grid-cols-4 gap-6">
          
          <div className="flex items-start gap-3.5">
            <div className="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-cyan-600 shrink-0">
              <Activity className="w-5 h-5" />
            </div>
            <div>
              <h4 className="text-base font-bold text-slate-900">Modern Laboratory</h4>
              <p className="text-xs text-slate-500 mt-0.5 leading-relaxed">Automated analyzers for accurate blood & specimen tests.</p>
            </div>
          </div>

          <div className="flex items-start gap-3.5">
            <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
              <Stethoscope className="w-5 h-5" />
            </div>
            <div>
              <h4 className="text-base font-bold text-slate-900">Multi-Specialty Care</h4>
              <p className="text-xs text-slate-500 mt-0.5 leading-relaxed">Internal Medicine, Pedia, OB-GYN, Cardiology & more.</p>
            </div>
          </div>

          <div className="flex items-start gap-3.5">
            <div className="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 shrink-0">
              <Award className="w-5 h-5" />
            </div>
            <div>
              <h4 className="text-base font-bold text-slate-900">DOH Licensed</h4>
              <p className="text-xs text-slate-500 mt-0.5 leading-relaxed">Compliant with Philippine national healthcare standards.</p>
            </div>
          </div>

          <div className="flex items-start gap-3.5">
            <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
              <HeartHandshake className="w-5 h-5" />
            </div>
            <div>
              <h4 className="text-base font-bold text-slate-900">HMO Seamless Billing</h4>
              <p className="text-xs text-slate-500 mt-0.5 leading-relaxed">Letter of Authorization (LOA) accepted on-site.</p>
            </div>
          </div>

        </div>

      </div>
    </section>
  );
}