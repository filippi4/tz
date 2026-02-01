<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Building;
use App\Models\Organization;
use App\Models\OrganizationPhone;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            'ООО "Ромашка"', 'ЗАО "Солнышко"', 'ООО "Гранит"', 'ПАО "Северсталь"',
            'ООО "Вектор"', 'ЗАО "Прогресс"', 'ООО "Альфа"', 'ПАО "Омега"',
            'ООО "Стройтех"', 'ЗАО "Мегаполис"', 'ООО "ИТ Решения"', 'ПАО "Консалтинг Плюс"',
            'ООО "Логистика 24"', 'ЗАО "Транспорт"', 'ООО "Пищепром"', 'ПАО "Машзавод"',
            'ООО "Рост"', 'ЗАО "Развитие"', 'ООО "Техносервис"', 'ПАО "Будущее"',
            'ООО "Инновации"', 'ЗАО "Технологии"', 'ООО "Бизнес Групп"', 'ПАО "Корпорация"',
            'ООО "Светлый путь"', 'ЗАО "Новый мир"', 'ООО "Глобал"', 'ПАО "Универсал"',
            'ООО "Премиум"', 'ЗАО "Стандарт"',
        ];

        $buildings = Building::all();
        $activities = Activity::all();

        foreach ($organizations as $orgName) {
            $organization = Organization::firstOrCreate(['name' => $orgName]);

            if ($organization->wasRecentlyCreated) {
                $phoneCount = rand(1, 3);
                for ($i = 0; $i < $phoneCount; $i++) {
                    OrganizationPhone::create([
                        'organization_id' => $organization->id,
                        'phone_number' => '+7'.rand(9000000000, 9999999999),
                    ]);
                }

                $buildingIds = $buildings->random(1)->pluck('id');
                $organization->buildings()->sync($buildingIds);

                $activityIds = $activities->random(rand(1, min(3, $activities->count())))->pluck('id');
                $organization->activities()->sync($activityIds);
            }
        }
    }
}
