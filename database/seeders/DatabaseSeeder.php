<?php

namespace Database\Seeders;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // TEMPORARY PLACEHOLDERS until User/Auth and Marketplace modules are merged.
        $seller = User::query()->updateOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Temporary Seller',
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => 'Temporary Buyer',
                'password' => Hash::make('password'),
            ]
        );

        Gig::query()->updateOrCreate(
            [
                'user_id' => $seller->id,
                'title' => 'Temporary Laravel Development Gig',
            ],
            [
                'description' => 'A temporary gig used to test hire-request submission and viewing.',
                'category' => 'Programming and Tech',
                'price' => 1500,
                'delivery_time' => 3,
                'image' => null,
                'status' => 'active',
            ]
        );
    }
}
