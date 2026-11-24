<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\MailAccount;
use App\Models\Plan;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_plan_or_license_deletes_its_file()
    {
        Storage::fake('local');
        $plan = Plan::factory()->create();
        $license = License::factory()->create();
        Storage::disk('local')->put($plan->file_path, 'pdf');
        Storage::disk('local')->put($license->file_path, 'pdf');

        $plan->delete();
        $license->delete();

        Storage::disk('local')->assertMissing($plan->file_path);
        Storage::disk('local')->assertMissing($license->file_path);
    }

    public function test_deleting_a_property_deletes_its_plans_licenses_files_and_history()
    {
        Storage::fake('local');
        $property = Property::factory()->hasPlans(2)->hasLicenses(1)->hasStatusChanges(1)->create();
        $property->architects()->attach(User::factory()->editor()->create());
        $paths = $property->plans->pluck('file_path')->merge($property->licenses->pluck('file_path'));
        $paths->each(fn ($path) => Storage::disk('local')->put($path, 'pdf'));

        $property->delete();

        $paths->each(fn ($path) => Storage::disk('local')->assertMissing($path));
        $this->assertSame(0, Plan::count() + License::count());
        $this->assertSame(0, DB::table('property_user')->count() + DB::table('property_status_changes')->count());
    }

    public function test_the_mail_password_is_stored_encrypted()
    {
        $account = MailAccount::factory()->create(['password' => 'plain-secret']);

        $stored = DB::table('mail_accounts')->value('password');
        $this->assertNotSame('plain-secret', $stored);
        $this->assertSame('plain-secret', $account->fresh()->password);
        $this->assertArrayNotHasKey('password', $account->toArray());
    }

    public function test_the_mail_login_defaults_to_the_email()
    {
        $this->assertSame('me@example.com', MailAccount::factory()->make(['email' => 'me@example.com'])->loginName());
        $this->assertSame('me', MailAccount::factory()->make(['email' => 'me@example.com', 'username' => 'me'])->loginName());
    }

    public function test_license_expiry()
    {
        $this->assertTrue(License::factory()->expired()->make()->isExpired());
        $this->assertTrue(License::factory()->expiringSoon()->make()->isExpiringSoon());
        $this->assertFalse(License::factory()->make(['expires_at' => today()->addYear()])->isExpiringSoon());
        $this->assertFalse(License::factory()->make(['expires_at' => null])->isExpired());

        License::factory()->expired()->create();
        License::factory()->expiringSoon()->create();
        License::factory()->create(['expires_at' => today()->addYear()]);
        $this->assertSame(2, License::needsAttention()->count());
    }

    public function test_property_labels_and_address()
    {
        $property = Property::factory()->make(['address' => 'Carrer Gran, 1', 'postal_code' => '08001', 'city' => 'Barcelona', 'work_status' => 'in_progress']);

        $this->assertSame('Carrer Gran, 1, 08001 Barcelona', $property->fullAddress());
        $this->assertSame('In progress', $property->workStatusLabel());
        $this->assertSame('draft', (new Property)->status);
        $this->assertFalse(Property::factory()->withoutLocation()->make()->hasLocation());
    }
}
