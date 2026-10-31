<?php

namespace Database\Seeders;

use App\Models\PprDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PprDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $sampleItems = [
            [
                'prog_id' => 1,
                'prog_name' => 'MBA',
                'regname' => 'R22 Online MBA Academic Regulations & Degree Guidelines',
                'regcode' => 'R22',
                'description' => 'Comprehensive academic regulations, credit requirements, grading framework, and award of degree for Online Master of Business Administration.',
                'filename' => 'MBA_R22_Academic_Regulations.pdf',
            ],
            [
                'prog_id' => 2,
                'prog_name' => 'MCA',
                'regname' => 'R22 Online MCA Academic Regulations & Lab Framework',
                'regcode' => 'R22',
                'description' => 'Regulations for Master of Computer Applications covering advanced computing curriculum, practical labs, and project evaluations.',
                'filename' => 'MCA_R22_Academic_Regulations.pdf',
            ],
            [
                'prog_id' => 3,
                'prog_name' => 'BBA',
                'regname' => 'R21 Online BBA Academic Regulations & Assessment Scheme',
                'regcode' => 'R21',
                'description' => 'Undergraduate guidelines, semester credits, continuous internal evaluation, and degree regulations for Bachelor of Business Administration.',
                'filename' => 'BBA_R21_Academic_Regulations.pdf',
            ],
            [
                'prog_id' => 4,
                'prog_name' => 'BCA',
                'regname' => 'R21 Online BCA Academic Regulations & Practical Policy',
                'regcode' => 'R21',
                'description' => 'Academic curriculum and examination regulations for Bachelor of Computer Applications including programming modules and internships.',
                'filename' => 'BCA_R21_Academic_Regulations.pdf',
            ],
        ];

        $minimalPdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 53 >>\nstream\nBT /F1 18 Tf 50 700 Td (Vignan CDOE - PPR Regulation Document) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000216 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n320\n%%EOF";

        foreach ($sampleItems as $item) {
            $path = 'general_documents/'.$item['filename'];
            Storage::disk('public')->put($path, $minimalPdf);

            PprDetail::firstOrCreate(
                ['regname' => $item['regname']],
                [
                    'prog_id' => $item['prog_id'],
                    'prog_name' => $item['prog_name'],
                    'regcode' => $item['regcode'],
                    'description' => $item['description'],
                    'filepath' => $path,
                    'filename' => $item['filename'],
                ]
            );
        }
    }
}
