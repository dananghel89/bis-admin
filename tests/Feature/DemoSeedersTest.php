<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Plan;
use App\Models\Property;
use App\Models\PropertyStatusChange;
use App\Models\User;
use Database\Seeders\ArchitectSeeder;
use Database\Seeders\PropertySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_architects_and_properties_but_no_admin()
    {
        Storage::fake('local');

        $this->seed();

        $this->assertSame(0, User::where('role', User::ADMIN)->count());
        $this->assertSame(ArchitectSeeder::COUNT, User::where('role', User::EDITOR)->count());
        $this->assertTrue(Hash::check(ArchitectSeeder::PASSWORD, User::first()->password));

        $this->assertSame(PropertySeeder::COUNT, Property::count());
        $this->assertGreaterThan(0, Property::where('status', 'draft')->count());
        $this->assertGreaterThan(0, Property::doesntHave('architects')->where('status', 'published')->count());
        $this->assertGreaterThan(0, Property::has('architects')->count());
        $this->assertSame(PropertySeeder::COUNT, Property::whereBetween('latitude', [41.3, 41.5])->whereBetween('longitude', [2.0, 2.3])->count());
    }

    public function test_status_history_leads_to_the_current_work_status()
    {
        Storage::fake('local');
        $this->seed();

        foreach (Property::where('work_status', '!=', 'pending')->get() as $property) {
            $this->assertSame($property->work_status, $property->statusChanges()->first()->to_status);
        }

        $this->assertSame(0, PropertyStatusChange::whereIn('property_id', Property::where('work_status', 'pending')->pluck('id'))->count());
    }

    public function test_every_plan_and_license_has_its_own_pdf()
    {
        Storage::fake('local');
        $this->seed();

        $files = Plan::pluck('file_path')->merge(License::pluck('file_path'));

        $this->assertGreaterThanOrEqual(PropertySeeder::COUNT * 2, $files->count());
        $this->assertSame($files->count(), $files->unique()->count());

        foreach ($files as $path) {
            Storage::disk('local')->assertExists($path);
            $this->assertStringStartsWith('%PDF-1.4', Storage::disk('local')->get($path));
        }
    }
}
