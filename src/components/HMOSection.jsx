import React from 'react';
import { 
  ShieldCheck, 
  CreditCard, 
  FileCheck2, 
  HelpCircle, 
  CheckCircle2, 
  Building2, 
  ArrowRight,
  PhoneCall
} from 'lucide-react';

export default function HMOSection({ onOpenAppointment }) {

    // ============================================================================
    // STUB DATA: The following datasets represent placeholder clinical records.
    // Pending final validation and copy sign-off from Asclepius Medical Board.
    // ============================================================================

  const hmoList = [
    { name: 'PhilHealth', type: 'Government Healthcare', coverage: 'Direct Inpatient / Dialysis / Outpatient Packages' },
    { name: 'Maxicare Healthcare', type: 'Private HMO', coverage: 'Outpatient Consultation, Lab & Diagnostic Tests' },
    { name: 'Intellicare (Asalus)', type: 'Private HMO', coverage: 'Comprehensive Checkup & Diagnostic Procedures' },
    { name: 'Medicard Philippines', type: 'Private HMO', coverage: 'Doctor Consultations & Routine Laboratory' },
    { name: 'PhilCare', type: 'Private HMO', coverage: 'Specialist Visits & Preventive Diagnostics' },
    { name: 'Avega Managed Care', type: 'Corporate TPA', coverage: 'Executive Health & Outpatient Benefits' },
    { name: 'Cocolife Healthcare', type: 'Insurance / HMO', coverage: 'Clinical Consultations & Radiology Tests' },
    { name: 'Pacific Cross', type: 'Medical Insurance', coverage: 'Comprehensive Outpatient & Diagnostic Coverage' },
    { name: 'Generali Philippines', type: 'Corporate Health', coverage: 'Laboratory Panels & Outpatient Treatment' },
    { name: 'Lacson & Lacson / ValueCare', type: 'Managed Care', coverage: 'Consultation & Clinical Testing' },
    { name: 'EastWest Healthcare', type: 'Private HMO', coverage: 'Routine Outpatient Diagnostics' },
    { name: 'InLife Health Care', type: 'Insurance / HMO', coverage: 'Specialist Evaluation & Annual Physical' },
  ];

  const steps = [
    {
      step: '01',
      title: 'Present Your Card & ID',
      desc: 'Bring your valid HMO card (physical or mobile app QR) along with any valid government-issued ID upon clinic arrival.'
    },
    {
      step: '02',
      title: 'LOA Approval Assistance',
      desc: 'Our dedicated HMO Concierge desk will assist you in generating or verifying your Letter of Authorization (LOA) in real-time.'
    },
    {
      step: '03',
      title: 'Avail Covered Services',
      desc: 'Proceed directly to your doctor consultation, laboratory blood draw, or ultrasound with cashless transaction for approved benefits.'
    }
  ];

  return (
    <section id="hmo" className="py-20 lg:py-28 bg-white relative">
      <div className="max-w-350 mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-3xl mb-14 text-left">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold uppercase tracking-wider mb-3">
            <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
            <span>Accredited Healthcare Providers</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Hassle-Free Health Card & Insurance Coverage.
          </h2>
          <p className="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
            We partner with the country's leading Health Maintenance Organizations (HMO) and corporate insurance providers to make your consultations and diagnostic exams cashless and worry-free.
          </p>
        </div>

        {/* 3-Step Process Guide */}
        <div className="mb-16 bg-slate-50 border border-slate-200/80 rounded-3xl p-6 sm:p-10">
          <h3 className="text-xl font-bold text-slate-900 mb-2">
            How to Use Your Health Card at Asclepius:
          </h3>
          <p className="text-sm text-slate-500 mb-8">
            Simple 3-step walk-in or appointment procedure for HMO cardholders.
          </p>

          <div className="grid md:grid-cols-3 gap-6">
            {steps.map((item, idx) => (
              <div key={idx} className="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs relative">
                <span className="text-3xl font-black text-cyan-100 absolute top-4 right-5">
                  {item.step}
                </span>
                <div className="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-sm mb-4">
                  {idx + 1}
                </div>
                <h4 className="text-base font-bold text-slate-900 mb-2">
                  {item.title}
                </h4>
                <p className="text-xs sm:text-sm text-slate-600 leading-relaxed">
                  {item.desc}
                </p>
              </div>
            ))}
          </div>
        </div>

        {/* HMO Partners Grid */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 mb-14">
          {hmoList.map((hmo, index) => (
            <div
              key={index}
              className="p-5 rounded-2xl border border-slate-200/80 hover:border-cyan-400 bg-white hover:shadow-sm transition-all flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between gap-2 mb-2">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    {hmo.type}
                  </span>
                  <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                </div>
                <h4 className="text-sm sm:text-base font-extrabold text-slate-900 mb-1.5">
                  {hmo.name}
                </h4>
                <p className="text-[11px] sm:text-xs text-slate-500 leading-normal">
                  {hmo.coverage}
                </p>
              </div>
            </div>
          ))}
        </div>

        {/* HMO Help Banner */}
        <div className="p-6 sm:p-8 rounded-3xl bg-linear-to-r from-slate-900 via-cyan-950 to-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="text-left">
            <h4 className="text-lg sm:text-xl font-extrabold">
              Don’t see your HMO or Insurance provider listed?
            </h4>
            <p className="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
              We continually expand our accredited partner network. Contact our HMO billing concierge directly to verify coverage for your specific card or reimbursement claims.
            </p>
          </div>
          
          <div className="flex flex-wrap items-center gap-3 shrink-0">
            <a
              href="tel:0287654321"
              className="px-5 py-3 rounded-xl bg-white text-slate-900 font-bold text-xs sm:text-sm hover:bg-slate-100 transition inline-flex items-center gap-2"
            >
              <PhoneCall className="w-4 h-4 text-cyan-700" />
              <span>Call HMO Desk</span>
            </a>
            <button
              onClick={onOpenAppointment}
              className="px-5 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs sm:text-sm transition cursor-pointer"
            >
              Inquire Coverage Online
            </button>
          </div>
        </div>

      </div>
    </section>
  );
}