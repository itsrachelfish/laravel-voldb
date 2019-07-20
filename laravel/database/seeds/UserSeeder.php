<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\UserRole;
use App\Models\UserData;

class UserSeeder extends Seeder 
{
    private function seedUsers()
    {
        
        $users = factory(User::class, 10)->create()->each(function ($user)
        {

            $user->data()->save( factory(UserData::class)->make(
                [
                    'user_id' => $user->id
                ]
            ));
        
            $user->roles()->save( factory(UserRole::class)->make(
                [
                    'user_id' => $user->id
                ]
            ));
        });
    }
    /** php artisan db:seed --class=UserSeeder */
    public function run()
    {
        $this->seedUsers();
    }
}