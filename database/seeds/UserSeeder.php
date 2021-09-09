<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user')->insert([
            ['email'=>'admin1@gmail.com','password'=>Hash::make('123456'),'name'=>'Nguyễn Việt Hân','gender'=>1,'birth'=>null,'phone'=>'0383868688','address'=>'50 đường 144','status'=>1,'role'=>2,'remember_token'=>null],
            ['email'=>'admin2@gmail.com','password'=>Hash::make('123456'),'name'=>'Nguyễn Việt Anh','gender'=>1,'birth'=>null,'phone'=>'0383868699','address'=>'50 đường 144','status'=>1,'role'=>2,'remember_token'=>null],
            ['email'=>'taikhoan1@gmail.com','password'=>Hash::make('123456'),'name'=>'Nguyễn Thị Hương','gender'=>0,'birth'=>null,'phone'=>'0383861547','address'=>'10 đường 144','status'=>1,'role'=>1,'remember_token'=>null],
            ['email'=>'taikhoan2@gmail.com','password'=>Hash::make('123456'),'name'=>'Nguyễn Văn Long','gender'=>0,'birth'=>null,'phone'=>'0383863567','address'=>'22 đường 144','status'=>1,'role'=>1,'remember_token'=>null]
        ]);
    }
}