<<<<<<<< Update Guide >>>>>>>>>>>

Immediate Older Version: 4.1.0
Current Version: 4.2.0

Feature Update:
1. Updated Language Key & Values For All User Panels.
2. Updated Agent Remittance Section.
3. Updated Strowallet Virtual Card Section.
4. Added Withdraw Webhooks Callback Api



Please Use This Commands On Your Terminal To Update Full System
1. To Run project Please Run This Command On Your Terminal
    composer update && composer dumpautoload && php artisan migrate


2. To Update Web & App Version Please Run This Command On Your Terminal
    php artisan db:seed --class=Database\\Seeders\\Update\\AppSettingsSeeder
    php artisan db:seed --class=Database\\Seeders\\Update\\BasicSettingsSeeder
    php artisan db:seed --class=Database\\Seeders\\Update\\UpdateCategoriesSeeder
    php artisan db:seed --class=Database\\Seeders\\Update\\UpdateBlogsSeeder
    php artisan db:seed --class=Database\\Seeders\\Admin\\TransactionSettingSeeder
    php artisan db:seed --class=Database\\Seeders\\Update\\SetupPageSeeder

