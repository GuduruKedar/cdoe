<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    public function test_send_otp_with_invalid_usercode_returns_json_error(): void
    {
        $response = $this->postJson(route('send.otp'), [
            'usercode' => 'NON_EXISTENT_99999',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid User Code / Register Number.',
            ]);
    }

    public function test_send_otp_with_empty_usercode_returns_validation_error(): void
    {
        $response = $this->postJson(route('send.otp'), [
            'usercode' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['usercode']);
    }

    public function test_verify_otp_without_session_returns_json_error(): void
    {
        $response = $this->postJson(route('verify.otp'), [
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Session expired. Please request a new OTP.',
            ]);
    }
}
