<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// ID:4_日時取得機能のテスト
class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストの準備：画面表示に必要なルートを定義する
     */
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/dummy-list', function () {})->name('stamp_correction_request.list');
        Route::get('/dummy-approve', function () {})->name('stamp_correction_request.approve');
    }

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_date_is_displayed_correctly()
    {
        \Carbon\Carbon::setLocale('ja');

        $user = \App\Models\User::factory()->create();

        $now = \Carbon\Carbon::now();
        \Carbon\Carbon::setTestNow($now);

        $response = $this->actingAs($user)->get('/attendance');

        $expectedDate = $now->isoFormat('YYYY年M月D日(ddd)');

        $response->assertStatus(200);
        $response->assertSee($expectedDate);

        \Carbon\Carbon::setTestNow();
    }
}
