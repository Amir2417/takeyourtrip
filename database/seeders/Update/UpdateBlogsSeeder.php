<?php

namespace Database\Seeders\Update;

use App\Models\Admin\Language;
use App\Models\Blog;
use Illuminate\Database\Seeder;

class UpdateBlogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $blogs_from_db = Blog::get();
        $languages = Language::get();
        foreach ($blogs_from_db as $blog) {
            // Update 'name' field as an object
            $languageTranslations = array();
            foreach ($languages as $language) {
                // Use the language code from the Language model
                $languageCode = $language->code;
                // Assuming the translation is the same as the original for the language code 'en'
                $translation = ($languageCode == 'en') ? $blog->tags : null;

                $languageTranslations[$languageCode] = array('tags' => $translation);
            }
            $blog->lan_tags = array('language' => $languageTranslations);
            // Save the updated record to the database
            $blog->save();

        }
    }
}
