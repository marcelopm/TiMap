<?php

use Illuminate\Database\Seeder;
use App\Models\Analyser;

/**
 *
 * @author marcelo moises
 */
class AnalysersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // get list of analyser names from app.php config file
        foreach (Analyser::getNameListFromConfig() as $analyser) {
            // workaround for creating if doesn't exist
            Analyser::firstOrCreate([
                'name' => $analyser,
            ]);
        }
    }
}
