<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $unitNames = [
            'Marcomm',
            'Admin & SO',
            'Kemahasiswaan',
            'BSI Career Center',
            'BSI Entrepreneur Center',
            'Technical Support',
        ];

        foreach (['Cikampek', 'Karawang', 'Cikarang', 'Cibitung', 'Kaliabang', 'Bekasi'] as $campusName) {
            $campus = Campus::firstOrCreate(['name' => $campusName]);

            foreach ($unitNames as $unitName) {
                $campus->units()->firstOrCreate(['name' => $unitName]);
            }
        }
    }
}
