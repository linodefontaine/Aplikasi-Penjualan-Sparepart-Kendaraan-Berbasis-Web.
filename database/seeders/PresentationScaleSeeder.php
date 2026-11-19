<?php

namespace Database\Seeders;

use App\Modules\Platform\Database\Seeders\PresentationScaleSeeder as ModulePresentationScaleSeeder;
use Illuminate\Database\Seeder;

class PresentationScaleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ModulePresentationScaleSeeder::class);
    }
}
