<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_redirects_to_applicant_registration(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('applicant.register'));
    }

    public function test_applicant_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/applicant/register');

        $response->assertStatus(200);
    }
}
