<?php

namespace App\Services\AI\Security;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Admission;
use App\Models\Prescription;
use App\Models\MedicalRecord;
use App\Models\LabReport;
use Exception;

class RelationshipGuard
{
    /**
     * Verify whether a user holds at least one of the allowed roles.
     *
     * @param User $user
     * @param array<string> $allowedRoles
     * @return bool
     */
    public static function userHasAllowedRole(User $user, array $allowedRoles): bool
    {
        if (empty($allowedRoles)) {
            return true;
        }

        // Superadmin and Admin have broad staff/management capabilities
        if ($user->isAdmin() && (in_array('admin', $allowedRoles) || in_array('superadmin', $allowedRoles) || in_array('staff', $allowedRoles))) {
            return true;
        }

        if ($user->isDoctor() && in_array('doctor', $allowedRoles)) {
            return true;
        }

        if ($user->isStaff() && in_array('staff', $allowedRoles)) {
            return true;
        }

        if ($user->isPatient() && in_array('patient', $allowedRoles)) {
            return true;
        }

        if ($user->isDriver() && in_array('driver', $allowedRoles)) {
            return true;
        }

        // Check explicit roles via relation
        return $user->hasRole($allowedRoles);
    }

    /**
     * Resolve and enforce authenticated Patient profile.
     * Prevents IDOR by ignoring any LLM-supplied patient_id for patient accounts.
     *
     * @param User $user
     * @param int|null $requestedPatientId
     * @return Patient
     * @throws Exception
     */
    public static function resolveEnforcedPatient(User $user, ?int $requestedPatientId = null): Patient
    {
        // For patient users: strictly resolve the authenticated user's own profile
        if ($user->isPatient()) {
            $patient = $user->patient;

            if (!$patient && !empty($user->email)) {
                $patient = Patient::where('email', $user->email)->orWhere('user_id', $user->id)->first();
            }

            if (!$patient) {
                // Auto-provision patient record if missing for this user
                $nameParts = explode(' ', $user->name, 2);
                $patient = Patient::create([
                    'user_id' => $user->id,
                    'patient_code' => 'P-' . strtoupper(substr(uniqid(), -6)),
                    'first_name' => $nameParts[0] ?? 'Patient',
                    'last_name' => $nameParts[1] ?? 'User',
                    'email' => $user->email,
                    'phone' => 'N/A',
                    'dob' => '1995-01-01',
                    'gender' => 'other',
                    'blood_type' => 'O+',
                ]);
            }

            return $patient;
        }

        // For Doctors / Staff / Admins acting on behalf of a patient
        if ($requestedPatientId) {
            $patient = Patient::find($requestedPatientId);
            if (!$patient) {
                throw new Exception("Patient record not found for ID #{$requestedPatientId}.");
            }
            return $patient;
        }

        throw new Exception("Patient ID is required for administrative or clinical operations.");
    }

    /**
     * Resolve and enforce Doctor profile for doctor accounts.
     *
     * @param User $user
     * @return Doctor
     * @throws Exception
     */
    public static function resolveEnforcedDoctor(User $user): Doctor
    {
        $doctor = $user->doctor;

        if (!$doctor) {
            throw new Exception("Authenticated user does not have an active Doctor profile.");
        }

        return $doctor;
    }

    /**
     * Check if a Doctor has an active or historical clinical relationship with a patient.
     * Checks appointments, admissions, prescriptions, and medical records.
     *
     * @param User $doctorUser
     * @param int $patientId
     * @return bool
     */
    public static function doctorHasRelationshipWithPatient(User $doctorUser, int $patientId): bool
    {
        // Admins and Superadmins have system-wide oversight
        if ($doctorUser->isAdmin()) {
            return true;
        }

        $doctor = $doctorUser->doctor;
        if (!$doctor) {
            return false;
        }

        $doctorId = $doctor->id;

        // 1. Appointment relationship
        $hasAppointment = Appointment::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->exists();

        if ($hasAppointment) {
            return true;
        }

        // 2. Inpatient Admission relationship
        $hasAdmission = Admission::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->exists();

        if ($hasAdmission) {
            return true;
        }

        // 3. Prescription relationship
        $hasPrescription = Prescription::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->exists();

        if ($hasPrescription) {
            return true;
        }

        // 4. Medical Record relationship
        $hasMedicalRecord = MedicalRecord::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->exists();

        if ($hasMedicalRecord) {
            return true;
        }

        // 5. Diagnostic Lab Report relationship
        $hasLabReport = LabReport::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->exists();

        return $hasLabReport;
    }

    /**
     * Assert clinical relationship guard. Throws exception if access is denied.
     *
     * @param User $doctorUser
     * @param int $patientId
     * @throws Exception
     */
    public static function assertDoctorPatientRelationship(User $doctorUser, int $patientId): void
    {
        if (!self::doctorHasRelationshipWithPatient($doctorUser, $patientId)) {
            throw new Exception("Access Denied: You are not authorized to access records of patients not assigned to your care.");
        }
    }
}
