<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('brands')->updateOrInsert(
            ['name' => 'Gourmet'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $brand = DB::table('brands')->where('name', 'Gourmet')->first();


        DB::table('units')->updateOrInsert(
            ['name' => 'ml'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $defaultUnit = DB::table('units')->where('name', 'ml')->first();


        $menu = [
            '225ml' => [
                'Zeera Cola',
                'Cola',
                'Lemon',
                'Malta',
                'Twister',
                'Red Anar',
                'Soda IceCream',
            ],

            '250ml' => [
                'Can-Cola',
                'Can-Diet Cola',
                'Can-Lemon Up',
                'Tin-Sparkling Water',
            ],

            '300ml' => [
                'Cola ',
                'Lemon Up ',
                'Malta ',
                'Twister ',
                'Red Anar ',
                'Spark Stimulant',
            ],

            '500ml' => [
                'Water',
                'Sparkling Water',
                'COLA',
                'DIET COLA',
                'ZEERA COLA',
                'DIET LEMON UP',
                'LEMON UP',
                'MALTA',
                'TWISTER',
                'RED ANAR',
                'JUICE GUAVA',
                'JUICE MAUJJ MANGO',
                'Spark STIMULANT',
            ],

            '750ml' => [
                'Cola',
                'Lemon',
                'Malta',
            ],

            '1000ml' => [
                'Cola ',
                'Lemon Up ',
                'Malta ',
                'Twister ',
                'Red Anar ',
                'Ice Cream Soda ',
                'Juice Mauj Mango ',
            ],
            '1500ml' => [
                'Cola ',
                'Lemon Up ',
                'Malta ',
                'Twister ',
                'Red Anar ',
                'Ice Cream Soda ',
                'Sparkling Water',
            ],



            '2250ml' => [
                'Cola',
                'Lemon Up',
                'Malta',
                'Twister',
                'Red Anar',
            ],
        ];


        foreach ($menu as $categoryName => $products) {
            DB::table('categories')->updateOrInsert(
                ['name' => $categoryName],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }


        foreach ($menu as $categoryName => $products) {
            $category = DB::table('categories')->where('name', $categoryName)->first();

            foreach ($products as $productName) {
                DB::table('products')->updateOrInsert(
                    ['name' => $productName, 'category_id' => $category->id],
                    [
                        'brand_id' => $brand->id,
                        'unit_id' => $defaultUnit->id,
                        'sku' => strtoupper(Str::random(8)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Gourmet products (without prices) seeded successfully!');
    }
}
