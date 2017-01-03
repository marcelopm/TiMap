<?php

use Illuminate\Database\Seeder;

/**
 *
 * @author marcelo moises
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@localhost.dev',
            'password' => bcrypt('admin'),
        ]);
    }
}
