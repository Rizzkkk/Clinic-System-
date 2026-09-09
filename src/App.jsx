import React, { useState } from 'react';
import Navbar from './components/Navbar';
import Hero from './components/Hero';

export default function App() {
  const [isAppointmentOpen, setIsAppointmentOpen] = useState(false);

  const handleOpenAppointment = () => {
    setIsAppointmentOpen(true);
    // Temporary alert until AppointmentModal is built
    alert('Magbubukas dito ang Appointment & Consultation Booking Form!');
  };

  return (
    <div className="min-h-screen bg-white text-slate-800 selection:bg-cyan-500 selection:text-white">
      {/* Navigation Bar s*/}
      <Navbar onOpenAppointment={handleOpenAppointment} />

      {/* Main Home / Hero Section */}
      <main>
        <Hero onOpenAppointment={handleOpenAppointment} />
      </main>
    </div>
  );
}