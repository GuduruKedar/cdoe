<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentStatusUpdatePaginationTest extends TestCase
{
    protected function getAdminSession(): array
    {
        return [
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ];
    }

    public function test_stdstatusupdate_page_loads_with_default_10_records(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/stdstatusupdate');

        $response->assertStatus(200);
        $response->assertSee('Student Status Records');
        $response->assertSee('name="per_page"', false);
        $response->assertSee('value="10"', false);

        $viewData = $response->original->getData();
        $this->assertEquals(10, $viewData['perPage']);
        $this->assertCount(10, $viewData['students']);
    }

    public function test_stdstatusupdate_custom_per_page(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/stdstatusupdate?per_page=25');

        $response->assertStatus(200);
        $response->assertSee('value="25"', false);

        $viewData = $response->original->getData();
        $this->assertEquals(25, $viewData['perPage']);
        $this->assertCount(25, $viewData['students']);
    }

    public function test_stdstatusupdate_per_page_cannot_cross_max(): void
    {
        // When requesting a number greater than max, it must clamp to maxAllowed
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/stdstatusupdate?per_page=999999');

        $response->assertStatus(200);

        $viewData = $response->original->getData();
        $this->assertLessThanOrEqual($viewData['maxAllowed'], $viewData['perPage']);
        $this->assertEquals($viewData['maxAllowed'], $viewData['perPage']);
    }

    public function test_stdstatusupdate_has_exact_three_statuses(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/stdstatusupdate');

        $response->assertStatus(200);

        $viewData = $response->original->getData();
        $statusKeys = array_keys($viewData['statuses']);

        $this->assertEquals(['ACTIVE', 'DISCONTINUED', 'GRADUATED'], $statusKeys);
    }

    public function test_stdstatusupdate_dropdown_renders_active_discontinued_graduated(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/stdstatusupdate?regno=261CC01014');

        $response->assertStatus(200);
        $response->assertSee('<option value="ACTIVE"', false);
        $response->assertSee('<option value="DISCONTINUED"', false);
        $response->assertSee('<option value="GRADUATED"', false);
        $response->assertDontSee('Debarred');
        $response->assertDontSee('Semester Break');
        $response->assertDontSee('Fee Blocked');
    }

    public function test_stdstatusupdate_renders_updated_batch_for_bhanuprasad(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/stdstatusupdate?regno=261BHANU99');

        $response->assertStatus(200);
        $response->assertSee('Y.BHANUPRASAD');
        $response->assertSee('261BHANU99');
        $response->assertSee('Batch:');
        $response->assertSee('JANUARY_2025');
        $response->assertDontSee('JANUARY_2026');
    }
}

