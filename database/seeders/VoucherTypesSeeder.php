<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

          DB::table('voucher_types')->insert([
            'name' => 'Journal Voucher', 
            'code' => 'JV',            
            'status' => 1,            
            'created_by' => 1,        
            'updated_by' => 1,         
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
