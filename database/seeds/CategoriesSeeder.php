<?php

use Illuminate\Database\Seeder;
use App\Models\Visitor;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Visitor::class, 10000)->create();
    }
}
