<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker, array $data)
{
    return
    [
        'name' => $faker->unique()->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt($faker->password),
    ];
});

$factory->state(User::class, 'admin', function (Faker $faker)
{
    return
    [
    ];
});

$factory->afterCreating(User::class, function (User $user, Faker $faker)
{
    //find the volunteer role
    $volunteer_role = Role::where('name', 'volunteer')->first();
    //if there is no volunteer role, create it
    if (!$volunteer_role)
    {
        $volunteer_role = factory(Role::class)->create([
            'name' => 'volunteer',
        ]);
    }

    $user->roles()->save(factory(UserRole::class)->make([
        'role_id' => $volunteer_role->id,
        'user_id' => $user->id,
    ]));
});

$factory->afterCreatingState(User::class, 'admin', function (User $user, Faker $faker)
{
    //find the admin role
    $admin_role = Role::where('name', 'admin')->first();
    //if there is no admin role, create it
    if (!$admin_role)
    {
        $admin_role = factory(Role::class)->create([
            'name' => 'admin',
        ]);
    }

    $user->roles()->save(factory(UserRole::class)->make([
        'role_id' => $admin_role->id,
        'user_id' => $user->id,
    ]));
});
