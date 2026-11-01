<?php

namespace Database\Seeders;

use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use Illuminate\Database\Seeder;

class EssentialMedicationsSeeder extends Seeder
{
    public function run(): void
    {
        $pharmaCategory = StockCategory::firstOrCreate(
            ['name' => 'Pharmaceuticals'],
            ['type' => 'drug', 'branch_type' => 'opd', 'is_active' => true]
        );

        $opticalCategory = StockCategory::firstOrCreate(
            ['name' => 'Optical Consumables'],
            ['type' => 'optical', 'branch_type' => 'eye_clinic', 'is_active' => true]
        );

        $meds = [
            [
                'name' => 'Amoxicillin 500mg Capsules',
                'generic_name' => 'Amoxicillin',
                'sku' => 'MED-AMX-500',
                'unit_of_measure' => 'capsule',
                'unit_price' => 15.00,
                'cost_price' => 8.00,
                'is_prescription_only' => true,
            ],
            [
                'name' => 'Paracetamol 500mg Tablets',
                'generic_name' => 'Paracetamol',
                'sku' => 'MED-PCM-500',
                'unit_of_measure' => 'tablet',
                'unit_price' => 5.00,
                'cost_price' => 2.00,
                'is_prescription_only' => false,
            ],
            [
                'name' => 'Ibuprofen 400mg Tablets',
                'generic_name' => 'Ibuprofen',
                'sku' => 'MED-IBU-400',
                'unit_of_measure' => 'tablet',
                'unit_price' => 10.00,
                'cost_price' => 4.50,
                'is_prescription_only' => false,
            ],
            [
                'name' => 'Cetirizine 10mg Tablets',
                'generic_name' => 'Cetirizine',
                'sku' => 'MED-CTZ-010',
                'unit_of_measure' => 'tablet',
                'unit_price' => 12.00,
                'cost_price' => 5.00,
                'is_prescription_only' => false,
            ],
            [
                'name' => 'Omeprazole 20mg Capsules',
                'generic_name' => 'Omeprazole',
                'sku' => 'MED-OMP-020',
                'unit_of_measure' => 'capsule',
                'unit_price' => 25.00,
                'cost_price' => 12.00,
                'is_prescription_only' => true,
            ],
            [
                'name' => 'Coartem (Artemether/Lumefantrine 20/120mg)',
                'generic_name' => 'Artemether + Lumefantrine',
                'sku' => 'MED-AL-20120',
                'unit_of_measure' => 'pack',
                'unit_price' => 350.00,
                'cost_price' => 200.00,
                'is_prescription_only' => true,
            ],
            [
                'name' => 'Azithromycin 500mg Tablets',
                'generic_name' => 'Azithromycin',
                'sku' => 'MED-AZM-500',
                'unit_of_measure' => 'tablet',
                'unit_price' => 60.00,
                'cost_price' => 30.00,
                'is_prescription_only' => true,
            ],
            [
                'name' => 'Ciprofloxacin Eye Drops 0.3% (5ml)',
                'generic_name' => 'Ciprofloxacin',
                'sku' => 'OPT-CIP-003',
                'unit_of_measure' => 'bottle',
                'unit_price' => 250.00,
                'cost_price' => 130.00,
                'is_prescription_only' => true,
            ],
        ];

        foreach ($meds as $med) {
            StockItem::firstOrCreate(
                ['sku' => $med['sku']],
                array_merge($med, [
                    'category_id' => str_starts_with($med['sku'], 'OPT') ? $opticalCategory->id : $pharmaCategory->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
