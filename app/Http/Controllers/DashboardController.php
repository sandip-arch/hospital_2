<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\LabReport;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Admission;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Medicine;
use App\Models\AuditLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return $this->superadminDashboard();
        }

        if ($user->isDoctor()) {
            return $this->doctorDashboard();
        }

        if ($user->isStaff()) {
            return $this->staffDashboard();
        }

        return $this->patientDashboard();
    }

    private function superadminDashboard()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'total_doctors' => Doctor::count(),
            'total_staff' => Staff::count(),
            'total_departments' => Department::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount_paid'),
            'unpaid_invoices_amount' => Invoice::where('status', 'unpaid')->sum('net_amount'),
            'occupied_beds' => Bed::where('status', 'occupied')->count(),
            'total_beds' => Bed::count(),
            'low_stock_medicines' => Medicine::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'pending_lab_reports' => LabReport::where('status', 'requested')->count(),
        ];

        $stats['bed_occupancy_rate'] = $stats['total_beds'] > 0 
            ? round(($stats['occupied_beds'] / $stats['total_beds']) * 100, 1) 
            : 0;

        $recentAppointments = Appointment::with(['patient', 'doctor.user', 'department'])
            ->orderBy('appointment_date', 'desc')
            ->take(6)
            ->get();

        $recentAdmissions = Admission::with(['patient', 'doctor.user', 'bed.room'])
            ->where('status', 'admitted')
            ->orderBy('admission_date', 'desc')
            ->take(5)
            ->get();

        $lowStockMedicines = Medicine::whereColumn('stock_quantity', '<=', 'reorder_level')->take(5)->get();
        $recentAuditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(6)->get();

        return view('dashboards.superadmin', compact('stats', 'recentAppointments', 'recentAdmissions', 'lowStockMedicines', 'recentAuditLogs'));
    }

    private function doctorDashboard()
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor) {
            // Fallback for doctor user without profile
            $doctor = Doctor::first();
        }

        $todayAppointments = Appointment::with(['patient', 'department'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', today())
            ->orderBy('time_slot')
            ->get();

        $upcomingAppointments = Appointment::with(['patient', 'department'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', '>', today())
            ->orderBy('appointment_date')
            ->take(6)
            ->get();

        $recentRecords = MedicalRecord::with('patient')
            ->where('doctor_id', $doctor->id)
            ->orderBy('visit_date', 'desc')
            ->take(5)
            ->get();

        $pendingLabReports = LabReport::with(['patient', 'test'])
            ->where('doctor_id', $doctor->id)
            ->where('status', '!=', 'completed')
            ->take(5)
            ->get();

        $doctorStats = [
            'today_count' => $todayAppointments->count(),
            'completed_today' => $todayAppointments->where('status', 'completed')->count(),
            'total_patients' => MedicalRecord::where('doctor_id', $doctor->id)->distinct('patient_id')->count('patient_id'),
            'total_prescriptions' => Prescription::where('doctor_id', $doctor->id)->count(),
        ];

        return view('dashboards.doctor', compact('doctor', 'todayAppointments', 'upcomingAppointments', 'recentRecords', 'pendingLabReports', 'doctorStats'));
    }

    private function staffDashboard()
    {
        $user = Auth::user();
        $staff = $user->staff;

        $stats = [
            'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
            'available_beds' => Bed::where('status', 'available')->count(),
            'occupied_beds' => Bed::where('status', 'occupied')->count(),
            'pending_prescriptions' => Prescription::where('status', 'active')->count(),
            'pending_labs' => LabReport::whereIn('status', ['requested', 'in_progress'])->count(),
            'unpaid_bills' => Invoice::where('status', 'unpaid')->count(),
        ];

        $todayAppointments = Appointment::with(['patient', 'doctor.user', 'department'])
            ->whereDate('appointment_date', today())
            ->orderBy('time_slot')
            ->take(8)
            ->get();

        $activePrescriptions = Prescription::with(['patient', 'doctor.user', 'items.medicine'])
            ->where('status', 'active')
            ->take(5)
            ->get();

        $pendingLabs = LabReport::with(['patient', 'test', 'doctor.user'])
            ->whereIn('status', ['requested', 'in_progress'])
            ->take(5)
            ->get();

        $recentInvoices = Invoice::with('patient')
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.staff', compact('staff', 'stats', 'todayAppointments', 'activePrescriptions', 'pendingLabs', 'recentInvoices'));
    }

    private function patientDashboard()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            // Auto-create patient profile if user is patient
            $count = Patient::count() + 1;
            $upi = 'PAT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $names = explode(' ', $user->name, 2);

            $patient = Patient::create([
                'user_id' => $user->id,
                'patient_code' => $upi,
                'first_name' => $names[0] ?? 'Patient',
                'last_name' => $names[1] ?? 'User',
                'dob' => '1995-01-01',
                'gender' => 'Other',
                'phone' => '+1 (555) 000-0000',
                'email' => $user->email,
            ]);
        }

        $upcomingAppointments = Appointment::with(['doctor.user', 'department'])
            ->where('patient_id', $patient->id)
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->get();

        $pastMedicalRecords = MedicalRecord::with(['doctor.user', 'details'])
            ->where('patient_id', $patient->id)
            ->orderBy('visit_date', 'desc')
            ->get();

        $prescriptions = Prescription::with(['doctor.user', 'items.medicine'])
            ->where('patient_id', $patient->id)
            ->orderBy('prescribed_date', 'desc')
            ->get();

        $labReports = LabReport::with(['test', 'doctor.user'])
            ->where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $invoices = Invoice::with(['items', 'payments'])
            ->where('patient_id', $patient->id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('dashboards.patient', compact('patient', 'upcomingAppointments', 'pastMedicalRecords', 'prescriptions', 'labReports', 'invoices'));
    }
}
