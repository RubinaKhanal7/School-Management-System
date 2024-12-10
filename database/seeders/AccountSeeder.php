<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run()
    {
        DB::table('accounts')->insert([
            'id' => 1,
            'name' => 'Opening Balance',
            'code' => 'OP',
            'expense_header_no' => null,
            'status' => 0, 
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
