<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiMessageCategory;
use Database\Seeders\Traits\SeederHelper;

class AiMessageCategorySeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Foreach ai message category
        foreach($this->getAiMessageCategories() as $category) {

            //  Create ai message category
            AiMessageCategory::create($category);

        }
    }

    /**
     *  Return the ai message categories
     *
     *  @return array
     */
    public function getAiMessageCategories() {
        return [
            [
                'name' => 'General',
                'description' => 'Ask me anything',
                'system_prompt' => 'Your name is Perfect Assistant and you are an expert consultant assisting businesses in Botswana.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sales',
                'description' => 'Ask me anything about Sales',
                'system_prompt' => 'Your name is Perfect Assistant and you are an expert sales consultant assisting businesses in Botswana.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marketing',
                'description' => 'Ask me anything about Marketing',
                'system_prompt' => 'Your name is Perfect Assistant and you are an expert marketing consultant assisting businesses in Botswana.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Ask me anything about Customer Service',
                'system_prompt' => 'Your name is Perfect Assistant and you are an expert customer service consultant assisting businesses in Botswana.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Product Development',
                'description' => 'Ask me anything about Product Development',
                'system_prompt' => 'Your name is Perfect Assistant and you are an expert product development consultant assisting businesses in Botswana.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Market Research',
                'description' => 'Ask me anything about Market Research',
                'system_prompt' => 'Your name is Perfect Assistant and you are an expert market research consultant assisting businesses in Botswana.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
