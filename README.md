# ASCLEPIUS MEDICAL & DIAGNOSTIC GROUP INC. — Official Website Landing Page

A modern, high-performance patient-facing landing page for **Asclepius Medical & Diagnostic Group Inc.** Built with React 19, Tailwind CSS v4, Lucide Icons, and Motion.

---

## 🏥 Project Overview
* **Client / Institution:** Asclepius Medical & Diagnostic Group Inc.
* **Goal:** Provide patients and visitors an accessible, professional, and responsive platform to discover medical services, diagnostic examinations, doctors' schedules, HMO affiliations, clinic hours, emergency contacts, and direct access to the hospital's Admin & Staff Portal.
* **Target Audience:** Outpatients, corporate check-up clients, HMO cardholders, and walk-in diagnostic patients.

---

## 🎨 Design System & Palette
* **Primary Brand Tones:** Medical Cyan (`#0891b2`), Sky Blue (`#0284c7`), Deep Navy (`#0f172a`)
* **Accents:** Emerald Green (Accreditation / Health trust) & Soft Coral (Emergency hotline)
* **Typography & Layout:** Clean sans-serif, spacious container (`max-w-[1400px]`), glassmorphic badges, and accessible contrast ratios.

---

## 🏗️ Architecture & Component Roadmap

```text
src/
├── assets/                  # Logos and photography
│   ├── ASCLEPIUS.jpg        # Official hospital emblem/logo
│   ├── Hero.png             # Medical care hero banner image
│   └── Doctors.webp         # Featured clinical staff & specialists
├── components/
│   ├── Navbar.jsx           # [COMPLETED] Top emergency bar, logo, nav anchors, Admin Portal link
│   ├── Hero.jsx             # [COMPLETED] Main headline, CTAs, trust badges, floating cards & quick stats
│   ├── About.jsx            # [PLANNED] Corporate profile, mission, vision, modern facilities
│   ├── Services.jsx         # [PLANNED] Outpatient clinics (Cardiology, Pedia, OB-GYN, Dental, etc.)
│   ├── Diagnostics.jsx      # [PLANNED] Laboratory packages, X-Ray, 4D Ultrasound, ECG, Blood Chemistry
│   ├── Doctors.jsx          # [PLANNED] Specialist roster, doctor schedules, credentials
│   ├── HMOSection.jsx       # [PLANNED] Accredited health cards (Maxicare, Intellicare, PhilHealth, etc.)
│   ├── AppointmentModal.jsx # [PLANNED] Interactive booking & inquiry modal
│   └── Footer.jsx           # [PLANNED] Operating hours, branch map/location, hotline, copyright
├── App.jsx                  # Main page assembler
├── index.css                # Global styles & Tailwind v4 directive
└── main.jsx                 # Vite application entry