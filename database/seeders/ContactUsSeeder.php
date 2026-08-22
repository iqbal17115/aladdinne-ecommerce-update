<?php

namespace Database\Seeders;

use App\Models\ContactUs;
use Illuminate\Database\Seeder;

class ContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactUs::query()->delete();

        ContactUs::create([
            'phone' => '+8801700000000',
            'email' => 'support@readyecommerce.com',
            'whatsapp' => '+8801700000000',
            'messenger' => 'https://m.me/readyecommerce',
            'address' => 'Dhaka, Bangladesh',
        ]);
    }
}
