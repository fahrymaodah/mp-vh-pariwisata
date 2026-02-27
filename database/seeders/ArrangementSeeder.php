<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PostingType;
use App\Models\Arrangement;
use App\Models\ArrangementLine;
use App\Models\Article;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ArrangementSeeder extends Seeder
{
    public function run(): void
    {
        $roomRevenue = Article::where('article_no', '1000')->first();
        $breakfast = Article::where('article_no', '2000')->first();
        $foDept = Department::where('code', 'FO')->first();
        $fbDept = Department::where('code', 'FB')->first();

        // RO - Room Only
        $ro = Arrangement::updateOrCreate(
            ['code' => 'RO'],
            [
                'description' => 'Room Only',
                'invoice_label' => 'Room Only',
                'lodging_article_id' => $roomRevenue?->id,
                'arrangement_article_id' => null,
                'min_stay' => 0,
                'currency_code' => 'IDR',
            ]
        );

        ArrangementLine::updateOrCreate(
            ['arrangement_id' => $ro->id, 'article_id' => $roomRevenue?->id],
            [
                'department_id' => $foDept->id,
                'amount' => 0,
                'posting_type' => PostingType::Daily,
                'included_in_room_rate' => true,
                'qty_always_one' => true,
                'guest_type' => 'adult',
            ]
        );

        // RB - Room & Breakfast
        $rb = Arrangement::updateOrCreate(
            ['code' => 'RB'],
            [
                'description' => 'Room & Breakfast',
                'invoice_label' => 'Room & Breakfast',
                'lodging_article_id' => $roomRevenue?->id,
                'arrangement_article_id' => $breakfast?->id,
                'min_stay' => 0,
                'currency_code' => 'IDR',
            ]
        );

        ArrangementLine::updateOrCreate(
            ['arrangement_id' => $rb->id, 'article_id' => $roomRevenue?->id],
            [
                'department_id' => $foDept->id,
                'amount' => 0,
                'posting_type' => PostingType::Daily,
                'included_in_room_rate' => true,
                'qty_always_one' => true,
                'guest_type' => 'adult',
            ]
        );

        ArrangementLine::updateOrCreate(
            ['arrangement_id' => $rb->id, 'article_id' => $breakfast?->id],
            [
                'department_id' => $fbDept->id,
                'amount' => 150000,
                'posting_type' => PostingType::Daily,
                'included_in_room_rate' => true,
                'qty_always_one' => false,
                'guest_type' => 'adult',
            ]
        );

        // FB - Full Board (Room + B/L/D)
        $fb = Arrangement::updateOrCreate(
            ['code' => 'FB'],
            [
                'description' => 'Full Board',
                'invoice_label' => 'Full Board (Room + Breakfast + Lunch + Dinner)',
                'lodging_article_id' => $roomRevenue?->id,
                'arrangement_article_id' => $breakfast?->id,
                'min_stay' => 0,
                'currency_code' => 'IDR',
            ]
        );

        // HB - Half Board (Room + Breakfast + Dinner)
        Arrangement::updateOrCreate(
            ['code' => 'HB'],
            [
                'description' => 'Half Board',
                'invoice_label' => 'Half Board (Room + Breakfast + Dinner)',
                'lodging_article_id' => $roomRevenue?->id,
                'arrangement_article_id' => $breakfast?->id,
                'min_stay' => 0,
                'currency_code' => 'IDR',
            ]
        );
    }
}
