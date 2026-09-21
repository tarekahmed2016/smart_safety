<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * PlastexContentSeeder, CreativeIndustryContentSeeder, and HomepageContentSeeder
     * remain in the codebase for historical/manual use but are not called here.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            HomepageSectionSeeder::class,
        ]);
    }
}
