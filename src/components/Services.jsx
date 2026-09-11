import React, { useState } from 'react';
import { 
  Stethoscope, 
  HeartPulse, 
  Baby, 
  UserCheck, 
  Scissors, 
  BriefcaseMedical, 
  Eye, 
  Activity,
  CalendarCheck,
  Check,
  ArrowUpRight
} from 'lucide-react';

export default function Services({ onOpenAppointment }) {
  const [activeCategory, setActiveCategory] = useState('all');

    // ============================================================================
    // STUB DATA: The following datasets represent placeholder clinical records.
    // Pending final validation and copy sign-off from Asclepius Medical Board.
    // ============================================================================
    
  const medicalServices = [
    {
      category: 'specialty',
      icon: HeartPulse,
      title: 'Internal Medicine & Cardiology',
      description: 'Comprehensive adult disease prevention, hypertension management, cholesterol profiling, cardiac risk evaluation, and chronic disease care.',
      treatments: ['Hypertension & Heart Care', 'Diabetes & Endocrine Management', 'Adult Wellness Check']
    },
    {
      category: 'specialty',
      icon: Baby,
      title: 'Pediatrics & Child Wellness',
      description: 'Dedicated clinical care for infants, children, and adolescents focusing on developmental milestones, illnesses, and childhood wellness.',
      treatments: ['Routine Child Checkups', 'Expanded Immunization / Vaccines', 'Growth & Nutrition Assessment']
    },
    {
      category: 'specialty',
      icon: UserCheck,
      title: 'Obstetrics & Gynecology (OB-GYN)',
      description: 'Complete women’s reproductive health, prenatal guidance, post-natal monitoring, routine cervical screening, and family planning support.',
      treatments: ['Prenatal & Postnatal Care', 'Cervical Cancer Screening (Pap Smear)', 'Women’s Health Consultation']
    },
    {
      category: 'surgical',
      icon: Scissors,
      title: 'General Surgery & Minor Procedures',
      description: 'Outpatient surgical consultations, routine dressing, suture placement/removal, cyst or mass excision, and incision & drainage.',
      treatments: ['Minor Excision of Skin Lesions', 'Wound Management & Suturing', 'Post-Op Follow-Up Care']
    },
    {
      category: 'corporate',
      icon: BriefcaseMedical,
      title: 'Occupational Health & Corporate APE',
      description: 'Full-scope corporate medical packages, Annual Physical Examinations (APE), Pre-Employment Medical Examinations (PEME), and Fit-to-Work clearances.',
      treatments: ['Pre-Employment Exam (PEME)', 'Annual Physical Exam (APE)', 'Medical Clearance & Drug Testing']
    },
    {
      category: 'specialty',
      icon: Eye,
      title: 'ENT & Head / Neck Consultation',
      description: 'Clinical diagnosis and treatment for ear infections, hearing evaluation, sinus disorders, chronic allergic rhinitis, and throat problems.',
      treatments: ['Ear Lavage & Cleaning', 'Sinusitis & Rhinitis Management', 'Throat & Tonsil Care']
    },
    {
      category: 'primary',
      icon: Stethoscope,
      title: 'Family Medicine & Primary Care',
      description: 'First-line outpatient consultations for acute illnesses such as flu, fever, respiratory infections, gastrointestinal upset, and prescription refills.',
      treatments: ['Acute Illness Diagnosis', 'Routine Health Screenings', 'Prescription Management']
    },
    {
      category: 'specialty',
      icon: Activity,
      title: 'Pulmonology & Respiratory Care',
      description: 'Specialized evaluation of lung conditions, chronic cough, asthma, bronchitis, pulmonary tuberculosis (TB) screening, and nebulization therapy.',
      treatments: ['Asthma Management', 'Nebulization & Inhalation Therapy', 'Respiratory Health Screening']
    }
  ];

  const categories = [
    { id: 'all', label: 'All Services' },
    { id: 'primary', label: 'Primary Care' },
    { id: 'specialty', label: 'Specialty Clinics' },
    { id: 'corporate', label: 'Corporate & APE' },
    { id: 'surgical', label: 'Minor Procedures' }
  ];

  const filteredServices = activeCategory === 'all' 
    ? medicalServices 
    : medicalServices.filter(s => s.category === activeCategory);

  return (
    <section id="services" className="py-20 lg:py-28 bg-slate-50 relative border-t border-slate-200/60">
      <div className="max-w-350 mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
          <div className="max-w-2xl text-left">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-cyan-100/70 border border-cyan-200 text-cyan-900 text-xs font-semibold uppercase tracking-wider mb-3">
              <Stethoscope className="w-3.5 h-3.5 text-cyan-700" />
              <span>Clinical Outpatient Specialties</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
              Comprehensive Clinical Care Across Every Life Stage.
            </h2>
            <p className="mt-3 text-slate-600 text-base leading-relaxed">
              Our clinic houses licensed physicians and board-certified specialists ready to provide thorough medical evaluation, diagnosis, and preventative health programs.
            </p>
          </div>

          {/* Consultation Button */}
          <div className="shrink-0">
            <button
              onClick={onOpenAppointment}
              className="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow transition cursor-pointer"
            >
              <CalendarCheck className="w-4 h-4 text-cyan-400" />
              <span>Schedule a Consultation</span>
            </button>
          </div>
        </div>

        {/* Filter Pills */}
        <div className="flex flex-wrap items-center gap-2 mb-10">
          {categories.map((cat) => (
            <button
              key={cat.id}
              onClick={() => setActiveCategory(cat.id)}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer ${
                activeCategory === cat.id
                  ? 'bg-cyan-700 text-white shadow-sm'
                  : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'
              }`}
            >
              {cat.label}
            </button>
          ))}
        </div>

        {/* Services Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {filteredServices.map((service, index) => {
            const Icon = service.icon;
            return (
              <div
                key={index}
                className="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-cyan-400/80 transition-all flex flex-col justify-between group"
              >
                <div>
                  <div className="w-12 h-12 rounded-xl bg-cyan-50 group-hover:bg-cyan-700 transition flex items-center justify-center text-cyan-700 group-hover:text-white mb-5">
                    <Icon className="w-6 h-6 transition-colors" />
                  </div>

                  <h3 className="text-lg font-bold text-slate-900 mb-2 group-hover:text-cyan-800 transition">
                    {service.title}
                  </h3>

                  <p className="text-xs sm:text-sm text-slate-500 leading-relaxed mb-5">
                    {service.description}
                  </p>

                  <div className="space-y-2 pt-4 border-t border-slate-100">
                    <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                      Key Highlights:
                    </p>
                    {service.treatments.map((item, idx) => (
                      <div key={idx} className="flex items-center gap-2 text-xs text-slate-700 font-medium">
                        <Check className="w-3.5 h-3.5 text-cyan-600 shrink-0" />
                        <span>{item}</span>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="pt-6 mt-6 border-t border-slate-100">
                  <button 
                    onClick={onOpenAppointment}
                    className="inline-flex items-center gap-1.5 text-xs font-bold text-cyan-700 hover:text-cyan-800 group-hover:translate-x-1 transition-transform cursor-pointer"
                  >
                    <span>Inquire or Book Doctor</span>
                    <ArrowUpRight className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            );
          })}
        </div>

        {/* Bottom Callout Notice */}
        <div className="mt-12 p-5 rounded-2xl bg-cyan-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-3.5 text-center sm:text-left">
            <div className="w-10 h-10 rounded-full bg-cyan-800 flex items-center justify-center shrink-0">
              <BriefcaseMedical className="w-5 h-5 text-cyan-300" />
            </div>
            <div>
              <h4 className="text-sm font-bold text-white">Need Corporate Account or Retainer Services?</h4>
              <p className="text-xs text-cyan-200">We offer customized on-site or clinic-based APE, PEME, and mobile clinic diagnostics for companies.</p>
            </div>
          </div>
          <button 
            onClick={onOpenAppointment}
            className="px-5 py-2.5 rounded-xl bg-white text-cyan-950 font-bold text-xs hover:bg-cyan-50 transition shrink-0 cursor-pointer"
          >
            Request Corporate Proposal
          </button>
        </div>

      </div>
    </section>
  );
}