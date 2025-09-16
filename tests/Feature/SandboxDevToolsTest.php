<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SandboxDevToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    /**
     * Test all sandbox development tools pages
     */
    public function test_sandbox_dashboard_loads()
    {
        $response = $this->get('/sandbox/dashboard');
        $response->assertStatus(200);
    }

    public function test_sandbox_sql_executor_loads()
    {
        $response = $this->get('/sandbox/sql-executor');
        $response->assertStatus(200);
    }

    public function test_sandbox_file_editor_loads()
    {
        $response = $this->get('/sandbox/file-editor');
        $response->assertStatus(200);
    }

    public function test_sandbox_database_manager_loads()
    {
        $response = $this->get('/sandbox/database-manager');
        $response->assertStatus(200);
    }

    public function test_sandbox_git_version_control_loads()
    {
        $response = $this->get('/sandbox/git-version-control');
        $response->assertStatus(200);
    }

    public function test_sandbox_function_browser_loads()
    {
        $response = $this->get('/sandbox/function-browser');
        $response->assertStatus(200);
    }

    public function test_sandbox_scenario_manager_loads()
    {
        $response = $this->get('/sandbox/scenario-manager');
        $response->assertStatus(200);
    }

    public function test_sandbox_documentation_manager_loads()
    {
        $response = $this->get('/sandbox/documentation-manager');
        $response->assertStatus(200);
    }

    public function test_sandbox_cron_manager_loads()
    {
        $response = $this->get('/sandbox/cron-manager');
        $response->assertStatus(200);
    }

    public function test_sandbox_callback_manager_loads()
    {
        $response = $this->get('/sandbox/callback-manager');
        $response->assertStatus(200);
    }

    public function test_sandbox_custom_screens_loads()
    {
        $response = $this->get('/sandbox/custom-screens');
        $response->assertStatus(200);
    }

    public function test_sandbox_form_publisher_loads()
    {
        $response = $this->get('/sandbox/form-publisher');
        $response->assertStatus(200);
    }

    public function test_sandbox_function_automation_loads()
    {
        $response = $this->get('/sandbox/function-automation');
        $response->assertStatus(200);
    }

    public function test_sandbox_function_creator_loads()
    {
        $response = $this->get('/sandbox/function-creator');
        $response->assertStatus(200);
    }
}