<?php
// The patient-portal role values, in one place. Both the auth chokepoint (bootstrap.php) and the
// login form (login.php, which deliberately does NOT include bootstrap) have to recognize them,
// and a portal role appearing in only one of the two would either lock a patient out or route
// them into the staff application.
//
// Staff roles are still spelled out inline at their existing call sites; consolidating those is a
// separate refactor and is not part of the portal work.
//
//   patient_pending   signed up, awaiting reception review. users.patientId IS NULL.
//   patient           verified and linked. users.patientId IS NOT NULL.
//   patient_rejected  denied, or unlinked after a bad match. users.patientId IS NULL.
const PORTAL_ROLES = ['patient', 'patient_pending', 'patient_rejected'];
