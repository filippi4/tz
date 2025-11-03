<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            [
                'name' => 'Торговля',
                'children' => [
                    ['name' => 'Розничная торговля'],
                    ['name' => 'Оптовая торговля'],
                    ['name' => 'Электронная коммерция'],
                ],
            ],
            [
                'name' => 'Производство',
                'children' => [
                    ['name' => 'Пищевое производство'],
                    ['name' => 'Машиностроение'],
                    ['name' => 'Легкая промышленность'],
                ],
            ],
            [
                'name' => 'Услуги',
                'children' => [
                    [
                        'name' => 'IT-услуги',
                        'children' => [
                            ['name' => 'Разработка ПО'],
                            ['name' => 'Техническая поддержка'],
                        ],
                    ],
                    ['name' => 'Консалтинг'],
                    ['name' => 'Образовательные услуги'],
                ],
            ],
            [
                'name' => 'Строительство',
                'children' => [
                    ['name' => 'Жилищное строительство'],
                    ['name' => 'Промышленное строительство'],
                ],
            ],
            [
                'name' => 'Транспорт и логистика',
                'children' => [
                    ['name' => 'Грузоперевозки'],
                    ['name' => 'Складские услуги'],
                ],
            ],
        ];

        foreach ($activities as $activity) {
            $this->createActivity($activity, null, 0);
        }
    }

    private function createActivity(array $data, ?int $parentId, int $level): void
    {
        $activity = Activity::firstOrCreate([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'level' => $level,
        ]);

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->createActivity($child, $activity->id, $level + 1);
            }
        }
    }
}
