<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatesTableSeeder extends Seeder
{
    public function run()
    {
        $states = [
            'Abia',
            'Adamawa',
            'Akwa Ibom',
            'Anambra',
            'Bauchi',
            'Bayelsa',
            'Benue',
            'Borno',
            'Cross River',
            'Delta',
            'Ebonyi',
            'Edo',
            'Ekiti',
            'Enugu',
            'Gombe',
            'Imo',
            'Jigawa',
            'Kaduna',
            'Kano',
            'Katsina',
            'Kebbi',
            'Kogi',
            'Kwara',
            'Lagos',
            'Nasarawa',
            'Niger',
            'Ogun',
            'Ondo',
            'Osun',
            'Oyo',
            'Plateau',
            'Rivers',
            'Sokoto',
            'Taraba',
            'Yobe',
            'Zamfara',
            'FCT'
        ];

        $now = Carbon::now();

        foreach ($states as $state) {
            DB::table('states')->updateOrInsert(
                ['name' => $state],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
