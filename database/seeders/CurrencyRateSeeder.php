<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CurrencyRate;
use Illuminate\Database\Seeder;

class CurrencyRateSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'description' => 'US Dollar', 'purchase_rate' => 15500.000000, 'sales_rate' => 15700.000000, 'unit' => 1],
            ['code' => 'EUR', 'description' => 'Euro', 'purchase_rate' => 16800.000000, 'sales_rate' => 17100.000000, 'unit' => 1],
            ['code' => 'GBP', 'description' => 'British Pound', 'purchase_rate' => 19500.000000, 'sales_rate' => 19800.000000, 'unit' => 1],
            ['code' => 'JPY', 'description' => 'Japanese Yen', 'purchase_rate' => 103.000000, 'sales_rate' => 106.000000, 'unit' => 1],
            ['code' => 'AUD', 'description' => 'Australian Dollar', 'purchase_rate' => 10200.000000, 'sales_rate' => 10400.000000, 'unit' => 1],
            ['code' => 'SGD', 'description' => 'Singapore Dollar', 'purchase_rate' => 11500.000000, 'sales_rate' => 11700.000000, 'unit' => 1],
            ['code' => 'MYR', 'description' => 'Malaysian Ringgit', 'purchase_rate' => 3400.000000, 'sales_rate' => 3500.000000, 'unit' => 1],
            ['code' => 'CNY', 'description' => 'Chinese Yuan', 'purchase_rate' => 2100.000000, 'sales_rate' => 2200.000000, 'unit' => 1],
        ];

        foreach ($currencies as $currency) {
            CurrencyRate::updateOrCreate(
                ['code' => $currency['code']],
                $currency,
            );
        }
    }
}
