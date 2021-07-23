<?php

use App\SupedioCustomer;
use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = SupedioCustomer::create([
            'name' => 'Seeburger Customer',
            'customer_number' => 1,
            'supedio_id' => 1,
        ]);
    }
}
