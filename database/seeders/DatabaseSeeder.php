<?php

namespace Database\Seeders;

use App\Models\AccessKey;
use App\Models\AccessKeyStatistic;
use App\Models\Partner;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function makeStat()
    {
        $accessKeys = [2, 3];

        $start = Carbon::now()->subDay()->startOfHour();
        $end = (clone $start)->addDay();

        foreach ($accessKeys as $accessKeyId) {
            $current = $start->copy();

            while ($current < $end) {

                // 🔀 случайное количество запросов в этом часу
                $requestsPerHour = random_int(10, 100);

                for ($i = 0; $i < $requestsPerHour; $i++) {
                    AccessKeyStatistic::create([
                        'access_key_id' => $accessKeyId,
                        'status' => random_int(1, 100) <= 95 ? 'success' : 'error', // ~5% ошибок
                        'data' => json_encode([
                            'duration_ms' => random_int(300, 120_000),
                            'memory_mb' => random_int(20, 180),
                            'source' => 'seed',
                        ]),
                        'created_at' => $current
                            ->copy()
                            ->addMinutes(random_int(0, 59))
                            ->addSeconds(random_int(0, 59)),
                        'updated_at' => now(),
                    ]);
                }

                $current->addHour();
            }
        }
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate([
            'name' => 'Admin Name',
            'email' => config('app.admin.email'),
            'email_verified_at' => now(),
            'password' => Hash::make(config('app.admin.password'))
        ]);

        Partner::create([
            'name' => 'taoteam.ru',
            'email' => 'taoteam@mail.ru'
        ]);

        Partner::create([
            'name' => 'New HR Platform',
            'email' => 'hrplatform@mail.ru'
        ]);


        AccessKey::firstOrCreate([
            'key' => '123',
            'name' => 'Тестовый ключ',
            'expires_at' => now()->addYear()
        ]);

        AccessKey::create([
            'name' => 'Ключ для taoteam',
            'expires_at' => now()->addYear(),
            'key' => '123456789'
        ]);


        AccessKey::create([
            'name' => 'Ключ для hrplatform',
            'expires_at' => now()->addYear(),
            'key' => '11111111111'
        ]);

        $this->makeStat();

    }
}
