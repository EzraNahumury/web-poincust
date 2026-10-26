<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_customer_export_sanitizes_formula_injection(): void
    {
        $super = $this->superAdmin();
        Customer::factory()->create(['name' => '=HYPERLINK("http://evil")', 'username' => 'evil']);

        $response = $this->actingAs($super, 'web')->get(route('admin.reports.export', ['type' => 'customers']));
        $response->assertOk();

        $content = $response->streamedContent();
        // The dangerous leading '=' must be neutralised with a leading apostrophe.
        $this->assertStringContainsString("'=HYPERLINK", $content);
    }

    public function test_customer_export_contains_no_password_or_pin_hashes(): void
    {
        $super = $this->superAdmin();
        Customer::factory()->create();

        $content = $this->actingAs($super, 'web')
            ->get(route('admin.reports.export', ['type' => 'customers']))
            ->streamedContent();

        // Bcrypt / Argon hashes must never appear in an export.
        $this->assertStringNotContainsString('$2y$', $content);
        $this->assertStringNotContainsString('$argon2', $content);
    }

    public function test_export_requires_reports_permission(): void
    {
        $admin = $this->adminUser(); // has reports.view by default
        $this->actingAs($admin, 'web')
            ->get(route('admin.reports.export', ['type' => 'customers']))
            ->assertOk();
    }
}
