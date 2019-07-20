<?php

use App\Models\User;
use App\Models\UserData;
use Faker\Generator as Faker;

$factory->define(UserData::class, function (Faker $faker, array $data)
{   

    if(env('APP_DEBUG') && !isset($userId))
    {
        Log::warning("Using Factory[UserData] without setting user_id");
    }

    $user_id = isset($data['user_id']) ? $data['user_id'] : factory(User::class)->create()->id;

    return
    [
        'burner_name' => $faker->firstName,
        'full_name' => $faker->name,
        'birthday' => $faker->dateTimeThisCentury->format('Y-m-d'),
        'phone' => $faker->phoneNumber,
        'emergency_name' => $faker->name,
        'emergency_phone' => $faker->phoneNumber,
        'camp' => "open camping",
        'user_id' => $user_id,
    ];
});
