<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['code' => 'IM204', 'name' => 'Information Management', 'category' => 'Database', 'instructor' => 'Faculty of Computing', 'maximum_slots' => 40, 'available_slots' => 30, 'minimum_slots' => 5],
            ['code' => 'WEB201', 'name' => 'Web Systems and Technologies', 'category' => 'Web Development', 'instructor' => 'Faculty of Computing', 'maximum_slots' => 30, 'available_slots' => 5, 'minimum_slots' => 5],
            ['code' => 'NET210', 'name' => 'Computer Networking', 'category' => 'Networking', 'instructor' => 'Faculty of Computing', 'maximum_slots' => 35, 'available_slots' => 15, 'minimum_slots' => 5],
            ['code' => 'HCI105', 'name' => 'Human-Computer Interaction', 'category' => 'Design', 'instructor' => 'Faculty of Computing', 'maximum_slots' => 25, 'available_slots' => 0, 'minimum_slots' => 5],
        ];

        foreach ($courses as $course) {
            Course::query()->firstOrCreate(['code' => $course['code']], $course);
        }
    }
}
