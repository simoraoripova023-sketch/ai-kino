<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Work;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Admin and Demo Users
        \App\Models\User::firstOrCreate(
            ['telegram_id' => 999999999],
            [
                'first_name' => 'Admin',
                'username' => 'aikino_admin',
                'language' => 'uz',
                'is_admin' => true,
                'email' => 'admin@aikino.app',
                'name' => 'AI Kino Administrator',
            ]
        );

        \App\Models\User::firstOrCreate(
            ['telegram_id' => 123456789],
            [
                'first_name' => 'Tomoshabin',
                'username' => 'demo_viewer',
                'language' => 'uz',
                'is_admin' => false,
                'email' => 'viewer@aikino.app',
                'name' => 'Demo Tomoshabin',
            ]
        );

        // 1. Seed Genres
        $genres = [
            ['slug' => 'scifi', 'name_uz' => 'Ilmiy fantastika', 'name_ru' => 'Научная фантастика', 'sort_order' => 1],
            ['slug' => 'fantasy', 'name_uz' => 'Fentezi', 'name_ru' => 'Фэнтези', 'sort_order' => 2],
            ['slug' => 'cyberpunk', 'name_uz' => 'Kiberpank', 'name_ru' => 'Киберпанк', 'sort_order' => 3],
            ['slug' => 'action', 'name_uz' => 'Jangari', 'name_ru' => 'Боевик', 'sort_order' => 4],
            ['slug' => 'animation', 'name_uz' => 'Multfilm', 'name_ru' => 'Мультфильм', 'sort_order' => 5],
        ];

        $genreModels = [];
        foreach ($genres as $g) {
            $genreModels[$g['slug']] = Genre::create($g);
        }

        // 2. 10 Sample AI Works
        $sampleWorks = [
            [
                'title' => 'Toshkent 2099',
                'description' => 'Kelajakdagi kiberpank Toshkent: sun\'iy intellekt va insoniyat o\'rtasidagi so\'nggi to\'qnashuv.',
                'format' => 'series',
                'style' => 'realistic',
                'language' => 'uz',
                'age_rating' => '16+',
                'poster_path' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=80',
                'cover_path' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200&auto=format&fit=crop&q=80',
                'trailer_file_id' => 'tg_sample_trailer_1',
                'status' => 'published',
                'pricing_mode' => 'work',
                'price_stars' => 30,
                'published_at' => now(),
                'genres' => ['scifi', 'cyberpunk'],
                'episodes_count' => 4,
            ],
            [
                'title' => 'Orolning Qaytishi',
                'description' => 'Orol dengizi tiklangan muqobil reallik haqidagi ta\'sirli ilmiy-ommabop AI asar.',
                'format' => 'film',
                'style' => 'realistic',
                'language' => 'uz',
                'age_rating' => '12+',
                'poster_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&auto=format&fit=crop&q=80',
                'cover_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&auto=format&fit=crop&q=80',
                'trailer_file_id' => 'tg_sample_trailer_2',
                'status' => 'published',
                'pricing_mode' => 'work',
                'price_stars' => 20,
                'published_at' => now(),
                'genres' => ['scifi'],
                'episodes_count' => 1,
            ],
            [
                'title' => 'Sehrli Samolyotcha',
                'description' => 'Bolalar va oila uchun mo\'ljallangan yorqin 3D animatsion sarguzasht.',
                'format' => 'series',
                'style' => 'animation_3d',
                'language' => 'uz',
                'age_rating' => '0+',
                'poster_path' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => null,
                'status' => 'published',
                'pricing_mode' => 'free',
                'price_stars' => 0,
                'published_at' => now(),
                'genres' => ['animation', 'fantasy'],
                'episodes_count' => 3,
            ],
            [
                'title' => 'Kvant Zamonasi',
                'description' => 'Vaqt bo\'ylab sayohat qiluvchi yosh fizik qizning hayratlanarli kechinmalari.',
                'format' => 'series',
                'style' => 'realistic',
                'language' => 'uz',
                'age_rating' => '12+',
                'poster_path' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => 'tg_sample_trailer_4',
                'status' => 'published',
                'pricing_mode' => 'episode',
                'price_stars' => 15,
                'published_at' => now(),
                'genres' => ['scifi'],
                'episodes_count' => 5,
            ],
            [
                'title' => 'Afsonaviy Samarqand',
                'description' => 'Buyuk ipak yo\'lining sirlari va qadimiy afsonalarning 2D AI animatsiyasi.',
                'format' => 'short',
                'style' => 'animation_2d',
                'language' => 'uz',
                'age_rating' => '6+',
                'poster_path' => 'https://images.unsplash.com/photo-1565008447742-97f6f38c985c?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => null,
                'status' => 'published',
                'pricing_mode' => 'free',
                'price_stars' => 0,
                'published_at' => now(),
                'genres' => ['animation', 'fantasy'],
                'episodes_count' => 2,
            ],
            [
                'title' => 'Neft va Qum',
                'description' => 'Post-apokaliptik cho\'lda resurslar uchun kurashayotgan mexanik jangchilar.',
                'format' => 'film',
                'style' => 'realistic',
                'language' => 'uz',
                'age_rating' => '16+',
                'poster_path' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => 'tg_sample_trailer_6',
                'status' => 'published',
                'pricing_mode' => 'work',
                'price_stars' => 25,
                'published_at' => now(),
                'genres' => ['action', 'scifi'],
                'episodes_count' => 1,
            ],
            [
                'title' => 'Yulduzlararo Kema',
                'description' => 'Begona galaktikaga yo\'l olgan avtonom stansiyadagi so\'nggi AI ekipaj.',
                'format' => 'series',
                'style' => 'mixed',
                'language' => 'ru',
                'age_rating' => '12+',
                'poster_path' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => null,
                'status' => 'published',
                'pricing_mode' => 'episode',
                'price_stars' => 10,
                'published_at' => now(),
                'genres' => ['scifi'],
                'episodes_count' => 3,
            ],
            [
                'title' => 'O\'rmon Sehrgari',
                'description' => 'Tabiatni qutqarishga bel bog\'lagan mitti jonzotlarning 3D ertak sarguzashti.',
                'format' => 'series',
                'style' => 'animation_3d',
                'language' => 'uz',
                'age_rating' => '0+',
                'poster_path' => 'https://images.unsplash.com/photo-1511447333015-45b65e60f6d5?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => null,
                'status' => 'published',
                'pricing_mode' => 'free',
                'price_stars' => 0,
                'published_at' => now(),
                'genres' => ['animation', 'fantasy'],
                'episodes_count' => 4,
            ],
            [
                'title' => 'Kiber Detektiv',
                'description' => 'Virtual haqiqatdagi sirli jinoyatni fosh etuvchi detektiv va uning AI yordamchisi.',
                'format' => 'film',
                'style' => 'realistic',
                'language' => 'uz',
                'age_rating' => '16+',
                'poster_path' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => 'tg_sample_trailer_9',
                'status' => 'published',
                'pricing_mode' => 'work',
                'price_stars' => 40,
                'published_at' => now(),
                'genres' => ['cyberpunk', 'action'],
                'episodes_count' => 1,
            ],
            [
                'title' => 'Ranglar Shaharchasi',
                'description' => 'Ranglarini yo\'qotgan dunyoga qayta hayot bag\'ishlash haqidagi yorqin qisqa metrajli asar.',
                'format' => 'short',
                'style' => 'animation_2d',
                'language' => 'uz',
                'age_rating' => '0+',
                'poster_path' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?w=600&auto=format&fit=crop&q=80',
                'cover_path' => null,
                'trailer_file_id' => null,
                'status' => 'published',
                'pricing_mode' => 'free',
                'price_stars' => 0,
                'published_at' => now(),
                'genres' => ['animation'],
                'episodes_count' => 1,
            ],
        ];

        foreach ($sampleWorks as $data) {
            $genresList = $data['genres'];
            $epCount = $data['episodes_count'];
            unset($data['genres'], $data['episodes_count']);

            $work = Work::create($data);

            // Attach genres
            foreach ($genresList as $gSlug) {
                if (isset($genreModels[$gSlug])) {
                    $work->genres()->attach($genreModels[$gSlug]->id);
                }
            }

            // Create episodes
            for ($i = 1; $i <= $epCount; $i++) {
                Episode::create([
                    'work_id' => $work->id,
                    'number' => $i,
                    'title' => $epCount > 1 ? "{$i}-qism" : $work->title,
                    'duration_sec' => rand(180, 1200),
                    'file_id' => "tg_file_work_{$work->id}_ep_{$i}",
                    'file_unique_id' => "unique_{$work->id}_{$i}",
                    'storage_chat_id' => -1001234567890,
                    'storage_message_id' => 100 + $work->id * 10 + $i,
                    'status' => 'published',
                    'publish_at' => now(),
                    'is_preview' => ($i === 1 && $epCount > 1),
                    'price_stars' => $work->pricing_mode === 'episode' ? ($i === 1 ? 0 : $work->price_stars) : null,
                ]);
            }
        }
    }
}
