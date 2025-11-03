<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = [
            ['address' => 'г. Москва, ул. Тверская, д. 1', 'latitude' => 557558000, 'longitude' => 376173000],
            ['address' => 'г. Москва, ул. Арбат, д. 10', 'latitude' => 557507000, 'longitude' => 375906000],
            ['address' => 'г. Москва, пр-т Ленина, д. 45', 'latitude' => 557601000, 'longitude' => 376520000],
            ['address' => 'г. Москва, ул. Невский, д. 22', 'latitude' => 559366000, 'longitude' => 303564000],
            ['address' => 'г. Москва, ул. Ломоносова, д. 5', 'latitude' => 559407000, 'longitude' => 303106000],
            ['address' => 'г. Москва, ул. Пушкинская, д. 12', 'latitude' => 559441000, 'longitude' => 303562000],
            ['address' => 'г. Москва, пр-т Мира, д. 100', 'latitude' => 558296000, 'longitude' => 377847000],
            ['address' => 'г. Москва, ул. Большая Морская, д. 30', 'latitude' => 559368000, 'longitude' => 303100000],
            ['address' => 'г. Москва, ул. Красная площадь, д. 3', 'latitude' => 557536000, 'longitude' => 376173000],
            ['address' => 'г. Москва, ул. Садовая, д. 25', 'latitude' => 557502000, 'longitude' => 376192000],
            ['address' => 'г. Москва, пр-т Кутузовский, д. 15', 'latitude' => 557415000, 'longitude' => 373485000],
            ['address' => 'г. Москва, ул. Тверская, д. 50', 'latitude' => 557645000, 'longitude' => 376122000],
            ['address' => 'г. Москва, ул. Лубянка, д. 7', 'latitude' => 557592000, 'longitude' => 376406000],
            ['address' => 'г. Москва, пр-т Вернадского, д. 86', 'latitude' => 556770000, 'longitude' => 375690000],
            ['address' => 'г. Москва, ул. Чайковского, д. 40', 'latitude' => 559377000, 'longitude' => 303675000],
            ['address' => 'г. Москва, пр-т Мира, д. 150', 'latitude' => 558596000, 'longitude' => 377847000],
            ['address' => 'г. Москва, ул. Маяковского, д. 8', 'latitude' => 557655000, 'longitude' => 376150000],
            ['address' => 'г. Москва, ул. Гороховая, д. 14', 'latitude' => 559372000, 'longitude' => 303214000],
            ['address' => 'г. Москва, пр-т Ленинский, д. 72', 'latitude' => 555924000, 'longitude' => 377132000],
            ['address' => 'г. Москва, ул. Петровка, д. 17', 'latitude' => 557626000, 'longitude' => 376202000],
        ];

        foreach ($buildings as $building) {
            Building::firstOrCreate($building);
        }
    }
}
