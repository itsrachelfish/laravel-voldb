<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Faker\Generator as Faker;

$factory->define(UserRole::class, function (Faker $faker, array $data)
{   

    if(env('APP_DEBUG') && !isset($data['role_id']))
    {
        Log::warning("Using Factory[UserRole] without setting role_id");
    }

    if(env('APP_DEBUG') && !isset($data['user_id']))
    {
        Log::warning("Using Factory[UserRole] without setting user_id");
    }

    $role_id = isset($data['role_id']) ? $data['role_id'] : factory(Role::class)->create()->id;
    $user_id = isset($data['user_id']) ? $data['user_id'] : factory(Role::class)->create()->id;

    return
    [
        'foreign_id' => 0,
        'foreign_type' => '',
        'role_id' => $role_id,
        'user_id' => $user_id,
    ];
});
