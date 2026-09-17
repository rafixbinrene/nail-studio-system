<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class DefaultServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'service_name' => 'Manicure & Pedicure',
                'description' => 'Complete nail care including cleaning, shaping, cuticle care, and polish application.',
                'price' => 80,
                'duration' => 60,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Nail Art Design',
                'description' => 'Creative nail art design customized based on customer preference.',
                'price' => 60,
                'duration' => 45,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Nail Extension',
                'description' => 'Nail extension service for longer and styled nails.',
                'price' => 120,
                'duration' => 90,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Eyelash Extension',
                'description' => 'Eyelash extension service for fuller and longer lashes.',
                'price' => 150,
                'duration' => 90,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Lash Lift & Tint',
                'description' => 'Lift and tint treatment for natural eyelashes.',
                'price' => 100,
                'duration' => 60,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Basic Facial',
                'description' => 'Basic facial care for cleansing and refreshing the skin.',
                'price' => 90,
                'duration' => 60,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Backjob Follow-up',
                'description' => 'Follow-up appointment for service correction or backjob.',
                'price' => 0,
                'duration' => 30,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Nail Repair Follow-up',
                'description' => 'Follow-up service for nail repair.',
                'price' => 30,
                'duration' => 30,
                'status' => 'Available',
            ],
            [
                'service_name' => 'Lash/Beauty Follow-up',
                'description' => 'Follow-up service for lash or beauty-related concern.',
                'price' => 50,
                'duration' => 30,
                'status' => 'Available',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['service_name' => $service['service_name']],
                $service
            );
        }
    }
}