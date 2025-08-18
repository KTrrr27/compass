<?php

namespace Database\Seeders;

use App\Models\Users\Subjects;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Users\Subject;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 国語、数学、英語を追加
        Subjects::create(['subject' => '国語']);
        Subjects::create(['subject' => '数学']);
        Subjects::create(['subject' => '英語']);
    }
}
