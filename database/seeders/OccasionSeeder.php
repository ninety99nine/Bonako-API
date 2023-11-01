<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Occasion;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class OccasionSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Foreach occasion
        foreach($this->getOccasions() as $occasion) {

            //  Create occasion
            Occasion::create($occasion);

        }
    }

    /**
     *  Return the ai message categories
     *
     *  @return array
     */
    public function getOccasions() {
        return [
            [
                'name' => '🥳 Happy Birthday',
            ],
            [
                'name' => '❤️ Happy Valentines',
            ],
            [
                'name' => '👨‍🍼 Happy Fathers Day',
            ],
            [
                'name' => '🎉 New Year\'s Eve Celebration',
            ],
            [
                'name' => '🎓 Graduation Day',
            ],
            [
                'name' => '🎂 Anniversary',
            ],
            [
                'name' => '🌼 Mother\'s Day',
            ],
            [
                'name' => '🎄 Christmas Celebration',
            ],
            [
                'name' => '🥇 Achievement Celebration',
            ],
            [
                'name' => '🎈 Surprise Party',
            ],
            [
                'name' => '🏆 Sports Victory',
            ],
            [
                'name' => '🎊 Engagement Party',
            ],
            [
                'name' => '🌺 Wedding Day',
            ],
            [
                'name' => '🌟 Promotion Celebration',
            ],
            [
                'name' => '🍾 Housewarming Party',
            ],
            [
                'name' => '🎁 Gift Exchange',
            ],
            [
                'name' => '🐰 Easter Celebration',
            ],
            [
                'name' => '🦃 Thanksgiving Feast',
            ],
            [
                'name' => '🌸 Baby Shower',
            ],
            [
                'name' => '🍁 Autumn Get-Together',
            ],
            [
                'name' => '🌞 Summer Picnic',
            ],
            [
                'name' => '🌟 New Job Celebration',
            ],
            [
                'name' => '🏥 Get Well Soon',
            ],
        ];
    }
}
