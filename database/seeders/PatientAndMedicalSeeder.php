<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Patient;
use App\Models\EmergencyContact;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordDetail;
use App\Models\Bed;
use App\Models\Admission;

class PatientAndMedicalSeeder extends Seeder
{
    public function run(): void
    {
        $patientRole = Role::where('name', 'patient')->first();
        $drSarah = Doctor::where('specialization', 'like', '%Cardiologist%')->first() ?? Doctor::first();
        $drJames = Doctor::where('specialization', 'like', '%Neurologist%')->first() ?? Doctor::skip(1)->first() ?? $drSarah;
        $drEmily = Doctor::where('specialization', 'like', '%Pediatrician%')->first() ?? Doctor::skip(2)->first() ?? $drSarah;
        $drRobert = Doctor::where('specialization', 'like', '%Orthopedic%')->first() ?? Doctor::skip(3)->first() ?? $drSarah;

        $cardiology = Department::where('code', 'CARD')->first() ?? Department::first();
        $neurology = Department::where('code', 'NEUR')->first() ?? Department::first();
        $pediatrics = Department::where('code', 'PED')->first() ?? Department::first();
        $orthopedics = Department::where('code', 'ORTH')->first() ?? Department::first();
        $emergency = Department::where('code', 'EMER')->first() ?? Department::first();

        // 1. Patient Accounts & Profiles
        $patientsData = [
            [
                'email' => 'patient.john@hospital.test',
                'username' => 'patient_john',
                'name' => 'Johnathan Doe',
                'first_name' => 'Johnathan',
                'last_name' => 'Doe',
                'code' => 'PAT-2026-0001',
                'dob' => '1988-05-14',
                'gender' => 'Male',
                'blood_type' => 'O+',
                'phone' => '+1 (555) 789-0123',
                'address' => '742 Evergreen Terrace, Springfield, IL 62704',
                'history' => 'Hypertension Stage 1 diagnosed in 2021. Mild penicillin allergy. Non-smoker.',
                'contact' => ['name' => 'Mary Doe', 'rel' => 'Spouse', 'phone' => '+1 (555) 789-0124', 'alt' => '+1 (555) 789-0125'],
            ],
            [
                'email' => 'patient.emma@hospital.test',
                'username' => 'patient_emma',
                'name' => 'Emma Watson',
                'first_name' => 'Emma',
                'last_name' => 'Watson',
                'code' => 'PAT-2026-0002',
                'dob' => '1995-11-20',
                'gender' => 'Female',
                'blood_type' => 'A+',
                'phone' => '+1 (555) 890-1234',
                'address' => '12 Grimmauld Place, Boston, MA 02108',
                'history' => 'Seasonal allergic rhinitis. Chronic migraine aura. No prior surgeries.',
                'contact' => ['name' => 'Chris Watson', 'rel' => 'Brother', 'phone' => '+1 (555) 890-1235', 'alt' => null],
            ],
            [
                'email' => 'robert.brown@example.com',
                'username' => null,
                'name' => 'Robert Brown',
                'first_name' => 'Robert',
                'last_name' => 'Brown',
                'code' => 'PAT-2026-0003',
                'dob' => '1965-03-08',
                'gender' => 'Male',
                'blood_type' => 'B+',
                'phone' => '+1 (555) 901-2345',
                'address' => '404 Baker Street, New York, NY 10001',
                'history' => 'Type 2 Diabetes Mellitus on Metformin. Coronary artery stent (LAD) in 2019.',
                'contact' => ['name' => 'Laura Brown', 'rel' => 'Daughter', 'phone' => '+1 (555) 901-2346', 'alt' => '+1 (555) 901-2347'],
            ],
            [
                'email' => 'olivia.davis@example.com',
                'username' => null,
                'name' => 'Olivia Davis',
                'first_name' => 'Olivia',
                'last_name' => 'Davis',
                'code' => 'PAT-2026-0004',
                'dob' => '2018-07-22',
                'gender' => 'Female',
                'blood_type' => 'O-',
                'phone' => '+1 (555) 345-6711',
                'address' => '88 Willow Creek Lane, Cambridge, MA 02138',
                'history' => 'Childhood bronchial asthma. Fully vaccinated per CDC schedule. Peanut allergy.',
                'contact' => ['name' => 'Marcus Davis', 'rel' => 'Father', 'phone' => '+1 (555) 345-6712', 'alt' => null],
            ],
            [
                'email' => 'william.taylor@example.com',
                'username' => null,
                'name' => 'William Taylor',
                'first_name' => 'William',
                'last_name' => 'Taylor',
                'code' => 'PAT-2026-0005',
                'dob' => '1976-09-12',
                'gender' => 'Male',
                'blood_type' => 'AB+',
                'phone' => '+1 (555) 456-7822',
                'address' => '312 Beacon Hill Road, Boston, MA 02116',
                'history' => 'Right knee osteoarthritis Grade 3. Post-arthroscopy status (2022).',
                'contact' => ['name' => 'Sarah Taylor', 'rel' => 'Spouse', 'phone' => '+1 (555) 456-7823', 'alt' => null],
            ],
            [
                'email' => 'sophia.martinez@example.com',
                'username' => null,
                'name' => 'Sophia Martinez',
                'first_name' => 'Sophia',
                'last_name' => 'Martinez',
                'code' => 'PAT-2026-0006',
                'dob' => '1992-04-30',
                'gender' => 'Female',
                'blood_type' => 'A-',
                'phone' => '+1 (555) 567-8933',
                'address' => '540 Commonwealth Ave, Boston, MA 02215',
                'history' => 'Hypothyroidism on Levothyroxine 75mcg daily. Sulfa drugs intolerance.',
                'contact' => ['name' => 'Carlos Martinez', 'rel' => 'Brother', 'phone' => '+1 (555) 567-8934', 'alt' => null],
            ],
            [
                'email' => 'david.clark@example.com',
                'username' => null,
                'name' => 'David Clark',
                'first_name' => 'David',
                'last_name' => 'Clark',
                'code' => 'PAT-2026-0007',
                'dob' => '1958-12-05',
                'gender' => 'Male',
                'blood_type' => 'O+',
                'phone' => '+1 (555) 678-9044',
                'address' => '71 Harvard Yard Way, Cambridge, MA 02139',
                'history' => 'Atrial Fibrillation on Apixaban. Hyperlipidemia on Rosuvastatin.',
                'contact' => ['name' => 'Judith Clark', 'rel' => 'Spouse', 'phone' => '+1 (555) 678-9045', 'alt' => null],
            ],
            [
                'email' => 'charlotte.lee@example.com',
                'username' => null,
                'name' => 'Charlotte Lee',
                'first_name' => 'Charlotte',
                'last_name' => 'Lee',
                'code' => 'PAT-2026-0008',
                'dob' => '2001-08-19',
                'gender' => 'Female',
                'blood_type' => 'B-',
                'phone' => '+1 (555) 789-0155',
                'address' => '190 Tremont St, Boston, MA 02111',
                'history' => 'Anxiety disorder. Iron deficiency anemia managed with oral supplements.',
                'contact' => ['name' => 'Grace Lee', 'rel' => 'Mother', 'phone' => '+1 (555) 789-0156', 'alt' => null],
            ],
            [
                'email' => 'james.rodriguez@example.com',
                'username' => null,
                'name' => 'James Rodriguez',
                'first_name' => 'James',
                'last_name' => 'Rodriguez',
                'code' => 'PAT-2026-0009',
                'dob' => '1984-02-17',
                'gender' => 'Male',
                'blood_type' => 'A+',
                'phone' => '+1 (555) 890-1266',
                'address' => '224 Newbury St, Boston, MA 02116',
                'history' => 'Lumbar disc herniation L4-L5. NSAID gastritis history.',
                'contact' => ['name' => 'Elena Rodriguez', 'rel' => 'Spouse', 'phone' => '+1 (555) 890-1267', 'alt' => null],
            ],
            [
                'email' => 'grace.kim@example.com',
                'username' => null,
                'name' => 'Grace Kim',
                'first_name' => 'Grace',
                'last_name' => 'Kim',
                'code' => 'PAT-2026-0010',
                'dob' => '2020-10-10',
                'gender' => 'Female',
                'blood_type' => 'O+',
                'phone' => '+1 (555) 901-2377',
                'address' => '45 Boylston St, Chestnut Hill, MA 02467',
                'history' => 'Recurrent acute otitis media. No known drug allergies.',
                'contact' => ['name' => 'Hanna Kim', 'rel' => 'Mother', 'phone' => '+1 (555) 901-2378', 'alt' => null],
            ],
        ];

        $patients = [];
        foreach ($patientsData as $pData) {
            $userId = null;
            if ($pData['username']) {
                $user = User::updateOrCreate(
                    ['email' => $pData['email']],
                    [
                        'name' => $pData['name'],
                        'username' => $pData['username'],
                        'password' => Hash::make('password'),
                        'status' => 'active',
                    ]
                );
                $user->roles()->sync([$patientRole->id]);
                $userId = $user->id;
            }

            $pat = Patient::updateOrCreate(
                ['patient_code' => $pData['code']],
                [
                    'user_id' => $userId,
                    'first_name' => $pData['first_name'],
                    'last_name' => $pData['last_name'],
                    'dob' => $pData['dob'],
                    'gender' => $pData['gender'],
                    'blood_type' => $pData['blood_type'],
                    'phone' => $pData['phone'],
                    'email' => $pData['email'],
                    'address' => $pData['address'],
                    'medical_history' => $pData['history'],
                ]
            );
            $patients[$pData['code']] = $pat;

            if (!empty($pData['contact'])) {
                EmergencyContact::updateOrCreate(
                    ['patient_id' => $pat->id, 'contact_name' => $pData['contact']['name']],
                    [
                        'relationship' => $pData['contact']['rel'],
                        'phone' => $pData['contact']['phone'],
                        'alt_phone' => $pData['contact']['alt'],
                    ]
                );
            }
        }

        // 2. Comprehensive Appointments
        $appointmentsData = [
            // Today's Appointments
            [
                'patient' => 'PAT-2026-0001',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->toDateString(),
                'time' => '09:30 AM',
                'status' => 'scheduled',
                'reason' => 'Routine cardiology follow-up and blood pressure assessment.',
                'notes' => 'Patient requested review of ACE inhibitor dosage.',
            ],
            [
                'patient' => 'PAT-2026-0002',
                'doctor' => $drJames,
                'dept' => $neurology,
                'date' => today()->toDateString(),
                'time' => '10:30 AM',
                'status' => 'scheduled',
                'reason' => 'Severe episodic throbbing headache with visual aura.',
                'notes' => 'Evaluate for prophylactic topiramate vs triptan regimen.',
            ],
            [
                'patient' => 'PAT-2026-0004',
                'doctor' => $drEmily,
                'dept' => $pediatrics,
                'date' => today()->toDateString(),
                'time' => '11:00 AM',
                'status' => 'scheduled',
                'reason' => 'Nocturnal cough and wheezing post viral URI.',
                'notes' => 'Peak flow measurement and inhaler technique review.',
            ],
            [
                'patient' => 'PAT-2026-0005',
                'doctor' => $drRobert,
                'dept' => $orthopedics,
                'date' => today()->toDateString(),
                'time' => '02:00 PM',
                'status' => 'scheduled',
                'reason' => 'Right knee joint pain during stair climbing.',
                'notes' => 'Check standing AP/lateral knee X-rays and range of motion.',
            ],
            [
                'patient' => 'PAT-2026-0006',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->toDateString(),
                'time' => '03:30 PM',
                'status' => 'scheduled',
                'reason' => 'Intermittent palpitations on exertion.',
                'notes' => 'Holter monitor requisition evaluation.',
            ],
            // Past Completed Appointments
            [
                'patient' => 'PAT-2026-0001',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->subDays(14)->toDateString(),
                'time' => '11:30 AM',
                'status' => 'completed',
                'reason' => 'Substernal chest tightness with moderate exertion.',
                'notes' => 'ECG performed. Advised Lipid Profile panel. Prescribed Atorvastatin.',
            ],
            [
                'patient' => 'PAT-2026-0002',
                'doctor' => $drJames,
                'dept' => $neurology,
                'date' => today()->subDays(10)->toDateString(),
                'time' => '02:30 PM',
                'status' => 'completed',
                'reason' => 'Follow-up for chronic tension-type headache.',
                'notes' => 'Neurological exam normal. Stress reduction techniques advised.',
            ],
            [
                'patient' => 'PAT-2026-0003',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->subDays(3)->toDateString(),
                'time' => '08:00 AM',
                'status' => 'completed',
                'reason' => 'Acute chest pain radiating to left shoulder - Emergency Admission.',
                'notes' => 'Admitted to ICU Room 101 Bed B1 for continuous telemetry.',
            ],
            [
                'patient' => 'PAT-2026-0007',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->subDays(7)->toDateString(),
                'time' => '09:00 AM',
                'status' => 'completed',
                'reason' => 'Atrial Fibrillation rate control assessment.',
                'notes' => 'Target heart rate achieved. Stable INR / Anticoagulation.',
            ],
            [
                'patient' => 'PAT-2026-0008',
                'doctor' => $drEmily,
                'dept' => $pediatrics,
                'date' => today()->subDays(5)->toDateString(),
                'time' => '10:00 AM',
                'status' => 'completed',
                'reason' => 'General health clearance for sports tournament.',
                'notes' => 'Normal cardiovascular and pulmonary exam. Cleared for athletics.',
            ],
            [
                'patient' => 'PAT-2026-0009',
                'doctor' => $drRobert,
                'dept' => $orthopedics,
                'date' => today()->subDays(4)->toDateString(),
                'time' => '03:00 PM',
                'status' => 'completed',
                'reason' => 'Chronic lower back pain radiating down left posterior thigh.',
                'notes' => 'SLR test positive at 45 degrees left. Ordered Lumbar Spine MRI.',
            ],
            // Upcoming Future Appointments
            [
                'patient' => 'PAT-2026-0001',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->addDays(7)->toDateString(),
                'time' => '10:00 AM',
                'status' => 'scheduled',
                'reason' => 'Post-treatment lipid profile check & medication review.',
                'notes' => null,
            ],
            [
                'patient' => 'PAT-2026-0003',
                'doctor' => $drSarah,
                'dept' => $cardiology,
                'date' => today()->addDays(5)->toDateString(),
                'time' => '11:00 AM',
                'status' => 'scheduled',
                'reason' => 'Post-discharge recovery check & cardiac rehab orientation.',
                'notes' => null,
            ],
            [
                'patient' => 'PAT-2026-0005',
                'doctor' => $drRobert,
                'dept' => $orthopedics,
                'date' => today()->addDays(3)->toDateString(),
                'time' => '02:30 PM',
                'status' => 'scheduled',
                'reason' => 'Intra-articular hyaluronic acid injection follow-up.',
                'notes' => null,
            ],
        ];

        $appointments = [];
        foreach ($appointmentsData as $idx => $aData) {
            $pat = $patients[$aData['patient']] ?? null;
            if ($pat && $aData['doctor']) {
                $apt = Appointment::updateOrCreate(
                    [
                        'patient_id' => $pat->id,
                        'doctor_id' => $aData['doctor']->id,
                        'appointment_date' => $aData['date'],
                        'time_slot' => $aData['time'],
                    ],
                    [
                        'department_id' => $aData['dept']->id ?? $cardiology->id,
                        'status' => $aData['status'],
                        'reason' => $aData['reason'],
                        'doctor_notes' => $aData['notes'],
                    ]
                );
                $appointments[$idx] = $apt;
            }
        }

        // 3. Clinical Medical Records (EMR / SOAP Notes)
        $emrData = [
            [
                'apt_idx' => 5, // PAT-2026-0001
                'patient' => 'PAT-2026-0001',
                'doctor' => $drSarah,
                'date' => today()->subDays(14)->setTime(11, 30),
                'diagnosis' => 'Essential Hypertension with Stable Angina Pectoris (CCS Class I)',
                'symptoms' => 'Patient reports substernal tightness on stair climbing lasting < 3 mins, relieved by rest. No radiation, no diaphoresis, no dyspnea at rest.',
                'notes' => 'SOAP Assessment: Cardiovascular exam reveals S1, S2 normal, no S3/S4, no murmurs. JVP normal. Lungs clear to auscultation bilaterally. Plan: Start Atorvastatin 20mg QHS, Amlodipine 5mg QAM. Requisitioned 12-lead ECG and Lipid Profile Panel.',
                'vitals' => [
                    'Blood Pressure' => '138/86 mmHg',
                    'Heart Rate' => '74 bpm',
                    'SpO2' => '98% on Room Air',
                    'Temperature' => '98.4 °F',
                    'Respiratory Rate' => '16 breaths/min',
                    'Body Weight' => '82.5 kg',
                ],
            ],
            [
                'apt_idx' => 6, // PAT-2026-0002
                'patient' => 'PAT-2026-0002',
                'doctor' => $drJames,
                'date' => today()->subDays(10)->setTime(14, 30),
                'diagnosis' => 'Migraine with Visual Aura (ICD-10 G43.109) & Tension Headache',
                'symptoms' => 'Unilateral throbbing frontotemporal pain preceded by scintillating scotoma. Accompanied by photophobia and mild nausea.',
                'notes' => 'SOAP Assessment: Cranial nerves II-XII intact. Fundoscopy shows no papilledema. Motor strength 5/5 all extremities. Sensation intact. Plan: Acute abortive Sumatriptan 50mg PRN. Prophylactic magnesium & riboflavin. Headache diary initiated.',
                'vitals' => [
                    'Blood Pressure' => '118/76 mmHg',
                    'Heart Rate' => '68 bpm',
                    'SpO2' => '99% on Room Air',
                    'Temperature' => '98.6 °F',
                    'Respiratory Rate' => '14 breaths/min',
                    'Body Weight' => '58.0 kg',
                ],
            ],
            [
                'apt_idx' => 7, // PAT-2026-0003
                'patient' => 'PAT-2026-0003',
                'doctor' => $drSarah,
                'date' => today()->subDays(3)->setTime(8, 30),
                'diagnosis' => 'Non-ST-Elevation Myocardial Infarction (NSTEMI) & T2DM',
                'symptoms' => 'Acute onset crushing retrosternal chest pain radiating to left jaw, onset 2 hours prior to presentation with diaphoresis and nausea.',
                'notes' => 'SOAP Assessment: High-sensitivity Troponin I elevated (1450 ng/L). ECG showed ST depressions in V4-V6. Direct admission to ICU Bed 101-B1. Started dual antiplatelet therapy (DAPT), IV heparin protocol, and high-intensity statin.',
                'vitals' => [
                    'Blood Pressure' => '152/94 mmHg',
                    'Heart Rate' => '92 bpm',
                    'SpO2' => '95% on 2L Nasal Cannula',
                    'Temperature' => '99.1 °F',
                    'Respiratory Rate' => '20 breaths/min',
                    'Body Weight' => '89.0 kg',
                ],
            ],
            [
                'apt_idx' => 8, // PAT-2026-0007
                'patient' => 'PAT-2026-0007',
                'doctor' => $drSarah,
                'date' => today()->subDays(7)->setTime(9, 30),
                'diagnosis' => 'Paroxysmal Atrial Fibrillation on Anticoagulation (CHA2DS2-VASc = 3)',
                'symptoms' => 'Occasional fluttering sensation in chest, no syncopal episodes, good exercise tolerance.',
                'notes' => 'SOAP Assessment: Heart sounds irregularly irregular. Controlled ventricular response rate. Advised continuation of Eliquis 5mg BID and Metoprolol Succinate 50mg daily.',
                'vitals' => [
                    'Blood Pressure' => '126/80 mmHg',
                    'Heart Rate' => '76 bpm (irregular)',
                    'SpO2' => '98% on Room Air',
                    'Temperature' => '98.2 °F',
                    'Respiratory Rate' => '16 breaths/min',
                    'Body Weight' => '77.0 kg',
                ],
            ],
            [
                'apt_idx' => 10, // PAT-2026-0009
                'patient' => 'PAT-2026-0009',
                'doctor' => $drRobert,
                'date' => today()->subDays(4)->setTime(15, 30),
                'diagnosis' => 'Lumbar Radiculopathy secondary to L4-L5 Disc Herniation',
                'symptoms' => 'Sharp burning pain down left lateral thigh and calf with numbness over L5 dermatome.',
                'notes' => 'SOAP Assessment: Straight Leg Raise positive at 45 degrees on left. EHL weakness 4/5. Patellar and Achilles reflexes 2+ symmetrical. Prescribed Pregabalin 75mg QHS and physical therapy protocol. Requisitioned MRI Spine.',
                'vitals' => [
                    'Blood Pressure' => '128/82 mmHg',
                    'Heart Rate' => '72 bpm',
                    'SpO2' => '99% on Room Air',
                    'Temperature' => '98.4 °F',
                    'Respiratory Rate' => '15 breaths/min',
                    'Body Weight' => '84.0 kg',
                ],
            ],
        ];

        foreach ($emrData as $e) {
            $pat = $patients[$e['patient']] ?? null;
            $linkedApt = $appointments[$e['apt_idx']] ?? null;

            if ($pat) {
                $mr = MedicalRecord::updateOrCreate(
                    ['patient_id' => $pat->id, 'visit_date' => $e['date']],
                    [
                        'doctor_id' => $e['doctor']->id,
                        'appointment_id' => $linkedApt?->id,
                        'diagnosis' => $e['diagnosis'],
                        'symptoms' => $e['symptoms'],
                        'notes' => $e['notes'],
                    ]
                );

                foreach ($e['vitals'] as $vName => $vVal) {
                    MedicalRecordDetail::updateOrCreate(
                        ['medical_record_id' => $mr->id, 'vital_sign_name' => $vName],
                        ['vital_sign_value' => $vVal]
                    );
                }
            }
        }

        // 4. Live Inpatient Admissions
        $icuBed1 = Bed::whereHas('room', function ($q) {
            $q->where('room_number', 'ICU-101');
        })->where('bed_number', 'B1')->first();

        $prvBed1 = Bed::whereHas('room', function ($q) {
            $q->where('room_number', 'PRV-201');
        })->where('bed_number', 'B1')->first();

        // Admit Robert Brown to ICU-101 Bed B1
        if ($icuBed1 && isset($patients['PAT-2026-0003'])) {
            $icuBed1->update(['status' => 'occupied']);
            Admission::updateOrCreate(
                ['patient_id' => $patients['PAT-2026-0003']->id, 'bed_id' => $icuBed1->id, 'status' => 'admitted'],
                [
                    'doctor_id' => $drSarah->id,
                    'admission_date' => today()->subDays(3)->setTime(9, 0),
                    'admission_reason' => 'Acute NSTEMI post-coronary care observation and continuous cardiac telemetry monitoring.',
                    'discharge_notes' => null,
                ]
            );
        }

        // Admit William Taylor to Private Room PRV-201 Bed B1
        if ($prvBed1 && isset($patients['PAT-2026-0005'])) {
            $prvBed1->update(['status' => 'occupied']);
            Admission::updateOrCreate(
                ['patient_id' => $patients['PAT-2026-0005']->id, 'bed_id' => $prvBed1->id, 'status' => 'admitted'],
                [
                    'doctor_id' => $drRobert->id,
                    'admission_date' => today()->subDays(1)->setTime(14, 30),
                    'admission_reason' => 'Pre-operative preparation and pain management for right knee arthroplasty.',
                    'discharge_notes' => null,
                ]
            );
        }

        // Set one bed to cleaning and one to maintenance for realistic visual bed board
        $cleaningBed = Bed::where('status', 'available')->first();
        if ($cleaningBed) {
            $cleaningBed->update(['status' => 'cleaning']);
        }
        $maintBed = Bed::where('status', 'available')->skip(2)->first();
        if ($maintBed) {
            $maintBed->update(['status' => 'maintenance']);
        }
    }
}
