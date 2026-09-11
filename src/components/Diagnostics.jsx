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
  FlaskConical, 
  Scan, 
  Activity, 
  FileSpreadsheet, 
  Clock, 
  CheckCircle2, 
  AlertCircle, 
  ArrowRight,
  ShieldCheck,
  Download
} from 'lucide-react';

export default function Diagnostics({ onOpenAppointment }) {
  const [activeTab, setActiveTab] = useState('lab');

    // ============================================================================
    // STUB DATA: The following datasets represent placeholder clinical records.
    // Pending final validation and copy sign-off from Asclepius Medical Board.
    // ============================================================================
    
  const diagnosticCategories = [
    { id: 'lab', label: 'Clinical Laboratory', icon: FlaskConical },
    { id: 'imaging', label: 'Digital X-Ray & Ultrasound', icon: Scan },
    { id: 'cardio', label: 'Cardiovascular Diagnostics', icon: Activity },
    { id: 'packages', label: 'Executive Health Packages', icon: FileSpreadsheet }
  ];

  const diagnosticData = {
    lab: {
      title: 'Automated Clinical Laboratory',
      subtitle: 'Accredited high-throughput analyzers delivering fast, verified pathology results.',
      items: [
        {
          name: 'Hematology',
          tests: ['Complete Blood Count (CBC) with Platelet', 'Blood Typing (ABO & Rh)', 'ESR & Coagulation Studies (PT/PTT)']
        },
        {
          name: 'Clinical Blood Chemistry',
          tests: ['Fasting Blood Sugar (FBS) & HbA1c', 'Lipid Profile (Cholesterol, Triglycerides, HDL, LDL)', 'Kidney Function: BUN, Creatinine, BUA', 'Liver Function: SGPT, SGOT, Total Bilirubin']
        },
        {
          name: 'Clinical Microscopy & Parasitology',
          tests: ['Routine Urinalysis (automated)', 'Routine Fecalysis / Stool Examination', 'Occult Blood Test (FOBT)']
        },
        {
          name: 'Immunology & Serology',
          tests: ['Hepatitis B Surface Antigen (HBsAg)', 'Thyroid Function (TSH, FT3, FT4)', 'Dengue NS1 / IgG & IgM Duo', 'Syphilis (VDRL/RPR) & HIV Screening']
        }
      ]
    },
    imaging: {
      title: 'Digital Imaging & Sonology Center',
      subtitle: 'Low-dose digital radiography and multi-frequency ultrasound with certified radiologist interpretation.',
      items: [
        {
          name: 'Digital Radiography (X-Ray)',
          tests: ['Chest PA / AP (Routine & Pre-Employment)', 'Cervical, Thoracic & Lumbar Spine', 'Paranasal Sinuses (Water’s view)', 'Upper and Lower Extremities']
        },
        {
          name: 'General Ultrasound (Sonology)',
          tests: ['Whole Abdomen Ultrasound', 'Upper Abdomen (Liver, Gallbladder, Pancreas, Spleen)', 'KUB (Kidneys, Ureters, Urinary Bladder)', 'Prostate Ultrasound']
        },
        {
          name: 'Women’s & Specialty Ultrasound',
          tests: ['Pelvic Ultrasound', 'Transvaginal Ultrasound (TVS)', 'Bilateral Breast Ultrasound', 'Thyroid Gland Ultrasound']
        },
        {
          name: 'OB Ultrasound & Fetal Assessment',
          tests: ['First Trimester Dating & Viability', 'Congenital Anomaly Scan (CAS)', '3D / 4D Fetal Visualization', 'Biophysical Profile (BPP)']
        }
      ]
    },
    cardio: {
      title: 'Non-Invasive Cardiovascular Tests',
      subtitle: 'Accurate electrical and structural assessment of cardiac health supervised by cardiologists.',
      items: [
        {
          name: 'Standard Electrocardiogram',
          tests: ['12-Lead Resting ECG with Official Reading', 'Rhythm Strip Recording', 'Pre-operative Cardiac Clearance ECG']
        },
        {
          name: '2D Echocardiography',
          tests: ['2D-Echo with Color Doppler', 'Left Ventricular Function & Ejection Fraction', 'Valvular Regurgitation / Stenosis Assessment']
        },
        {
          name: 'Cardiovascular Stress Testing',
          tests: ['Treadmill Stress Test (Exercise ECG)', 'Ischemia & CAD Risk Detection', 'Functional Capacity Assessment']
        },
        {
          name: 'Ambulatory Monitoring',
          tests: ['24-Hour Holter ECG Monitoring (Arrhythmia detection)', '24-Hour Ambulatory Blood Pressure Monitoring (ABPM)']
        }
      ]
    },
    packages: {
      title: 'Tailored Health Checkup Packages',
      subtitle: 'Cost-efficient wellness bundles designed for routine monitoring, employment, and preventive health.',
      items: [
        {
          name: 'Basic Wellness Package',
          tests: ['CBC with Platelet Count', 'Routine Urinalysis & Fecalysis', 'Chest X-Ray (Digital)', 'Physical Examination & Vital Signs Check']
        },
        {
          name: 'Diabetic & Metabolic Bundle',
          tests: ['Fasting Blood Sugar (FBS)', 'Glycated Hemoglobin (HbA1c)', 'Full Lipid Profile', 'Serum Creatinine & Blood Uric Acid (BUA)']
        },
        {
          name: 'Comprehensive Executive Package',
          tests: ['Complete Blood Chemistry (12 Parameters)', '12-Lead ECG & Digital Chest X-Ray', 'Whole Abdomen Ultrasound', 'Doctor Consultation & Medical Evaluation Report']
        },
        {
          name: 'Pre-Employment Medical Package',
          tests: ['Physical Exam & Medical History', 'Chest X-Ray PA View', 'Complete Blood Count & Urinalysis', 'Drug Screening (Methamphetamine & THC)']
        }
      ]
    }
  };

  const currentData = diagnosticData[activeTab];

  return (
    <section id="diagnostics" className="py-20 lg:py-28 bg-white relative">
      <div className="max-w-350 mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-3xl mb-12 text-left">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-cyan-50 border border-cyan-200 text-cyan-800 text-xs font-semibold uppercase tracking-wider mb-3">
            <FlaskConical className="w-3.5 h-3.5 text-cyan-600" />
            <span>State-of-the-Art Diagnostic Center</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Accurate Laboratory, Imaging & Diagnostic Services.
          </h2>
          <p className="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
            Equipped with modern automated laboratory instruments, computerized radiography, and diagnostic imaging to provide your attending physician with reliable data.
          </p>
        </div>

        {/* Tab Buttons */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-10">
          {diagnosticCategories.map((tab) => {
            const TabIcon = tab.icon;
            const isSelected = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`p-4 rounded-2xl flex items-center gap-3 text-left transition-all border cursor-pointer ${
                  isSelected
                    ? 'bg-cyan-900 border-cyan-900 text-white shadow-md'
                    : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'
                }`}
              >
                <div className={`p-2.5 rounded-xl shrink-0 ${
                  isSelected ? 'bg-cyan-800 text-cyan-300' : 'bg-white text-slate-600 shadow-xs'
                }`}>
                  <TabIcon className="w-5 h-5" />
                </div>
                <span className="text-xs sm:text-sm font-bold tracking-tight">
                  {tab.label}
                </span>
              </button>
            );
          })}
        </div>

        {/* Tab Content Display */}
        <div className="bg-slate-50/70 border border-slate-200/80 rounded-3xl p-6 sm:p-10 shadow-xs">
          
          {/* Active Tab Headline */}
          <div className="flex flex-col md:flex-row md:items-center justify-between pb-8 mb-8 border-b border-slate-200 gap-4">
            <div>
              <h3 className="text-2xl font-extrabold text-slate-900">
                {currentData.title}
              </h3>
              <p className="text-sm text-slate-500 mt-1">
                {currentData.subtitle}
              </p>
            </div>

            <div className="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-100/70 text-emerald-800 border border-emerald-200 shrink-0 self-start md:self-auto">
              <Clock className="w-3.5 h-3.5 text-emerald-600" />
              <span>Routine tests release within 4 - 8 Hours</span>
            </div>
          </div>

          {/* Test Groups Grid */}
          <div className="grid md:grid-cols-2 gap-6 lg:gap-8">
            {currentData.items.map((group, idx) => (
              <div key={idx} className="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-2xs">
                <h4 className="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                  <span className="w-2 h-2 rounded-full bg-cyan-600"></span>
                  {group.name}
                </h4>
                <ul className="space-y-2.5">
                  {group.tests.map((testItem, tIdx) => (
                    <li key={tIdx} className="flex items-start gap-2.5 text-xs sm:text-sm text-slate-700">
                      <CheckCircle2 className="w-4 h-4 text-cyan-600 shrink-0 mt-0.5" />
                      <span>{testItem}</span>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>

          {/* Patient Fasting & Prep Guidelines Callout */}
          <div className="mt-8 p-4 sm:p-5 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div className="flex items-start gap-3">
              <AlertCircle className="w-5 h-5 text-amber-700 shrink-0 mt-0.5" />
              <div className="text-xs sm:text-sm text-amber-900">
                <strong>Patient Preparation Notice:</strong> Blood Chemistry tests (FBS, Lipid Profile) require <strong>10 to 12 hours of strict fasting</strong> (water only). Whole abdomen ultrasound requires a full bladder.
              </div>
            </div>
            
            <button
              onClick={onOpenAppointment}
              className="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-xl font-semibold text-xs transition shrink-0 cursor-pointer shadow-xs"
            >
              Book Diagnostic Schedule
            </button>
          </div>

        </div>

      </div>
    </section>
  );
}