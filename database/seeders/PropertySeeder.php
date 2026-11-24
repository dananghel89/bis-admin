<?php

namespace Database\Seeders;

use App\Models\License;
use App\Models\Plan;
use App\Models\Property;
use App\Models\PropertyStatusChange;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo properties around the map center, with architects, status history, plans and
 * licenses. Every plan and license gets its own small PDF on the private disk.
 */
class PropertySeeder extends Seeder
{
    public const COUNT = 20;

    public function run(): void
    {
        $architects = User::where('role', User::EDITOR)->get();
        $staff = User::where('role', User::ADMIN)->first();

        $properties = Property::factory()->count(self::COUNT)->create(['created_by' => $staff?->id]);

        foreach ($properties as $i => $property) {
            // A few drafts (staff only), a few with no architect (every architect sees them).
            if ($i % 5 === 4) {
                $property->update(['status' => 'draft']);
            }

            if ($architects->isNotEmpty() && $i % 3 !== 0) {
                $property->architects()->attach($architects->random(min(fake()->numberBetween(1, 2), $architects->count()))->pluck('id'));
            }

            $this->history($property, $property->architects()->first() ?? $staff);
            $this->files($property, $staff);
        }

        $this->command?->info(self::COUNT.' demo properties with plans and licenses.');
    }

    /**
     * Status changes that lead to the property's current work status.
     */
    protected function history(Property $property, ?User $user): void
    {
        $steps = ['pending', 'in_progress', 'done'];
        $last = array_search($property->work_status, $steps, true);

        for ($step = 1; $step <= $last; $step++) {
            PropertyStatusChange::factory()->create([
                'property_id' => $property->id,
                'user_id' => $user?->id,
                'from_status' => $steps[$step - 1],
                'to_status' => $steps[$step],
                'created_at' => now()->subDays(($last - $step + 1) * fake()->numberBetween(3, 20)),
            ]);
        }
    }

    protected function files(Property $property, ?User $staff): void
    {
        foreach (range(1, fake()->numberBetween(1, 4)) as $n) {
            $plan = Plan::factory()->make(['property_id' => $property->id, 'uploaded_by' => $staff?->id]);
            $this->store($plan, 'plans', [$plan->title, $property->name, $property->fullAddress(), 'Demo plan']);
        }

        foreach (range(1, fake()->numberBetween(1, 3)) as $n) {
            $factory = License::factory();
            $state = fake()->randomElement(['valid', 'valid', 'expired', 'expiringSoon']);
            if ($state !== 'valid') {
                $factory = $factory->{$state}();
            }

            $license = $factory->make(['property_id' => $property->id, 'uploaded_by' => $staff?->id]);
            $this->store($license, 'licenses', [
                $license->title, $property->name, 'Number: '.$license->number,
                'Expires: '.$license->expires_at?->format('Y-m-d'), 'Demo license',
            ]);
        }
    }

    /**
     * @param  list<string>  $lines
     */
    protected function store(Plan|License $model, string $folder, array $lines): void
    {
        $pdf = DemoPdf::make($lines);
        $model->file_path = $folder.'/'.Str::random(40).'.pdf';
        $model->file_size = strlen($pdf);

        Storage::disk('local')->put($model->file_path, $pdf);
        $model->save();
    }
}
