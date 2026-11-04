<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $maxId = (int) (DB::table('admissions.student_documents')->max('doc_id') ?? 100);
        $nextVal = max($maxId + 1, 120);

        DB::statement("CREATE SEQUENCE IF NOT EXISTS admissions.student_documents_doc_id_seq START WITH {$nextVal}");
        DB::statement("SELECT setval('admissions.student_documents_doc_id_seq', {$nextVal}, false)");
        DB::statement("ALTER TABLE admissions.student_documents ALTER COLUMN doc_id SET DEFAULT nextval('admissions.student_documents_doc_id_seq')");

        DB::statement('CREATE OR REPLACE VIEW admissions.document AS SELECT * FROM admissions.student_documents');
        DB::statement('CREATE OR REPLACE VIEW public.document AS SELECT * FROM admissions.student_documents');
        DB::statement('CREATE OR REPLACE VIEW public.documents AS SELECT * FROM admissions.student_documents');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS public.documents');
        DB::statement('DROP VIEW IF EXISTS public.document');
        DB::statement('DROP VIEW IF EXISTS admissions.document');
    }
};
