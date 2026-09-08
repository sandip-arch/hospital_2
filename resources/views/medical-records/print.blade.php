<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clinical Consultation Summary - {{ $record->patient->patient_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 md:p-12 text-slate-800 text-sm">

    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 shadow-md rounded-2xl print:shadow-none print:p-0">
        
        <div class="flex items-center justify-between no-print mb-8 pb-4 border-b border-slate-200">
            <span class="text-xs text-slate-500 font-bold">Printable Medical Consultation Summary</span>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800">
                Print Document
            </button>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Apex Horizon Medical Center</h1>
                <p class="text-xs text-slate-500">500 Health Sciences Blvd, Boston MA 02115 &bull; Phone: +1 (800) 555-APEX</p>
                <p class="text-xs text-cyan-700 font-bold uppercase tracking-wider mt-1">Clinical Consultation Summary</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block uppercase font-bold">Patient UPI</span>
                <span class="text-lg font-mono font-black text-slate-900">{{ $record->patient->patient_code }}</span>
            </div>
        </div>

        <!-- Demographics Grid -->
        <div class="grid grid-cols-2 gap-4 py-6 border-b border-slate-200 text-xs">
            <div>
                <p><strong class="text-slate-900">Patient Name:</strong> {{ $record->patient->full_name }}</p>
                <p><strong class="text-slate-900">Age / Gender:</strong> {{ $record->patient->age }} yrs / {{ $record->patient->gender }}</p>
                <p><strong class="text-slate-900">Blood Type:</strong> {{ $record->patient->blood_type ?? 'N/A' }}</p>
            </div>
            <div>
                <p><strong class="text-slate-900">Consulting Physician:</strong> {{ $record->doctor->full_name }}</p>
                <p><strong class="text-slate-900">Specialization:</strong> {{ $record->doctor->specialization }}</p>
                <p><strong class="text-slate-900">Encounter Date:</strong> {{ $record->visit_date->format('M d, Y - h:i A') }}</p>
            </div>
        </div>

        <!-- Diagnosis & SOAP -->
        <div class="py-6 border-b border-slate-200 space-y-4 text-xs">
            <div>
                <strong class="text-slate-900 block uppercase tracking-wider text-[11px] mb-1">Clinical Diagnosis:</strong>
                <p class="text-base font-bold text-slate-900">{{ $record->diagnosis }}</p>
            </div>

            @if($record->symptoms)
            <div>
                <strong class="text-slate-900 block uppercase tracking-wider text-[11px] mb-1">Presenting Symptoms:</strong>
                <p class="text-slate-700">{{ $record->symptoms }}</p>
            </div>
            @endif

            @if($record->notes)
            <div>
                <strong class="text-slate-900 block uppercase tracking-wider text-[11px] mb-1">Clinical Notes & Assessment:</strong>
                <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $record->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Vital Signs -->
        @if($record->details->isNotEmpty())
        <div class="py-6 border-b border-slate-200">
            <strong class="text-slate-900 block uppercase tracking-wider text-[11px] mb-3">Vital Signs at Visit:</strong>
            <div class="grid grid-cols-3 gap-3 text-xs">
                @foreach($record->details as $vital)
                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                    <span class="text-[10px] text-slate-500 block uppercase">{{ $vital->vital_sign_name }}</span>
                    <span class="font-bold text-slate-900">{{ $vital->vital_sign_value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer Sign Off -->
        <div class="pt-16 flex items-end justify-between text-xs">
            <div>
                <p class="text-slate-400">Generated on {{ now()->format('M d, Y h:i A') }}</p>
                <p class="text-slate-400 text-[10px]">Confidential Health Information &bull; Protected under Healthcare Regulations</p>
            </div>
            <div class="text-center w-56 border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-900">{{ $record->doctor->full_name }}</p>
                <p class="text-slate-500 text-[11px]">Authorized Signature / Seal</p>
            </div>
        </div>

    </div>

</body>
</html>
