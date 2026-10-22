<?php

namespace Tests\Feature;

use App\Common\Services\ActivityLogger;
use App\Models\ActivityLog;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    public function test_activity_logs_requires_login(): void
    {
        $response = $this->get('/hrms/activitylogs');
        $response->assertRedirect('/login');
    }

    public function test_activity_logs_page_loads_with_session(): void
    {
        $response = $this->withSession([
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ])->get('/hrms/activitylogs');

        $response->assertStatus(200);
        $response->assertSee('Audit Log Feed');
        $response->assertSee('Activity Logs');
    }

    public function test_activity_logger_service_records_entry(): void
    {
        $entry = ActivityLogger::log(
            'AUTH',
            'LOGIN',
            'Test suite verified login activity.',
            [
                'user_code' => 'TEST_USER',
                'status' => 'success',
                'payload' => ['test_key' => 'test_value'],
            ]
        );

        $this->assertNotNull($entry);
        $this->assertDatabaseHas('hrms.activity_logs', [
            'id' => $entry->id,
            'user_code' => 'TEST_USER',
            'action' => 'LOGIN',
        ]);
    }

    public function test_activity_logs_ajax_search(): void
    {
        $response = $this->withSession([
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ])->getJson('/hrms/activitylogs?q=TEST_USER');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertArrayHasKey('html', $response->json());
        $this->assertArrayHasKey('stats', $response->json());
    }

    public function test_activity_logs_show_endpoint(): void
    {
        $log = ActivityLog::first();
        if ($log) {
            $response = $this->withSession([
                'user' => [
                    'usercode' => '02266',
                    'role_id' => 2,
                    'user_type' => 2,
                ],
            ])->getJson("/hrms/activitylogs/{$log->id}");

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
            ]);
            $this->assertEquals($log->id, $response->json('log.id'));
        }
    }
}
