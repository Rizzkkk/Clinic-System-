


import React, { useState } from 'react';
import { 
  X, 
  Calendar, 
  Clock, 
  User, 
  Phone, 
  Mail, 
  Stethoscope, 
  CreditCard, 
  CheckCircle2, 
  AlertCircle 
} from 'lucide-react';

export default function AppointmentModal({ isOpen, onClose }) {
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [formData, setFormData] = useState({
    fullName: '',
    phone: '',
    email: '',
    serviceType: 'Consultation',
    hmoProvider: 'None / Cash',
    preferredDate: '',
    timeSlot: 'Morning (8:00 AM - 12:00 PM)',
    notes: ''
  });

  if (!isOpen) return null;

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Simulate booking submission
    setIsSubmitted(true);
  };

  const handleReset = () => {
    setIsSubmitted(false);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6 animate-in fade-in duration-200">
      
      {/* Modal Container */}
      <div className="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
        
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-full transition cursor-pointer z-10"
          aria-label="Close modal"
        >
          <X className="w-5 h-5" />
        </button>

        {isSubmitted ? (
          /* Success Screen */
          <div className="p-8 sm:p-12 text-center space-y-4">
            <div className="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
              <CheckCircle2 className="w-10 h-10" />
            </div>
            <h3 className="text-2xl font-extrabold text-slate-900">
              Appointment Request Sent!
            </h3>
            <p className="text-sm text-slate-600 leading-relaxed max-w-md mx-auto">
              Thank you, <strong>{formData.fullName || 'Patient'}</strong>. Our patient coordination desk has received your request. We will contact you via <strong>{formData.phone}</strong> shortly to confirm your doctor's availability and schedule.
            </p>
            <div className="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-500 text-left space-y-1">
              <p><strong>Reference Request:</strong> #{Math.floor(100000 + Math.random() * 900000)}</p>
              <p><strong>Service:</strong> {formData.serviceType}</p>
              <p><strong>Preferred Slot:</strong> {formData.preferredDate || 'Earliest available'} ({formData.timeSlot})</p>
            </div>
            <div className="pt-4">
              <button
                onClick={handleReset}
                className="w-full py-3 px-6 rounded-xl bg-cyan-700 hover:bg-cyan-800 text-white font-bold text-sm shadow transition cursor-pointer"
              >
                Close & Return to Website
              </button>
            </div>
          </div>
        ) : (
          /* Booking Form */
          <div>
            {/* Modal Header */}
            <div className="bg-linear-to-r from-cyan-900 to-slate-900 text-white p-6 sm:p-8">
              <span className="inline-block px-2.5 py-0.5 rounded-full bg-cyan-800/80 text-cyan-200 text-[11px] font-bold uppercase tracking-wider mb-2">
                Fast Outpatient Scheduling
              </span>
              <h3 className="text-xl sm:text-2xl font-extrabold">
                Book an Appointment or Inquire
              </h3>
              <p className="text-xs sm:text-sm text-cyan-100 mt-1">
                Fill out this quick form and our clinic staff will reach out to confirm your slot.
              </p>
            </div>

            {/* Form Fields */}
            <form onSubmit={handleSubmit} className="p-6 sm:p-8 space-y-4">
              
              {/* Patient Name */}
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Patient Full Name *
                </label>
                <div className="relative">
                  <User className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                  <input
                    type="text"
                    required
                    name="fullName"
                    value={formData.fullName}
                    onChange={handleChange}
                    placeholder="e.g., Juan Dela Cruz"
                    className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:border-transparent transition"
                  />
                </div>
              </div>

              {/* Contact Information (Grid) */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Mobile Number *
                  </label>
                  <div className="relative">
                    <Phone className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                    <input
                      type="tel"
                      required
                      name="phone"
                      value={formData.phone}
                      onChange={handleChange}
                      placeholder="0917 123 4567"
                      className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:border-transparent transition"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Email Address
                  </label>
                  <div className="relative">
                    <Mail className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                    <input
                      type="email"
                      name="email"
                      value={formData.email}
                      onChange={handleChange}
                      placeholder="patient@email.com"
                      className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:border-transparent transition"
                    />
                  </div>
                </div>
              </div>

              {/* Service & HMO Selection */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Service Required *
                  </label>
                  <select
                    name="serviceType"
                    value={formData.serviceType}
                    onChange={handleChange}
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-white"
                  >
                    <option value="Specialist Consultation">Specialist Doctor Consultation</option>
                    <option value="Laboratory Blood Test">Laboratory / Blood Chemistry Test</option>
                    <option value="Digital X-Ray">Digital Chest / Bone X-Ray</option>
                    <option value="Ultrasound / Sonology">Ultrasound / Sonology</option>
                    <option value="12-Lead ECG / 2D-Echo">12-Lead ECG or 2D-Echo</option>
                    <option value="Executive Checkup Package">Executive Health Package</option>
                    <option value="Corporate / Pre-Employment">Pre-Employment (PEME) / APE</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Payment / HMO Method
                  </label>
                  <select
                    name="hmoProvider"
                    value={formData.hmoProvider}
                    onChange={handleChange}
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-white"
                  >
                    <option value="Cash / Self-Pay">Cash / Card (Self-Pay)</option>
                    <option value="Maxicare">Maxicare Healthcare</option>
                    <option value="Intellicare">Intellicare / Asalus</option>
                    <option value="Medicard">Medicard Philippines</option>
                    <option value="PhilHealth">PhilHealth Covered</option>
                    <option value="Other HMO">Other Accredited HMO</option>
                  </select>
                </div>
              </div>

              {/* Preferred Date & Time */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Preferred Date
                  </label>
                  <input
                    type="date"
                    name="preferredDate"
                    value={formData.preferredDate}
                    onChange={handleChange}
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-white"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Preferred Time Window
                  </label>
                  <select
                    name="timeSlot"
                    value={formData.timeSlot}
                    onChange={handleChange}
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-white"
                  >
                    <option>Morning (6:30 AM - 12:00 PM)</option>
                    <option>Afternoon (1:00 PM - 5:00 PM)</option>
                    <option>Evening (5:00 PM - 7:00 PM)</option>
                  </select>
                </div>
              </div>

              {/* Submit Button */}
              <div className="pt-3">
                <button
                  type="submit"
                  className="w-full py-3.5 px-6 rounded-xl bg-linear-to-r from-cyan-600 via-sky-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold text-sm shadow-md hover:shadow-lg transition cursor-pointer"
                >
                  Submit Appointment Request
                </button>
                <p className="text-[11px] text-slate-400 text-center mt-2">
                  No online payment needed. Confirmation is processed by clinic staff.
                </p>
              </div>

            </form>
          </div>
        )}

      </div>
    </div>
  );
}