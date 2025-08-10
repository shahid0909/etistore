<?php

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::where('username', 'superadmin')->first();

        if (is_null($admin)) {
            $admin           = new Admin();
            $admin->name     = "Super Admin";
            $admin->email    = "superadmin@reflexsoft.com.bd";
            $admin->username = "superadmin";
            $admin->password = Hash::make('etistore@superadmin##');
            $admin->save();
        }
    }
}
