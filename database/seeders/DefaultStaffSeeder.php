<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultStaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffMembers = [
            [
                'name' => 'Nelisah',
                'email' => 'nelisah@nsbeauty.test',
                'phone_number' => '+60123456780',
            ],
            [
                'name' => 'Ivy',
                'email' => 'ivy@nsbeauty.test',
                'phone_number' => '+60123456781',
            ],
            [
                'name' => 'Annie Gloria',
                'email' => 'annie@nsbeauty.test',
                'phone_number' => '+60123456782',
            ],
            [
                'name' => 'Belinda',
                'email' => 'belinda@nsbeauty.test',
                'phone_number' => '+60123456783',
            ],
        ];

        foreach ($staffMembers as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password' => Hash::make('Staff12345'),
                    'role' => 'staff',
                    'status' => 'active',
                ]
            );

            Staff::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'full_name' => $staff['name'],
                    'phone_number' => $staff['phone_number'],
                    'status' => 'Active',
                ]
            );
        }
    }
}