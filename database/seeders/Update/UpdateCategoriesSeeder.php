<?php

namespace Database\Seeders\Update;

use App\Models\Admin\Language;
use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class UpdateCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $blog_categories_from_db = BlogCategory::get();
        $languages = Language::get();

        foreach ($blog_categories_from_db as $blog_category) {
            // Update 'name' field as an object
            $languageTranslations = array();
            foreach ($languages as $language) {
                // Use the language code from the Language model
                $languageCode = $language->code;
                // Assuming the translation is the same as the original for the language code 'en'
                $translation = ($languageCode == 'en') ? $blog_category->name : null;

                $languageTranslations[$languageCode] = array('name' => $translation);
            }
            $blog_category->data = array('language' => $languageTranslations);

            // Save the updated record to the database
            $blog_category->save();
        }
    }
}
