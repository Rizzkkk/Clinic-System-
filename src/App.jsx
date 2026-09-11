import React, { useState } from 'react';
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import About from './components/About';
import Services from './components/Services';
import Diagnostics from './components/Diagnostics';
import Doctors from './components/Doctors';
import HMOSection from './components/HMOSection';
import Footer from './components/Footer';
import AppointmentModal from './components/AppointmentModal';

export default function App() {
  const [isAppointmentOpen, setIsAppointmentOpen] = useState(false);

  const handleOpenAppointment = () => {
    setIsAppointmentOpen(true);
  };

  const handleCloseAppointment = () => {
    setIsAppointmentOpen(false);
  };

  return (
    <div className="min-h-screen bg-white text-slate-800 selection:bg-cyan-600 selection:text-white antialiased font-sans scroll-smooth">
      
      <Navbar onOpenAppointment={handleOpenAppointment} />

      <main>
        <Hero onOpenAppointment={handleOpenAppointment} />
        <About />
        <Services onOpenAppointment={handleOpenAppointment} />
        <Diagnostics onOpenAppointment={handleOpenAppointment} />
        <Doctors onOpenAppointment={handleOpenAppointment} />
        <HMOSection onOpenAppointment={handleOpenAppointment} />
      </main>

      <Footer onOpenAppointment={handleOpenAppointment} />
      <AppointmentModal 
        isOpen={isAppointmentOpen} 
        onClose={handleCloseAppointment} 
      />

    </div>
  );
}