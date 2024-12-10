<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FiscalYear;

class FiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fiscalYears = [
            [
                'name' => '2081',
                'from_date' => '2024-07-16',
                'to_date' => '2025-07-15',
                'from_date_nepali' => '2081-04-01',
                'to_date_nepali' => '2082-03-31',
            ],
            [
                'name' => '2082',
                'from_date' => '2025-07-16',
                'to_date' => '2026-07-15',
                'from_date_nepali' => '2082-04-01',
                'to_date_nepali' => '2083-03-30',
            ],
            [
                'name' => '2083',
                'from_date' => '2026-07-16',
                'to_date' => '2027-07-15',
                'from_date_nepali' => '2083-04-01',
                'to_date_nepali' => '2084-03-30',
            ],
        ];

        foreach ($fiscalYears as $index => $year) {
            FiscalYear::create([
                'name' => $year['name'],
                'from_date' => $year['from_date'],
                'to_date' => $year['to_date'],
                'from_date_nepali' => $year['from_date_nepali'],
                'to_date_nepali' => $year['to_date_nepali'],
                'status' => $index === 0 ? 1 : 0, 
                'created_by' => 1,
            ]);
        }
    }
}
