<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\User;
use Workbench\Database\Factories\ArticleFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::forceCreate([
            'name' => 'Tony Messias',
            'email' => 'tonysm@hey.com',
            'password' => bcrypt('password'),
        ]);

        ArticleFactory::new()->times(10)->create();
    }
}
