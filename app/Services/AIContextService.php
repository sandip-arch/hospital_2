<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\LabReport;
use App\Models\Admission;
use App\Models\Invoice;

class AIContextService
{
    /**
     * Build the minimum live database context
     * required to answer the user's question.
     *
     * IMPORTANT:
     * Laravel remains the authorization authority.
     * AI never receives unrestricted database access.
     */
    public function build(User $user, string $message): array
    {
        $message = strtolower(trim($message));

        $context = [
            'source' => 'laravel_live_context',
            'data_available' => false,
            'data' => [],
        ];

        /*
         * ========================================
         * APPOINTMENTS
         * ========================================
         */
        if ($this->mentionsAppointment($message)) {

            $appointments = $this->getAuthorizedAppointments($user);

            if ($appointments !== null) {
                $context['data_available'] = true;
                $context['data']['appointments'] = $appointments;
            }
        }

        /*
         * ========================================
         * MEDICAL RECORDS
         * ========================================
         */
        if ($this->mentionsMedicalRecord($message)) {

            $records = $this->getAuthorizedMedicalRecords($user);

            if ($records !== null) {
                $context['data_available'] = true;
                $context['data']['medical_records'] = $records;
            }
        }

        /*
         * ========================================
         * PRESCRIPTIONS
         * ========================================
         */
        if ($this->mentionsPrescription($message)) {

            $prescriptions = $this->getAuthorizedPrescriptions($user);

            if ($prescriptions !== null) {
                $context['data_available'] = true;
                $context['data']['prescriptions'] = $prescriptions;
            }
        }

        /*
         * ========================================
         * LAB REPORTS
         * ========================================
         */
        if ($this->mentionsLabReport($message)) {

            $reports = $this->getAuthorizedLabReports($user);

            if ($reports !== null) {
                $context['data_available'] = true;
                $context['data']['lab_reports'] = $reports;
            }
        }

        /*
         * ========================================
         * ADMISSIONS
         * ========================================
         */
        if ($this->mentionsAdmission($message)) {

            $admissions = $this->getAuthorizedAdmissions($user);

            if ($admissions !== null) {
                $context['data_available'] = true;
                $context['data']['admissions'] = $admissions;
            }
        }

        /*
         * ========================================
         * BILLING / INVOICES
         * ========================================
         */
        if ($this->mentionsBilling($message)) {

            $invoices = $this->getAuthorizedInvoices($user);

            if ($invoices !== null) {
                $context['data_available'] = true;
                $context['data']['invoices'] = $invoices;
            }
        }

        return $context;
    }


    /*
     * ============================================
     * APPOINTMENT AUTHORIZATION
     * ============================================
     */

    private function getAuthorizedAppointments(User $user): ?array
    {
        /*
         * Patient:
         * Only their own appointments.
         */
        if ($user->isPatient() && $user->patient) {

            return Appointment::where(
                'patient_id',
                $user->patient->id
            )
            ->with([
                'doctor.user',
                'department',
            ])
            ->latest('appointment_date')
            ->limit(20)
            ->get()
            ->map(function ($appointment) {

                return [
                    'date' => optional($appointment->appointment_date)?->format('Y-m-d'),
                    'time_slot' => $appointment->time_slot,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'doctor' => $appointment->doctor?->user?->name,
                    'department' => $appointment->department?->name,
                ];

            })
            ->values()
            ->all();
        }

        /*
         * Doctor:
         * Only appointments assigned to this doctor.
         */
        if ($user->isDoctor() && $user->doctor) {

            return Appointment::where(
                'doctor_id',
                $user->doctor->id
            )
            ->with([
                'patient',
                'department',
            ])
            ->latest('appointment_date')
            ->limit(50)
            ->get()
            ->map(function ($appointment) {

                return [
                    'date' => optional($appointment->appointment_date)?->format('Y-m-d'),
                    'time_slot' => $appointment->time_slot,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'patient' => $appointment->patient?->getFullNameAttribute(),
                    'department' => $appointment->department?->name,
                ];

            })
            ->values()
            ->all();
        }

        /*
         * Admin / Superadmin:
         *
         * We intentionally do NOT automatically expose
         * every appointment yet.
         *
         * Your application's exact admin data-access
         * policy should determine this later.
         */
        if ($user->isAdmin()) {

            return Appointment::with([
                'patient',
                'doctor.user',
                'department',
            ])
            ->latest('appointment_date')
            ->limit(100)
            ->get()
            ->map(function ($appointment) {

                return [
                    'date' => optional($appointment->appointment_date)?->format('Y-m-d'),
                    'time_slot' => $appointment->time_slot,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'patient' => $appointment->patient?->getFullNameAttribute(),
                    'doctor' => $appointment->doctor?->user?->name,
                    'department' => $appointment->department?->name,
                ];

            })
            ->values()
            ->all();
        }

        return null;
    }


