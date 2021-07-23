<?php

use App\Supedio;
use Illuminate\Database\Seeder;

class SupedioTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $today = date('YmdHi');
        // Production start date
        $startDate = date('YmdHi', strtotime('2012-03-14 09:06:00'));
        $range = $today - $startDate;
        $rand = rand(0, $range);

        $admin = Supedio::create([
            'name' => 'Seeburger',
            'customer_number' => 1,
            'orga_number' => "SP" . $rand,
        ]);
    }
}
