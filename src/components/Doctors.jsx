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
  UserCheck, 
  Calendar, 
  Clock, 
  Award, 
  Search, 
  GraduationCap, 
  ArrowRight,
  ShieldCheck
} from 'lucide-react';
import teamImg from '../assets/doctors.png';

export default function Doctors({ onOpenAppointment }) {
  const [selectedSpecialty, setSelectedSpecialty] = useState('All');

    // ============================================================================
    // STUB DATA: The following datasets represent placeholder clinical records.
    // Pending final validation and copy sign-off from Asclepius Medical Board.
    // ============================================================================
    
  const specialists = [
    {
      name: 'Dr. Roberto M. Santos, MD, FPCP',
      specialty: 'Internal Medicine & Adult Cardiology',
      department: 'Internal Medicine',
      schedule: 'Mon, Wed, Fri | 9:00 AM - 1:00 PM',
      room: 'Room 201 - Cardiology Suite',
      experience: '16+ Years Experience'
    },
    {
      name: 'Dr. Maria Elena C. Reyes, MD, DPPS',
      specialty: 'Pediatrics & Child Wellness',
      department: 'Pediatrics',
      schedule: 'Tue, Thu, Sat | 10:00 AM - 3:00 PM',
      room: 'Room 104 - Children’s Clinic',
      experience: '12+ Years Experience'
    },
    {
      name: 'Dr. Patricia Anne V. Lim, MD, FPOGS',
      specialty: 'Obstetrics & Gynecology (OB-GYN)',
      department: 'OB-GYN',
      schedule: 'Mon to Thu | 1:00 PM - 5:00 PM',
      room: 'Room 205 - Women’s Health',
      experience: '14+ Years Experience'
    },
    {
      name: 'Dr. Antonio Jose G. Dizon, MD, FPCS',
      specialty: 'General Surgery & Minor Procedures',
      department: 'Surgery',
      schedule: 'Wed, Sat | 8:00 AM - 12:00 PM',
      room: 'Room 302 - Minor OR & Surgical',
      experience: '18+ Years Experience'
    },
    {
      name: 'Dr. Katherine Joy B. Ramos, MD, FPSP',
      specialty: 'Clinical Pathology & Laboratory Medicine',
      department: 'Pathology & Lab',
      schedule: 'Daily | 7:00 AM - 4:00 PM',
      room: 'Diagnostic Pathology Lab',
      experience: '11+ Years Experience'
    },
    {
      name: 'Dr. Michael Francis T. Tan, MD, FPCR',
      specialty: 'Radiology & Diagnostic Ultrasound',
      department: 'Radiology',
      schedule: 'Mon to Sat | 8:00 AM - 2:00 PM',
      room: 'Imaging & Ultrasound Suite',
      experience: '15+ Years Experience'
    }
  ];

  const categories = ['All', 'Internal Medicine', 'Pediatrics', 'OB-GYN', 'Surgery', 'Radiology'];

  const filteredDoctors = selectedSpecialty === 'All' 
    ? specialists 
    : specialists.filter(d => d.department.includes(selectedSpecialty));

  return (
    <section id="doctors" className="py-20 lg:py-28 bg-slate-50 relative border-t border-slate-200/60">
      <div className="max-w-350 mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-3xl mb-12 text-left">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-cyan-100/70 border border-cyan-200 text-cyan-900 text-xs font-semibold uppercase tracking-wider mb-3">
            <UserCheck className="w-3.5 h-3.5 text-cyan-700" />
            <span>Medical Specialists & Physicians</span>
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Meet Our Board-Certified Doctors & Clinical Experts.
          </h2>
          <p className="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
            Our physicians undergo rigorous training and fellowship accreditations from leading medical institutions, ensuring you receive attentive, accurate, and evidence-based healthcare.
          </p>
        </div>

        {/* Featured Medical Team Banner (using Doctors.webp) */}
        <div className="mb-14 rounded-3xl overflow-hidden shadow-md border border-slate-200 bg-white grid lg:grid-cols-12 items-center">
          <div className="lg:col-span-5 h-64 sm:h-80 lg:h-full relative overflow-hidden bg-slate-100">
            <img 
              src={teamImg} 
              alt="Asclepius Medical Specialists Team" 
              className="w-full h-full object-cover object-center"
            />
          </div>
          <div className="lg:col-span-7 p-6 sm:p-10 text-left">
            <div className="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full text-xs font-bold mb-3 border border-emerald-200">
              <ShieldCheck className="w-3.5 h-3.5" />
              <span>Multi-Disciplinary Clinical Panel</span>
            </div>
            <h3 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
              Collaborative Patient Care
            </h3>
            <p className="mt-3 text-sm sm:text-base text-slate-600 leading-relaxed">
              Whether you need routine monitoring, specialized pediatric consultations, prenatal ultrasound interpretations, or corporate fitness certifications, our medical team works hand-in-hand with our diagnostic laboratory for swift medical assessment.
            </p>
            <div className="mt-6 flex flex-wrap items-center gap-4">
              <button
                onClick={onOpenAppointment}
                className="px-5 py-2.5 rounded-xl bg-cyan-700 hover:bg-cyan-800 text-white font-semibold text-sm shadow-xs transition cursor-pointer"
              >
                Inquire Doctor’s Schedule
              </button>
              <span className="text-xs text-slate-500 font-medium">
                Walk-ins & HMO cardholders welcome
              </span>
            </div>
          </div>
        </div>

        {/* Filter Badges */}
        <div className="flex flex-wrap items-center gap-2 mb-8">
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setSelectedSpecialty(cat)}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer ${
                selectedSpecialty === cat
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* Doctors Directory Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredDoctors.map((doc, idx) => (
            <div 
              key={idx}
              className="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between"
            >
              <div>
                {/* Doctor Specialty Badge */}
                <div className="flex items-center justify-between gap-2 mb-3">
                  <span className="text-[11px] font-bold uppercase tracking-wider text-cyan-800 bg-cyan-50 px-2.5 py-1 rounded-md border border-cyan-100">
                    {doc.department}
                  </span>
                  <span className="text-xs font-semibold text-slate-400">
                    {doc.experience}
                  </span>
                </div>

                <h4 className="text-lg font-bold text-slate-900 mb-1">
                  {doc.name}
                </h4>
                <p className="text-xs sm:text-sm font-medium text-slate-500 mb-5">
                  {doc.specialty}
                </p>

                {/* Schedule & Suite info */}
                <div className="space-y-2.5 pt-4 border-t border-slate-100 text-xs text-slate-600">
                  <div className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-cyan-600 shrink-0" />
                    <span><strong>Schedule:</strong> {doc.schedule}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Award className="w-4 h-4 text-cyan-600 shrink-0" />
                    <span><strong>Location:</strong> {doc.room}</span>
                  </div>
                </div>
              </div>

              {/* Action Button */}
              <div className="pt-5 mt-5 border-t border-slate-100">
                <button
                  onClick={onOpenAppointment}
                  className="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-cyan-50 text-slate-700 hover:text-cyan-800 text-xs font-bold transition cursor-pointer"
                >
                  <Calendar className="w-3.5 h-3.5 text-cyan-600" />
                  <span>Book Consultation</span>
                </button>
              </div>
            </div>
          ))}
        </div>

      </div>
    </section>
  );
}