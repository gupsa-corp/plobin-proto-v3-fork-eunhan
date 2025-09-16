<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SandboxFormCreatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_form_creator_page_loads_without_auth()
    {
        $response = $this->get('/sandbox/form-creator');
        
        $response->assertStatus(200);
        $response->assertViewIs('700-page-sandbox.709-page-form-creator.000-index');
        $response->assertSee('FormEngine Demo');
    }

    public function test_form_creator_page_loads_with_auth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->get('/sandbox/form-creator');
        
        $response->assertStatus(200);
        $response->assertViewIs('700-page-sandbox.709-page-form-creator.000-index');
    }

    public function test_form_creator_page_has_required_elements()
    {
        $response = $this->get('/sandbox/form-creator');

        $response->assertStatus(200);
        // Check for key UI elements
        $response->assertSee('컴포넌트');
        $response->assertSee('Form Canvas');
        $response->assertSee('속성');
    }
}