<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class AddressSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Foreach address
        foreach($this->getAddresses() as $address) {

            //  Create address
            Address::create($address);

        }
    }

    /**
     *  Return the addresses
     *
     *  @return array
     */
    public function getAddresses() {
        return [
            [
                'name' => 'Home',
                'address' => 'Tlokweng, Royal Aria, Plot 2084, Apartment Block J1',
                'share_address' => '1',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Work',
                'address' => 'Finance Park, Plot 1083, Optimum Q, Unit 6, Office 9',
                'share_address' => '1',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grannys House',
                'description' => 'Francistown, Aerodrome, Plot 235',
                'share_address' => '1',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
    }
}
