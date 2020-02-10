<?php

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $faker = app('Faker\Generator');
            $dataArray = [
                [
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'email_verified_at' => now(),
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'remember_token' => Str::random(10),
                ],
                [
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'email_verified_at' => now(),
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'remember_token' => Str::random(10),
                ]
            ];
            \App\User::insert($dataArray);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit;
        }
    }
}
