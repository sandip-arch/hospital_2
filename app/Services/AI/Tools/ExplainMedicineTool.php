<?php

namespace App\Services\AI\Tools;

use App\Models\Medicine;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class ExplainMedicineTool implements ToolInterface
{
    public function name(): string
    {
        return 'explain_medicine';
    }

    public function description(): string
    {
        return 'Retrieve verified pharmacological knowledge, drug class, clinical indications, common dosage schedules, side effects, and hospital formulary availability for a medicine. Includes a strict medical disclaimer.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'medicine_name' => [
                    'type' => 'STRING',
                    'description' => 'Brand name or generic formulation of the pharmaceutical, e.g., "Amoxicillin", "Paracetamol", "Metformin", "Atorvastatin".',
                ],
            ],
            'required' => ['medicine_name'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $term = trim($arguments['medicine_name'] ?? '');

        if (empty($term)) {
            return [
                'status' => 'error',
                'message' => 'Please provide a medicine name to explain.',
            ];
        }

        // Query database formulary
        $matches = Medicine::where('name', 'like', "%{$term}%")
            ->orWhere('generic_name', 'like', "%{$term}%")
            ->take(5)
            ->get();

        // Built-in pharmacological knowledge dictionary for hospital formulary
        $pharmacologyGuide = [
            'amoxicillin' => [
                'class' => 'Beta-lactam Aminopenicillin Antibiotic',
                'indications' => 'Bacterial infections including ENT, respiratory tract, urinary tract, and dental infections.',
                'mechanism' => 'Inhibits bacterial cell wall synthesis during active multiplication.',
                'common_dosage' => '500mg every 8 hours or 875mg every 12 hours orally.',
                'precautions' => 'Contraindicated in penicillin allergy. Complete full course to prevent antimicrobial resistance.',
                'side_effects' => 'Nausea, diarrhea, cutaneous rash.',
            ],
            'paracetamol' => [
                'class' => 'Analgesic & Antipyretic (Acetaminophen)',
                'indications' => 'Mild-to-moderate pain, fever reduction, musculoskeletal aches, post-operative analgesia.',
                'mechanism' => 'Inhibits prostaglandin synthesis primarily in the central nervous system.',
                'common_dosage' => '500mg - 1000mg every 4 to 6 hours as needed (maximum 4000mg in 24 hours).',
                'precautions' => 'Strict maximum 4g daily to avoid acute hepatotoxicity. Caution in chronic liver disease or alcohol use.',
                'side_effects' => 'Rare at therapeutic doses; allergic skin reactions.',
            ],
            'metformin' => [
                'class' => 'Biguanide Oral Antihyperglycemic Agent',
                'indications' => 'First-line pharmacotherapy for Type 2 Diabetes Mellitus.',
                'mechanism' => 'Reduces hepatic glucose output and improves peripheral insulin sensitivity.',
                'common_dosage' => '500mg - 1000mg twice daily with meals.',
                'precautions' => 'Take with meals to minimize gastrointestinal discomfort. Monitor eGFR regularly; withhold before iodinated radiocontrast.',
                'side_effects' => 'Abdominal bloating, nausea, metallic taste; rare risk of lactic acidosis.',
            ],
            'atorvastatin' => [
                'class' => 'HMG-CoA Reductase Inhibitor (Statin)',
                'indications' => 'Hypercholesterolemia, primary and secondary cardiovascular risk reduction.',
                'mechanism' => 'Competitively inhibits HMG-CoA reductase, decreasing intracellular hepatic cholesterol and upregulating LDL receptors.',
                'common_dosage' => '10mg - 80mg orally once daily, typically administered at bedtime.',
                'precautions' => 'Report unexplained muscle pain, tenderness, or weakness promptly (rhabdomyolysis warning). Periodic liver enzymes check.',
                'side_effects' => 'Myalgia, headache, mild gastrointestinal upset.',
            ],
            'omeprazole' => [
                'class' => 'Proton Pump Inhibitor (PPI)',
                'indications' => 'Gastroesophageal reflux disease (GERD), peptic ulcer disease, NSAID-induced gastric prophylaxis.',
                'mechanism' => 'Irreversibly inhibits the H+/K+ ATPase pump in gastric parietal cells.',
                'common_dosage' => '20mg - 40mg once daily taken 30-60 minutes before breakfast.',
                'precautions' => 'Prolonged use may reduce magnesium and vitamin B12 absorption and increase risk of Clostridioides difficile.',
                'side_effects' => 'Headache, abdominal pain, constipation, flatulence.',
            ],
            'azithromycin' => [
                'class' => 'Macrolide Antibiotic',
                'indications' => 'Community-acquired pneumonia, acute bacterial sinusitis, tonsillitis, urethritis.',
                'mechanism' => 'Reversibly binds to 50S ribosomal subunit of susceptible microorganisms, inhibiting protein synthesis.',
                'common_dosage' => '500mg on day 1, followed by 250mg once daily on days 2-5.',
                'precautions' => 'Caution in patients with cardiac conduction disorders or prolonged QT intervals.',
                'side_effects' => 'Gastrointestinal cramping, diarrhea, temporary altered taste.',
            ],
            'cetirizine' => [
                'class' => 'Second-Generation H1 Antihistamine',
                'indications' => 'Allergic rhinitis, seasonal allergies, chronic idiopathic urticaria.',
                'mechanism' => 'Selective peripheral histamine H1-receptor antagonist.',
                'common_dosage' => '10mg once daily orally (evening preferred).',
                'precautions' => 'May cause mild drowsiness in sensitive individuals; avoid alcohol.',
                'side_effects' => 'Somnolence, dry mouth, mild fatigue.',
            ],
            'ibuprofen' => [
                'class' => 'Non-Steroidal Anti-Inflammatory Drug (NSAID)',
                'indications' => 'Inflammatory pain, arthritis, dysmenorrhea, musculoskeletal injuries.',
                'mechanism' => 'Non-selective inhibition of Cyclooxygenase (COX-1 and COX-2) enzymes.',
                'common_dosage' => '200mg - 400mg every 6 to 8 hours with food (maximum 1200mg OTC / 2400mg under prescription).',
                'precautions' => 'Always take with food or milk. Avoid in active peptic ulcer, chronic kidney disease, or third-trimester pregnancy.',
                'side_effects' => 'Dyspepsia, epigastric discomfort, fluid retention.',
            ],
        ];

        // Match against dictionary
        $lowerTerm = strtolower($term);
        $guideData = null;
        foreach ($pharmacologyGuide as $key => $info) {
            if (str_contains($lowerTerm, $key) || str_contains($key, $lowerTerm)) {
                $guideData = $info;
                $guideData['matched_key'] = ucfirst($key);
                break;
            }
        }

        $formularyMatches = $matches->map(function ($med) {
            return [
                'id' => $med->id,
                'name' => $med->name,
                'generic_name' => $med->generic_name,
                'category' => $med->category,
                'unit_price' => '$' . number_format($med->unit_price, 2),
                'stock_available' => $med->stock_quantity . ' units',
                'is_low_stock' => $med->isLowStock(),
            ];
        })->all();

        return [
            'status' => 'success',
            'query' => $term,
            'hospital_formulary_matches' => $formularyMatches,
            'pharmacology_summary' => $guideData ?? [
                'class' => 'Hospital Formulary Pharmaceutical',
                'indications' => 'Indicated for therapeutic clinical treatment as ordered by physician.',
                'precautions' => 'Administer only in accordance with explicit prescription labeling.',
                'common_dosage' => 'Refer to official physician prescription order.',
            ],
            'medical_disclaimer' => 'IMPORTANT MEDICAL DISCLAIMER: This pharmacological information is provided strictly for educational and informational purposes. It does not replace professional medical evaluation, diagnosis, or personalized prescription instructions. Always consult your attending physician or licensed pharmacist before starting, stopping, or adjusting any medication.',
        ];
    }
}
