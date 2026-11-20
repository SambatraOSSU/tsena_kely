<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    // Chargement des articles initiaux pour Tsena Kely
    public function run(): void
    {
        $products = [
            [
                'name'        => 'Mofo baolina (Lot de 5)',
                'description' => 'Beignets traditionnels malgaches sucres et moelleux.',
                'price'       => 2500,
                'stock'       => 45,
                'category'    => 'Gouter & Fritures',
                'image'       => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'Sambos viande (Lot de 3)',
                'description' => 'Samoussas croustillants a la viande de boeuf parfume.',
                'price'       => 3000,
                'stock'       => 30,
                'category'    => 'Gouter & Fritures',
                'image'       => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'Kafe Gasy torrefie 250g',
                'description' => 'Cafe moulu artisanal de la cote est de Madagascar.',
                'price'       => 7500,
                'stock'       => 20,
                'category'    => 'Boissons',
                'image'       => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'The a la citronnelle 100g',
                'description' => 'Infusion naturelle de citronnelle sechee parfumee.',
                'price'       => 3500,
                'stock'       => 25,
                'category'    => 'Boissons',
                'image'       => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'Vary gasy mena (Sac 5kg)',
                'description' => 'Riz rouge traditionnel riche en mineraux et fibres.',
                'price'       => 18000,
                'stock'       => 15,
                'category'    => 'Epicerie',
                'image'       => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'Miel pur de Manakara 500g',
                'description' => 'Miel d eucalyptus 100% naturel recolte artisanalement.',
                'price'       => 14000,
                'stock'       => 12,
                'category'    => 'Epicerie',
                'image'       => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'Mofo akondro (Lot de 4)',
                'description' => 'Beignets a la banane douce frits a la minute.',
                'price'       => 2000,
                'stock'       => 35,
                'category'    => 'Gouter & Fritures',
                'image'       => 'https://images.unsplash.com/photo-1528975604071-b4dc52a2d18c?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name'        => 'Lasary karaoty & sakay 200g',
                'description' => 'Condiment de carottes pimentees pour accompagner le riz.',
                'price'       => 4000,
                'stock'       => 18,
                'category'    => 'Epicerie',
                'image'       => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'name'        => $p['name'],
                    'description' => $p['description'],
                    'price'       => $p['price'],
                    'stock'       => $p['stock'],
                    'category'    => $p['category'],
                    'image'       => $p['image'],
                ]
            );
        }

        // Initialisation des parametres d injection de panne
        SystemSetting::updateOrCreate(['key' => 'chaos_latency_ms'], ['value' => '0']);
        SystemSetting::updateOrCreate(['key' => 'chaos_error_rate'], ['value' => '0']);
        SystemSetting::updateOrCreate(['key' => 'chaos_stock_down'], ['value' => '0']);
        SystemSetting::updateOrCreate(['key' => 'chaos_orders_down'], ['value' => '0']);
    }
}
