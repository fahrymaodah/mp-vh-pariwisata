<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all()->keyBy('code');

        $articles = [
            // Front Office - Lodging
            ['article_no' => '1000', 'name' => 'Room Revenue', 'department' => 'FO', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => false],
            ['article_no' => '1001', 'name' => 'Extra Bed', 'department' => 'FO', 'type' => ArticleType::Sales, 'default_price' => 200000, 'tax_inclusive' => true],
            ['article_no' => '1002', 'name' => 'Early Check-In', 'department' => 'FO', 'type' => ArticleType::Sales, 'default_price' => 150000, 'tax_inclusive' => true],
            ['article_no' => '1003', 'name' => 'Late Check-Out', 'department' => 'FO', 'type' => ArticleType::Sales, 'default_price' => 150000, 'tax_inclusive' => true],

            // Food & Beverage
            ['article_no' => '2000', 'name' => 'Restaurant Breakfast', 'department' => 'FB', 'type' => ArticleType::Sales, 'default_price' => 150000, 'tax_inclusive' => true],
            ['article_no' => '2001', 'name' => 'Restaurant Lunch', 'department' => 'FB', 'type' => ArticleType::Sales, 'default_price' => 200000, 'tax_inclusive' => true],
            ['article_no' => '2002', 'name' => 'Restaurant Dinner', 'department' => 'FB', 'type' => ArticleType::Sales, 'default_price' => 250000, 'tax_inclusive' => true],
            ['article_no' => '2003', 'name' => 'Room Service', 'department' => 'FB', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => true],

            // Laundry
            ['article_no' => '3000', 'name' => 'Laundry Service', 'department' => 'LDY', 'type' => ArticleType::Sales, 'default_price' => 50000, 'tax_inclusive' => true],
            ['article_no' => '3001', 'name' => 'Pressing Service', 'department' => 'LDY', 'type' => ArticleType::Sales, 'default_price' => 30000, 'tax_inclusive' => true],

            // Telephone
            ['article_no' => '4000', 'name' => 'Telephone Local', 'department' => 'TEL', 'type' => ArticleType::Sales, 'default_price' => 5000, 'tax_inclusive' => true],
            ['article_no' => '4001', 'name' => 'Telephone Long Distance', 'department' => 'TEL', 'type' => ArticleType::Sales, 'default_price' => 25000, 'tax_inclusive' => true],
            ['article_no' => '4002', 'name' => 'Telephone International', 'department' => 'TEL', 'type' => ArticleType::Sales, 'default_price' => 50000, 'tax_inclusive' => true],

            // Spa
            ['article_no' => '5000', 'name' => 'Spa Treatment', 'department' => 'SPA', 'type' => ArticleType::Sales, 'default_price' => 350000, 'tax_inclusive' => true],
            ['article_no' => '5001', 'name' => 'Massage', 'department' => 'SPA', 'type' => ArticleType::Sales, 'default_price' => 250000, 'tax_inclusive' => true],

            // Minibar
            ['article_no' => '6000', 'name' => 'Minibar Consumption', 'department' => 'MBC', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => true],

            // Bar
            ['article_no' => '7000', 'name' => 'Bar Beverage', 'department' => 'BAR', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => true],

            // Banquet
            ['article_no' => '8000', 'name' => 'Meeting Room', 'department' => 'BQT', 'type' => ArticleType::Sales, 'default_price' => 1000000, 'tax_inclusive' => true],
            ['article_no' => '8001', 'name' => 'Coffee Break', 'department' => 'BQT', 'type' => ArticleType::Sales, 'default_price' => 75000, 'tax_inclusive' => true],

            // Drugstore
            ['article_no' => '9000', 'name' => 'Drugstore Item', 'department' => 'DRG', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => true],

            // Payment articles
            ['article_no' => 'P100', 'name' => 'Cash Payment', 'department' => 'PMT', 'type' => ArticleType::Payment, 'default_price' => 0, 'tax_inclusive' => false],
            ['article_no' => 'P101', 'name' => 'Credit Card Payment', 'department' => 'PMT', 'type' => ArticleType::Payment, 'default_price' => 0, 'tax_inclusive' => false],
            ['article_no' => 'P102', 'name' => 'Bank Transfer', 'department' => 'PMT', 'type' => ArticleType::Payment, 'default_price' => 0, 'tax_inclusive' => false],
            ['article_no' => 'P103', 'name' => 'City Ledger', 'department' => 'PMT', 'type' => ArticleType::Payment, 'default_price' => 0, 'tax_inclusive' => false],

            // Miscellaneous
            ['article_no' => 'M100', 'name' => 'Miscellaneous Charge', 'department' => 'MSC', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => true],
            ['article_no' => 'M101', 'name' => 'Damage Charge', 'department' => 'MSC', 'type' => ArticleType::Sales, 'default_price' => 0, 'tax_inclusive' => true],
        ];

        foreach ($articles as $article) {
            $deptCode = $article['department'];
            unset($article['department']);

            Article::updateOrCreate(
                ['article_no' => $article['article_no']],
                array_merge($article, ['department_id' => $departments[$deptCode]->id]),
            );
        }
    }
}
