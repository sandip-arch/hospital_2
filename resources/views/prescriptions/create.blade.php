@extends('layouts.app')

@section('title', 'Issue Electronic Prescription')
@section('header_title', 'e-Prescription Builder')
@section('header_subtitle', 'Select medicines from pharmacy inventory, define dosage regimens, duration, and patient instructions')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="prescriptionBuilder()">

    <div class="flex items-center justify-between">
        <a href="{{ route('prescriptions.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Prescriptions
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        
        <form action="{{ route('prescriptions.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($selectedMedicalRecordId)
            <input type="hidden" name="medical_record_id" value="{{ $selectedMedicalRecordId }}">
            @endif

            <!-- Patient & Doctor Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patient *</label>
                    <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatientId) == $p->id) ? 'selected' : '' }}>
                            {{ $p->full_name }} ({{ $p->patient_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Prescribing Doctor *</label>
                    <select name="doctor_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ (old('doctor_id', $selectedDoctorId) == $doc->id) ? 'selected' : '' }}>
                            {{ $doc->full_name }} ({{ $doc->specialization }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Prescribed Date *</label>
                    <input type="date" name="prescribed_date" value="{{ old('prescribed_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>

            <!-- Dynamic Prescription Items Table -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-pills text-emerald-600"></i> Prescribed Medications
                    </h4>
                    <button type="button" @click="addItem()" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-plus"></i> Add Medicine Row
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 relative space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700" x-text="'Medication #' + (index + 1)"></span>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Remove
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-5">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Medicine *</label>
                                    <select :name="'items[' + index + '][medicine_id]'" x-model="item.medicine_id" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                        <option value="">-- Choose Medicine from Inventory --</option>
                                        @foreach($medicines as $med)
                                        <option value="{{ $med->id }}">{{ $med->name }} ({{ $med->generic_name }}) - In Stock: {{ $med->stock_quantity }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Dosage *</label>
                                    <input type="text" :name="'items[' + index + '][dosage]'" x-model="item.dosage" placeholder="e.g. 500mg" required
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                </div>

                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Frequency *</label>
                                    <input type="text" :name="'items[' + index + '][frequency]'" x-model="item.frequency" placeholder="e.g. Twice daily after meals" required
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Duration (Days) *</label>
                                    <input type="number" :name="'items[' + index + '][duration_days]'" x-model="item.duration_days" min="1" required
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                </div>

                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Quantity *</label>
                                    <input type="number" :name="'items[' + index + '][quantity_prescribed]'" x-model="item.quantity_prescribed" min="1" required
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                </div>

                                <div class="sm:col-span-6">
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Special Instructions</label>
                                    <input type="text" :name="'items[' + index + '][instructions]'" x-model="item.instructions" placeholder="e.g. Take with plenty of water. Avoid alcohol."
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">General Dietary or Lifestyle Recommendations</label>
                <textarea name="notes" rows="2" placeholder="e.g. Sodium restriction, increase fluid intake, follow up in 2 weeks"
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('prescriptions.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Finalize & Issue Prescription
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    function prescriptionBuilder() {
        return {
            items: [
                { medicine_id: '', dosage: '500mg', frequency: 'Twice daily (Morning & Night)', duration_days: 7, quantity_prescribed: 14, instructions: 'Take after meals' }
            ],
            addItem() {
                this.items.push({
                    medicine_id: '',
                    dosage: '',
                    frequency: 'Once daily',
                    duration_days: 5,
                    quantity_prescribed: 5,
                    instructions: ''
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            }
        }
    }
</script>
@endsection
