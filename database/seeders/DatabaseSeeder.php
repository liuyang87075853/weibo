<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        // \App\Models\User::factory(10)->create();
        $this->call(UsersTableSeeder::class);
        Model::reguard();
    }
}
