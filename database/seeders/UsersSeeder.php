<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123456789');

        $users = [
            [
                'first_name'          => 'Karim',
                'last_name'           => 'Benali',
                'email'               => 'karim.benali@example.com',
                'gender'              => 'male',
                'birthday'            => '1990-03-15',
                'city_id'      => 1, // Casablanca
                
                'nationality_id'      => 1, // Marocain
                'job_title'           => 'Directeur Commercial',
                'points_balance'      => 42,
                'badge_level'         => 'bronze',
                'languages'           => [
                    ['id' => 4, 'level' => 'native'],   // Français
                    ['id' => 1, 'level' => 'native'],   // Arabe
                    ['id' => 5, 'level' => 'fluent'],   // Anglais
                ],
                'interests'           => [2, 4, 10],    // Marketing, Ventes, Consulting
            ],
            [
                'first_name'          => 'Sara',
                'last_name'           => 'Idrissi',
                'email'               => 'sara.idrissi@example.com',
                'gender'              => 'female',
                'birthday'            => '1993-07-22',
                'city_id'      => 2, // Rabat
                 // Marrakech
                'nationality_id'      => 1,
                'job_title'           => 'Product Manager',
                'points_balance'      => 87,
                'badge_level'         => 'argent',
                'languages'           => [
                    ['id' => 4, 'level' => 'native'],
                    ['id' => 5, 'level' => 'fluent'],
                    ['id' => 6, 'level' => 'intermediate'], // Espagnol
                ],
                'interests'           => [1, 12, 20],   // Technologie, Startup, IA
            ],
            [
                'first_name'          => 'Youssef',
                'last_name'           => 'El Amrani',
                'email'               => 'youssef.elamrani@example.com',
                'gender'              => 'male',
                'birthday'            => '1988-11-05',
                'city_id'      => 1, // Casablanca
                 // Fès
                'nationality_id'      => 1,
                'job_title'           => 'CEO & Co-Fondateur',
                'points_balance'      => 165,
                'badge_level'         => 'or',
                'languages'           => [
                    ['id' => 1, 'level' => 'native'],
                    ['id' => 4, 'level' => 'native'],
                    ['id' => 5, 'level' => 'fluent'],
                    ['id' => 8, 'level' => 'intermediate'], // Allemand
                ],
                'interests'           => [12, 3, 10, 1], // Startup, Finance, Consulting, Tech
            ],
            [
                'first_name'          => 'Nadia',
                'last_name'           => 'Rousseau',
                'email'               => 'nadia.rousseau@example.com',
                'gender'              => 'female',
                'birthday'            => '1991-02-18',
                'city_id'      => 91, // Paris
                
                'nationality_id'      => 6, // (France — on prend ce qui existe)
                'job_title'           => 'Business Developer',
                'points_balance'      => 55,
                'badge_level'         => 'argent',
                'languages'           => [
                    ['id' => 4, 'level' => 'native'],   // Français
                    ['id' => 5, 'level' => 'fluent'],   // Anglais
                    ['id' => 9, 'level' => 'basic'],    // Italien
                ],
                'interests'           => [2, 4, 19],    // Marketing, Ventes, Tourisme
            ],
            [
                'first_name'          => 'Mehdi',
                'last_name'           => 'Tahiri',
                'email'               => 'mehdi.tahiri@example.com',
                'gender'              => 'male',
                'birthday'            => '1995-09-30',
                'city_id'      => 5, // Tanger
                
                'nationality_id'      => 1,
                'job_title'           => 'Développeur Full-Stack',
                'points_balance'      => 18,
                'badge_level'         => 'bronze',
                'languages'           => [
                    ['id' => 1, 'level' => 'native'],
                    ['id' => 4, 'level' => 'fluent'],
                    ['id' => 5, 'level' => 'fluent'],
                ],
                'interests'           => [1, 20, 16],   // Technologie, IA, Design
            ],
            [
                'first_name'          => 'Amira',
                'last_name'           => 'Benali',
                'email'               => 'amira.benali@example.com',
                'gender'              => 'female',
                'birthday'            => '1989-06-12',
                'city_id'      => 74, // Dubaï
                  // Casablanca
                'nationality_id'      => 1,
                'job_title'           => 'Head of Marketing',
                'points_balance'      => 210,
                'badge_level'         => 'or',
                'languages'           => [
                    ['id' => 1, 'level' => 'native'],
                    ['id' => 4, 'level' => 'fluent'],
                    ['id' => 5, 'level' => 'fluent'],
                    ['id' => 6, 'level' => 'intermediate'],
                ],
                'interests'           => [2, 11, 12, 20], // Marketing, E-commerce, Startup, IA
            ],
            [
                'first_name'          => 'Thomas',
                'last_name'           => 'Renard',
                'email'               => 'thomas.renard@example.com',
                'gender'              => 'male',
                'birthday'            => '1986-04-07',
                'city_id'      => 91, // Paris
                
                'nationality_id'      => 6,
                'job_title'           => 'Directeur Général',
                'points_balance'      => 73,
                'badge_level'         => 'argent',
                'languages'           => [
                    ['id' => 4, 'level' => 'native'],
                    ['id' => 5, 'level' => 'fluent'],
                    ['id' => 8, 'level' => 'basic'],
                ],
                'interests'           => [3, 10, 14],   // Finance, Consulting, Industrie
            ],
            [
                'first_name'          => 'Fatima',
                'last_name'           => 'Zahra Alaoui',
                'email'               => 'fatima.alaoui@example.com',
                'gender'              => 'female',
                'birthday'            => '1994-12-25',
                'city_id'      => 3, // Marrakech
                
                'nationality_id'      => 1,
                'job_title'           => 'Avocate d\'affaires',
                'points_balance'      => 31,
                'badge_level'         => 'bronze',
                'languages'           => [
                    ['id' => 1, 'level' => 'native'],
                    ['id' => 4, 'level' => 'native'],
                    ['id' => 5, 'level' => 'intermediate'],
                ],
                'interests'           => [6, 9, 10],    // Juridique, Immobilier, Consulting
            ],
            [
                'first_name'          => 'Omar',
                'last_name'           => 'Benjelloun',
                'email'               => 'omar.benjelloun@example.com',
                'gender'              => 'male',
                'birthday'            => '1987-08-14',
                'city_id'      => 6, // Agadir
                
                'nationality_id'      => 1,
                'job_title'           => 'Responsable Logistique',
                'points_balance'      => 26,
                'badge_level'         => 'bronze',
                'languages'           => [
                    ['id' => 1, 'level' => 'native'],
                    ['id' => 2, 'level' => 'native'],   // Darija
                    ['id' => 4, 'level' => 'fluent'],
                ],
                'interests'           => [13, 14, 18],  // Logistique, Industrie, Agriculture
            ],
            [
                'first_name'          => 'Leila',
                'last_name'           => 'Moussaoui',
                'email'               => 'leila.moussaoui@example.com',
                'gender'              => 'female',
                'birthday'            => '1992-01-29',
                'city_id'      => 2, // Rabat
                 // Oujda
                'nationality_id'      => 1,
                'job_title'           => 'RH & Talent Acquisition',
                'points_balance'      => 48,
                'badge_level'         => 'bronze',
                'languages'           => [
                    ['id' => 1, 'level' => 'native'],
                    ['id' => 4, 'level' => 'fluent'],
                    ['id' => 5, 'level' => 'intermediate'],
                ],
                'interests'           => [5, 8, 10],    // RH, Éducation, Consulting
            ],
        ];

        foreach ($users as $data) {
            $languages = $data['languages'];
            $interests  = $data['interests'];
            $jobTitle = $data['job_title'];
            unset($data['languages'], $data['interests'], $data['job_title']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'             => $password,
                    'email_verified_at'    => now(),
                    'onboarding_completed' => true,
                ])
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['job_title' => $jobTitle]
            );

            // Langues
            foreach ($languages as $lang) {
                DB::table('user_languages')->updateOrInsert(
                    ['user_id' => $user->id, 'language_id' => $lang['id']],
                    ['level' => $lang['level'], 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // Intérêts
            foreach ($interests as $interestId) {
                DB::table('user_interests')->updateOrInsert(
                    ['user_id' => $user->id, 'interest_id' => $interestId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
