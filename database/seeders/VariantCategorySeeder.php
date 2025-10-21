<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Not strictly needed, but kept for general utility

class VariantCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define the array of standard variant names, including those specific to your products.
        $variants = [
            // Volumes (mL / L) - Comprehensive list based on previous and new data
            '100ml',
            '200ml',
            '225ml',
            '250ml',
            '300ml',
            '500ml',
            '750ml',
            '1000ml', // Equivalent to 1L
            '1500ml', // Equivalent to 1.5L
            '2L',
            '5L',

            // Common Weights (If applicable, kept for completeness)
            '50g', '100g', '200g', '250g', '500g', '1kg',
            
            // Other general variants
            'Small', 'Medium', 'Large', 'X-Large',
            '1 Piece', '2 Pack', '6 Pack',
        ];
        
        // Remove duplicates just in case
        $variants = array_unique($variants);

        $now = now();

        // Use updateOrCreate to prevent duplicates and make the seeder idempotent.
        // We will loop through each variant individually.
        foreach ($variants as $name) {
            DB::table('categories')->updateOrInsert(
                // 1. Attributes to check if a record exists (the 'search' criteria)
                ['name' => $name],
                
                // 2. Attributes to set or update
                [
                    // Only setting the name if it's a new record (redundant but explicit)
                    'name' => $name, 
                    
                    // We only set created_at on creation, but we set updated_at on both.
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}