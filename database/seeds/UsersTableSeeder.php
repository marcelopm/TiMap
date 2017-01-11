<?php

use Illuminate\Database\Seeder;
use App\Models\User;

/**
 *
 * @author marcelo moises
 */
class UsersTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $user = User::firstOrNew([
            'name' => 'admin',
        ]);

        // if it's a new object, fill with the relevant information
        if (empty($user->id)) {
            $user->fill([
                'email' => 'admin@localhost.dev',
                'password' => bcrypt('admin'),
            ]);

            $user->save();
        }
    }

}
