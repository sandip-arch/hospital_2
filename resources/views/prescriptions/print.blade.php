<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Slip - Rx #{{ $prescription->id }}</title>
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
            <span class="text-xs text-slate-500 font-bold">Official e-Prescription (Rx) Slip</span>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800">
                Print Rx Slip
            </button>
        </div>

        <!-- Clinic Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Apex Horizon Medical Center</h1>
                <p class="text-xs text-slate-500">500 Health Sciences Blvd, Boston MA 02115 &bull; Phone: +1 (800) 555-APEX</p>
                <p class="text-xs text-emerald-700 font-bold uppercase tracking-wider mt-1">Department of Pharmacy & Outpatient Services</p>
            </div>
            <div class="text-right">
                <span class="text-3xl font-black font-serif text-slate-900">℞</span>
                <span class="text-xs font-mono block font-bold text-slate-600">#Rx-{{ $prescription->id }}</span>
            </div>
        </div>

        <!-- Demographics Grid -->
        <div class="grid grid-cols-2 gap-4 py-6 border-b border-slate-200 text-xs">
            <div>
                <p><strong class="text-slate-900">Patient:</strong> {{ $prescription->patient->full_name }}</p>
                <p><strong class="text-slate-900">UPI:</strong> {{ $prescription->patient->patient_code }}</p>
                <p><strong class="text-slate-900">Age / Gender:</strong> {{ $prescription->patient->age }} yrs / {{ $prescription->patient->gender }}</p>
            </div>
            <div>
                <p><strong class="text-slate-900">Doctor:</strong> {{ $prescription->doctor->full_name }}</p>
                <p><strong class="text-slate-900">Specialization:</strong> {{ $prescription->doctor->specialization }}</p>
                <p><strong class="text-slate-900">Date Prescribed:</strong> {{ $prescription->prescribed_date->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Prescription Items -->
        <div class="py-6 border-b border-slate-200">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4">Prescribed Medications (℞)</h3>
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Medication</th>
                        <th class="py-2.5 px-3">Dosage</th>
                        <th class="py-2.5 px-3">Frequency & Duration</th>
                        <th class="py-2.5 px-3">Instructions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($prescription->items as $idx => $item)
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-500">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3">
                            <strong class="text-slate-900">{{ $item->medicine->name }}</strong>
                            <span class="text-[10px] text-slate-400 block">{{ $item->medicine->generic_name }}</span>
                        </td>
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-800">{{ $item->dosage }}</td>
                        <td class="py-2.5 px-3">{{ $item->frequency }} &bull; {{ $item->duration_days }} Days</td>
                        <td class="py-2.5 px-3 text-slate-600">{{ $item->instructions ?? 'As directed' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($prescription->notes)
        <div class="py-4 border-b border-slate-200 text-xs">
            <strong class="text-slate-900 block mb-1">Dietary / Health Advice:</strong>
            <p class="text-slate-700">{{ $prescription->notes }}</p>
        </div>
        @endif

        <!-- Signature -->
        <div class="pt-20 flex items-end justify-between text-xs">
            <div>
                <p class="text-slate-400">Valid only with registered pharmacy dispenser seal</p>
                <p class="text-[10px] text-slate-400">Medical Council License No: {{ $prescription->doctor->license_number }}</p>
            </div>
            <div class="text-center w-56 border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-900">{{ $prescription->doctor->full_name }}</p>
                <p class="text-slate-500 text-[11px]">Doctor Signature & Reg Stamp</p>
            </div>
        </div>

    </div>

</body>
</html>
