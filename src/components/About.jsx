/**
 * @fileoverview Asclepius Medical & Diagnostic Group Inc.
 * @description Component currently populated with mock clinical data for design review and layout verification.
 * 
 * @todo Replace mock values with finalized copy and clinical schedule upon client approval.
 * @author Engineering Team
 * @status Development / Staging
 */

import React from 'react';
import { 
  Building2, 
  Target, 
  Eye, 
  CheckCircle2, 
  ShieldCheck, 
  Users2, 
  Microscope, 
  Award 
} from 'lucide-react';
import aboutImg from '../assets/ASCLEPIUS.jpg';

export default function About() {
  const coreValues = [
    {
      title: "Diagnostic Accuracy",
      desc: "Zero-compromise precision in laboratory tests, imaging, and patient reporting through automated medical equipment."
    },
    {
      title: "Patient-First Compassion",
      desc: "Attentive healthcare delivered with warmth, empathy, and clear guidance for patients and their families."
    },
    {
      title: "Integrity & Compliance",
      desc: "Full adherence to DOH regulatory standards, clinical ethical protocols, and honest healthcare billing."
    },
    {
      title: "Accessible Care",
      desc: "Affordable consultation rates, comprehensive checkup packages, and wide HMO accreditation."
    }
  ];

  return (
    <section id="about" className="py-20 lg:py-28 bg-white relative">
      <div className="max-w-350 mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-3xl mb-16 text-left">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-cyan-50 border border-cyan-200 text-cyan-800 text-xs font-semibold uppercase tracking-wider mb-3">
            <Building2 className="w-3.5 h-3.5 text-cyan-600" />
            <span>About Our Institution</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Dedicated to Accurate Results, <br className="hidden sm:inline" />
            Reliable Care, and Healthier Communities.
          </h2>
          <p className="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
            Established to deliver high-standard outpatient and diagnostic services, <strong>Asclepius Medical & Diagnostic Group Inc.</strong> serves as a reliable healthcare partner for individuals, families, and corporate organizations.
          </p>
        </div>

        {/* Content Grid */}
        <div className="grid lg:grid-cols-12 gap-12 items-center">
          
          {/* Left Column: Visual Profile & Badges */}
          <div className="lg:col-span-5">
            <div className="relative rounded-3xl bg-slate-50 border border-slate-200/80 p-6 sm:p-8 shadow-sm">
              <div className="w-full h-64 sm:h-72 rounded-2xl overflow-hidden bg-white border border-slate-100 flex items-center justify-center p-6 shadow-inner">
                <img 
                  src={aboutImg} 
                  alt="Asclepius Medical Seal" 
                  className="max-h-full max-w-full object-contain"
                />
              </div>

              {/* Institution Quick Facts */}
              <div className="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-200/70">
                <div>
                  <p className="text-xs text-slate-500 font-medium">Accreditation</p>
                  <p className="text-sm font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                    <ShieldCheck className="w-4 h-4 text-emerald-600" /> DOH Licensed
                  </p>
                </div>
                <div>
                  <p className="text-xs text-slate-500 font-medium">Service Coverage</p>
                  <p className="text-sm font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                    <Microscope className="w-4 h-4 text-cyan-600" /> Comprehensive Lab
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Right Column: Mission, Vision, and Core Values */}
          <div className="lg:col-span-7 space-y-8">
            
            {/* Mission & Vision Cards */}
            <div className="grid sm:grid-cols-2 gap-5">
              <div className="p-6 rounded-2xl bg-cyan-50/50 border border-cyan-100">
                <div className="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center mb-4 shadow-sm">
                  <Target className="w-5 h-5" />
                </div>
                <h3 className="text-lg font-bold text-slate-900 mb-2">Our Mission</h3>
                <p className="text-sm text-slate-600 leading-relaxed">
                  To provide accessible, precise, and timely medical diagnostics and patient-centered outpatient care guided by certified professionals and ethical standards.
                </p>
              </div>

              <div className="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div className="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center mb-4 shadow-sm">
                  <Eye className="w-5 h-5" />
                </div>
                <h3 className="text-lg font-bold text-slate-900 mb-2">Our Vision</h3>
                <p className="text-sm text-slate-600 leading-relaxed">
                  To be the recognized leader in diagnostic excellence and community healthcare, known for modern technology, compassionate clinical service, and trusted results.
                </p>
              </div>
            </div>

            {/* Core Values 2x2 List */}
            <div>
              <h4 className="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <Award className="w-4 h-4 text-cyan-600" />
                Our Clinical Commitment to Every Patient
              </h4>
              <div className="grid sm:grid-cols-2 gap-4">
                {coreValues.map((val, idx) => (
                  <div key={idx} className="flex items-start gap-3 p-3.5 rounded-xl border border-slate-100 bg-white hover:border-cyan-200 transition">
                    <CheckCircle2 className="w-4 h-4 text-cyan-600 shrink-0 mt-0.5" />
                    <div>
                      <h5 className="text-sm font-semibold text-slate-800">{val.title}</h5>
                      <p className="text-xs text-slate-500 mt-1 leading-relaxed">{val.desc}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>

          </div>

        </div>

      </div>
    </section>
  );
}