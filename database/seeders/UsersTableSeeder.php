<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Users\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'over_name' => '紺羽栖',
            'under_name' => '太郎',
            'over_name_kana' => 'コンパス',
            'under_name_kana' => 'タロウ',
            'mail_address' => 'com_taro@com.com',
            'sex' => '1',
            'birth_day' => '1999/09/09',
            'role' => '4',
            'password' => 'com_taro'
        ]);
    }
}
