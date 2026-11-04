<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Admissions\Models\StudentDocument;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Auth\Models\Login;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    protected function getAdminSession(): array
    {
        $user = Login::where('usercode', 'admin')->first() ?? Login::first();

        return [
            'user' => [
                'id' => $user->id ?? 1,
                'usercode' => $user->usercode ?? 'admin',
                'username' => $user->username ?? 'Administrator',
                'user_type' => 2,
            ],
        ];
    }

    public function test_docuploadsd_portal_renders_with_select_all_and_passport_size_photo(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/docuploadsd');

        $response->assertStatus(200);
        $response->assertSee('Passport Size Photo');
        $response->assertSee('selectAllQueueCheckbox');
        $response->assertSee('Upload All Selected');
        $response->assertSee('Preview Selected Files (Line by Line)');
    }

    public function test_passport_size_photo_upload_succeeds_and_creates_database_record(): void
    {
        Storage::fake('public');
        $student = StudentRegistration::first();
        $this->assertNotNull($student, 'Student record must exist');

        $fakePhoto = UploadedFile::fake()->image('my_passport_size_photo.jpg', 300, 300);

        $response = $this->withSession($this->getAdminSession())
            ->postJson('/admissions/docupload/upload', [
                'application_no' => $student->application_no,
                'register_no' => $student->register_no,
                'doctype' => 'photo',
                'file' => $fakePhoto,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'document' => [
                'application_no' => (int) $student->application_no,
                'doctype' => 'photo',
            ],
        ]);

        $docId = $response->json('document.doc_id');
        $this->assertNotEmpty($docId);

        $record = StudentDocument::find($docId);
        $this->assertNotNull($record);
        $this->assertEquals($student->application_no, $record->application_no);

        // Clean up
        $record->delete();
    }

    public function test_docuploadsd_upload_alias_route_works(): void
    {
        Storage::fake('public');
        $student = StudentRegistration::first();

        $fakePdf = UploadedFile::fake()->create('degree_cert.pdf', 250, 'application/pdf');

        $response = $this->withSession($this->getAdminSession())
            ->postJson('/admissions/docuploadsd/upload', [
                'application_no' => $student->application_no,
                'register_no' => $student->register_no,
                'doctype' => 'ug',
                'file' => $fakePdf,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $docId = $response->json('document.doc_id');
        if ($docId) {
            StudentDocument::where('doc_id', $docId)->delete();
        }
    }
}

