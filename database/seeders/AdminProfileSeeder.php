<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = \App\Models\Role::count();
        if($roles == 0)
        {
            \App\Models\Role::create(['role_name'=>'admin','role_status'=>1]);
            \App\Models\Role::create(['role_name'=>'user','role_status'=>1]);
            \App\Models\Role::create(['role_name'=>'company','role_status'=>1]);
            \App\Models\Role::create(['role_name'=>'employee','role_status'=>1]);
            \App\Models\Role::create(['role_name'=>'freelancer','role_status'=>1]);
        }

        $admin = \App\Models\User::whereHas( 'roles', function($q){ $q->where('role_name', 'admin'); })->first();
        if($admin == null)
        {
            $user = new \App\Models\User;
            $user->name = 'Trider';
            $user->email = 'admin@trider.info';
            $user->email_verified_at = now();
            $user->account_status=1;
            $user->password = \Hash::make('trider');
            $user->role = "admin";
            $user->status = 1;
            if($user->save())
            {
                $role = \App\Models\Role::where('role_name','admin')->first();
                $user->roles()->attach($role);
            }
        }

    }
}
