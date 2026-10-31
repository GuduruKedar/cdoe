<?php

namespace Database\Seeders;

use App\Models\GeneralDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class GeneralDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $samplePdfs = [
            [
                'program' => 'MBA',
                'sub_category' => 'Regulations',
                'title' => 'R22 Online MBA Academic Regulations & Degree Requirements',
                'regulation_code' => 'R22',
                'description' => 'Comprehensive academic regulations, credit distribution, grading scheme, promotion rules, and award of Master of Business Administration degree.',
                'file_name' => 'MBA_R22_Academic_Regulations.pdf',
            ],
            [
                'program' => 'MBA',
                'sub_category' => 'Syllabus',
                'title' => 'Online MBA Detailed Syllabus & Specialization Modules (R22)',
                'regulation_code' => 'R22',
                'description' => 'Course curriculum across Semesters I to IV covering Finance, Marketing, HR, Operations, and Business Analytics electives.',
                'file_name' => 'MBA_R22_Curriculum_Syllabus.pdf',
            ],
            [
                'program' => 'BBA',
                'sub_category' => 'Regulations',
                'title' => 'R21 Online BBA Academic Regulations & Assessment Policy',
                'regulation_code' => 'R21',
                'description' => 'Undergraduate academic guidelines, credit requirements, continuous internal assessments, and end-semester evaluation schemes for BBA.',
                'file_name' => 'BBA_R21_Academic_Regulations.pdf',
            ],
            [
                'program' => 'BBA',
                'sub_category' => 'Syllabus',
                'title' => 'Online BBA Semester-wise Course Structure & Electives (R21)',
                'regulation_code' => 'R21',
                'description' => 'Three-year undergraduate curriculum covering business administration fundamentals, management practices, and domain electives.',
                'file_name' => 'BBA_R21_Course_Structure.pdf',
            ],
            [
                'program' => 'COMMON',
                'sub_category' => 'Exam Guidelines',
                'title' => 'Online Proctored Semester Examination & Evaluation Guidelines',
                'regulation_code' => '2026',
                'description' => 'Standard operating procedures for remote online proctored examinations, system requirements, rules against malpractice, and grievance redressal.',
                'file_name' => 'CDOE_Online_Exam_Guidelines_2026.pdf',
            ],
        ];

        $minimalPdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 53 >>\nstream\nBT /F1 18 Tf 50 700 Td (Vignan CDOE - Academic Document) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000216 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n320\n%%EOF";

        foreach ($samplePdfs as $item) {
            $path = 'general_documents/'.$item['file_name'];
            Storage::disk('public')->put($path, $minimalPdf);

            GeneralDocument::firstOrCreate(
                ['title' => $item['title']],
                [
                    'program' => $item['program'],
                    'sub_category' => $item['sub_category'],
                    'regulation_code' => $item['regulation_code'],
                    'description' => $item['description'],
                    'file_path' => $path,
                    'file_name' => $item['file_name'],
                    'file_type' => 'pdf',
                    'file_size' => strlen($minimalPdf),
                    'uploaded_by' => '02598',
                    'downloads_count' => rand(15, 85),
                    'status' => 1,
                ]
            );
        }
    }
}