    /*
     * ============================================
     * MEDICAL RECORD AUTHORIZATION
     * ============================================
     */

    private function getAuthorizedMedicalRecords(User $user): ?array
    {
        /*
         * Patient → own records only.
         */
        if ($user->isPatient() && $user->patient) {

            return MedicalRecord::where(
                'patient_id',
                $user->patient->id
            )
            ->with([
                'doctor.user',
                'details',
            ])
            ->latest('visit_date')
            ->limit(20)
            ->get()
            ->map(function ($record) {

                return [
                    'visit_date' => optional($record->visit_date)?->format('Y-m-d H:i:s'),
                    'diagnosis' => $record->diagnosis,
                    'symptoms' => $record->symptoms,
                    'notes' => $record->notes,
                    'doctor' => $record->doctor?->user?->name,
                    'vitals' => $record->details->map(function ($detail) {
                        return [
                            'name' => $detail->vital_sign_name,
                            'value' => $detail->vital_sign_value,
                        ];
                    })->values()->all(),
                ];

            })
            ->values()
            ->all();
        }

        /*
         * Doctor → records belonging to patients
         * associated with this doctor.
         *
         * We use doctor_id because MedicalRecord
         * explicitly contains that relationship.
         */
        if ($user->isDoctor() && $user->doctor) {

            return MedicalRecord::where(
                'doctor_id',
                $user->doctor->id
            )
            ->with([
                'patient',
            ])
            ->latest('visit_date')
            ->limit(50)
            ->get()
            ->map(function ($record) {

                return [
                    'visit_date' => optional($record->visit_date)?->format('Y-m-d H:i:s'),
                    'patient' => $record->patient?->getFullNameAttribute(),
                    'diagnosis' => $record->diagnosis,
                    'symptoms' => $record->symptoms,
                    'notes' => $record->notes,
                ];

            })
            ->values()
            ->all();
        }

        /*
         * We do NOT automatically expose medical records
         * to staff/receptionists.
         */
        return null;
    }


    /*
     * ============================================
     * PRESCRIPTIONS
     * ============================================
     */

    private function getAuthorizedPrescriptions(User $user): ?array
    {
        if ($user->isPatient() && $user->patient) {

            return Prescription::where(
                'patient_id',
                $user->patient->id
            )
            ->with([
                'doctor.user',
                'items',
            ])
            ->latest('prescribed_date')
            ->limit(20)
            ->get()
            ->map(function ($prescription) {

                return [
                    'date' => optional($prescription->prescribed_date)?->format('Y-m-d'),
                    'status' => $prescription->status,
                    'notes' => $prescription->notes,
                    'doctor' => $prescription->doctor?->user?->name,
                    'items' => $prescription->items->map(function ($item) {
                        return $item->toArray();
                    })->values()->all(),
                ];

            })
            ->values()
            ->all();
        }

        if ($user->isDoctor() && $user->doctor) {

            return Prescription::where(
                'doctor_id',
                $user->doctor->id
            )
            ->with([
                'patient',
                'items',
            ])
            ->latest('prescribed_date')
            ->limit(50)
            ->get()
            ->map(function ($prescription) {

                return [
                    'date' => optional($prescription->prescribed_date)?->format('Y-m-d'),
                    'status' => $prescription->status,
                    'patient' => $prescription->patient?->getFullNameAttribute(),
                    'notes' => $prescription->notes,
                    'items' => $prescription->items->map(function ($item) {
                        return $item->toArray();
                    })->values()->all(),
                ];

            })
            ->values()
            ->all();
        }

        return null;
    }


    /*
     * ============================================
     * LAB REPORTS
     * ============================================
     */

    private function getAuthorizedLabReports(User $user): ?array
    {
        if ($user->isPatient() && $user->patient) {

            return LabReport::where(
                'patient_id',
                $user->patient->id
            )
            ->with([
                'test',
                'doctor.user',
            ])
            ->latest('report_date')
            ->limit(20)
            ->get()
            ->map(function ($report) {

                return [
                    'date' => optional($report->report_date)?->format('Y-m-d H:i:s'),
                    'status' => $report->status,
                    'test' => $report->test?->name,
                    'result_summary' => $report->result_summary,
                    'doctor' => $report->doctor?->user?->name,
                ];

            })
            ->values()
            ->all();
        }

        if ($user->isDoctor() && $user->doctor) {

            return LabReport::where(
                'doctor_id',
                $user->doctor->id
            )
            ->with([
                'patient',
                'test',
            ])
            ->latest('report_date')
            ->limit(50)
            ->get()
            ->map(function ($report) {

                return [
                    'date' => optional($report->report_date)?->format('Y-m-d H:i:s'),
                    'patient' => $report->patient?->getFullNameAttribute(),
                    'status' => $report->status,
                    'test' => $report->test?->name,
                    'result_summary' => $report->result_summary,
                ];

            })
            ->values()
            ->all();
        }

        return null;
    }


    /*
     * ============================================
     * ADMISSIONS
     * ============================================
     */

    private function getAuthorizedAdmissions(User $user): ?array
    {
        if ($user->isPatient() && $user->patient) {

            return Admission::where(
                'patient_id',
                $user->patient->id
            )
            ->with([
                'doctor.user',
                'bed.room.department',
            ])
            ->latest('admission_date')
            ->limit(20)
            ->get()
            ->map(function ($admission) {

                return [
                    'admission_date' => optional($admission->admission_date)?->format('Y-m-d H:i:s'),
                    'discharge_date' => optional($admission->discharge_date)?->format('Y-m-d H:i:s'),
                    'status' => $admission->status,
                    'admission_reason' => $admission->admission_reason,
                    'discharge_notes' => $admission->discharge_notes,
                    'doctor' => $admission->doctor?->user?->name,
                ];

            })
            ->values()
            ->all();
        }

        if ($user->isDoctor() && $user->doctor) {

            return Admission::where(
                'doctor_id',
                $user->doctor->id
            )
            ->with([
                'patient',
            ])
            ->latest('admission_date')
            ->limit(50)
            ->get()
            ->map(function ($admission) {

                return [
                    'admission_date' => optional($admission->admission_date)?->format('Y-m-d H:i:s'),
                    'discharge_date' => optional($admission->discharge_date)?->format('Y-m-d H:i:s'),
                    'status' => $admission->status,
                    'patient' => $admission->patient?->getFullNameAttribute(),
                    'admission_reason' => $admission->admission_reason,
                    'discharge_notes' => $admission->discharge_notes,
                ];

            })
            ->values()
            ->all();
        }

        return null;
    }


    /*
     * ============================================
     * INVOICES / BILLING
     * ============================================
     */

    private function getAuthorizedInvoices(User $user): ?array
    {
        if ($user->isPatient() && $user->patient) {

            return Invoice::where(
                'patient_id',
                $user->patient->id
            )
            ->with([
                'payments',
            ])
            ->latest('invoice_date')
            ->limit(20)
            ->get()
            ->map(function ($invoice) {

                return [
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => optional($invoice->invoice_date)?->format('Y-m-d'),
                    'due_date' => optional($invoice->due_date)?->format('Y-m-d'),
                    'net_amount' => (float) $invoice->net_amount,
                    'status' => $invoice->status,
                    'paid_amount' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                    'payments' => $invoice->payments->map(function ($payment) {

                        return [
                            'date' => optional($payment->payment_date)?->format('Y-m-d H:i:s'),
                            'amount' => (float) $payment->amount_paid,
                            'method' => $payment->payment_method,
                            'status' => $payment->status,
                        ];

                    })->values()->all(),
                ];

            })
            ->values()
            ->all();
        }

        /*
         * Billing access for other roles is deliberately
         * not enabled until we verify your application's
         * actual permission rules.
         */
        return null;
    }


    /*
     * ============================================
     * INTENT DETECTION
     * ============================================
     */

    private function mentionsAppointment(string $message): bool
    {
        return $this->containsAny($message, [
            'appointment',
            'appointments',
            'booking',
            'bookings',
            'scheduled',
            'schedule',
        ]);
    }

    private function mentionsMedicalRecord(string $message): bool
    {
        return $this->containsAny($message, [
            'medical record',
            'medical records',
            'diagnosis',
            'symptoms',
            'vital',
            'vitals',
        ]);
    }

    private function mentionsPrescription(string $message): bool
    {
        return $this->containsAny($message, [
            'prescription',
            'prescriptions',
            'medicine',
            'medicines',
            'medication',
            'medications',
        ]);
    }

    private function mentionsLabReport(string $message): bool
    {
        return $this->containsAny($message, [
            'lab report',
            'lab reports',
            'laboratory',
            'test result',
            'test results',
        ]);
    }

    private function mentionsAdmission(string $message): bool
    {
        return $this->containsAny($message, [
            'admission',
            'admissions',
            'admitted',
            'discharge',
            'discharged',
            'bed',
        ]);
    }

    private function mentionsBilling(string $message): bool
    {
        return $this->containsAny($message, [
            'invoice',
            'invoices',
            'billing',
            'bill',
            'payment',
            'payments',
            'paid',
            'balance',
            'due',
        ]);
    }

    private function containsAny(string $message, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($message, $term)) {
                return true;
            }
        }

        return false;
    }
}