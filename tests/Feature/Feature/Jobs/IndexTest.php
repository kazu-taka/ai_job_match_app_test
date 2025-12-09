<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\JobPost;
use App\Models\Location;
use App\Models\User;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// 認証が必要なテスト
test('求人一覧画面は未認証ユーザーはアクセスできない', function () {
    get(route('jobs.index'))
        ->assertRedirect(route('login'));
});

test('求人一覧画面は認証済みユーザーがアクセスできる', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('jobs.index'))
        ->assertSuccessful()
        ->assertSeeLivewire('jobs.index');
});

// 一覧表示のテスト
test('求人一覧が表示される', function () {
    $user = User::factory()->create();
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    // 求人を作成
    $jobs = JobPost::factory()->count(3)->create([
        'company_id' => $company->id,
    ]);

    actingAs($user);

    Volt::test('jobs.index')
        ->assertSee($jobs[0]->title)
        ->assertSee($jobs[1]->title)
        ->assertSee($jobs[2]->title)
        ->assertSee($company->name);
});

// 企業ユーザーの場合のテスト
test('企業ユーザーには新規求人投稿ボタンが表示される', function () {
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    actingAs($company)
        ->get(route('jobs.index'))
        ->assertSee('新規求人投稿（準備中）');
});

test('ワーカーユーザーには新規求人投稿ボタンが表示されない', function () {
    $worker = User::factory()->create(['role' => 'worker']);

    actingAs($worker)
        ->get(route('jobs.index'))
        ->assertDontSee('新規求人投稿');
});

// 検索機能のテスト
test('キーワード検索が動作する', function () {
    $user = User::factory()->create();
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    $matchingJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => 'Webエンジニア募集',
    ]);
    $nonMatchingJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '営業スタッフ募集',
    ]);

    actingAs($user);

    Volt::test('jobs.index')
        ->set('search', 'エンジニア')
        ->assertSee($matchingJob->title)
        ->assertDontSee($nonMatchingJob->title);
});

// 勤務地フィルタのテスト
test('勤務地フィルタ（市区町村）が動作する', function () {
    $user = User::factory()->create();
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    $tokyo = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
        'code' => '131016',
    ]);
    $osaka = Location::factory()->create([
        'prefecture' => '大阪府',
        'city' => '大阪市',
        'code' => '271004',
    ]);

    $tokyoJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '東京の求人',
        'location_id' => $tokyo->id,
    ]);
    $osakaJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '大阪の求人',
        'location_id' => $osaka->id,
    ]);

    actingAs($user);

    Volt::test('jobs.index')
        ->set('location_id', $tokyo->id)
        ->assertSee($tokyoJob->title)
        ->assertDontSee($osakaJob->title);
});

test('勤務地フィルタ（都道府県のみ）が動作する', function () {
    $user = User::factory()->create();
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    // 東京都の都道府県データと2つの市区町村
    $tokyoPrefecture = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => null,
        'code' => '130001',
    ]);
    $chiyoda = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
        'code' => '131016',
    ]);
    $shibuya = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '渋谷区',
        'code' => '131130',
    ]);

    // 大阪府の都道府県データと市区町村
    $osakaPrefecture = Location::factory()->create([
        'prefecture' => '大阪府',
        'city' => null,
        'code' => '270000',
    ]);
    $osakaCity = Location::factory()->create([
        'prefecture' => '大阪府',
        'city' => '大阪市',
        'code' => '271004',
    ]);

    // 求人作成
    $chiyodaJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '千代田区の求人',
        'location_id' => $chiyoda->id,
    ]);
    $shibuyaJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '渋谷区の求人',
        'location_id' => $shibuya->id,
    ]);
    $osakaJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '大阪の求人',
        'location_id' => $osakaCity->id,
    ]);

    actingAs($user);

    // 東京都を選択した場合、東京都内のすべての求人が表示される
    Volt::test('jobs.index')
        ->set('selectedPrefecture', $tokyoPrefecture->id)
        ->assertSee($chiyodaJob->title)
        ->assertSee($shibuyaJob->title)
        ->assertDontSee($osakaJob->title);
});

// 雇用形態フィルタのテスト
test('雇用形態フィルタが動作する', function () {
    $user = User::factory()->create();
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    $fullTimeJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => '正社員求人',
        'employment_type_id' => 1, // 正社員
    ]);
    $partTimeJob = JobPost::factory()->create([
        'company_id' => $company->id,
        'title' => 'パート求人',
        'employment_type_id' => 3, // パート
    ]);

    actingAs($user);

    Volt::test('jobs.index')
        ->set('employment_types', [1])
        ->assertSee($fullTimeJob->title)
        ->assertDontSee($partTimeJob->title);
});

// フィルタリセットのテスト
test('フィルタリセットが動作する', function () {
    $user = User::factory()->create();

    actingAs($user);

    Volt::test('jobs.index')
        ->set('search', 'テスト')
        ->set('employment_types', [1, 2])
        ->set('work_styles', [1])
        ->set('industries', [3])
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('employment_types', [])
        ->assertSet('work_styles', [])
        ->assertSet('industries', []);
});

// ページネーションのテスト
test('ページネーションが動作する', function () {
    $user = User::factory()->create();
    $company = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $company->id]);

    // 25件の求人を作成（1ページ20件なので2ページになる）
    $jobs = JobPost::factory()->count(25)->create([
        'company_id' => $company->id,
    ]);

    actingAs($user);

    $response = Volt::test('jobs.index');

    // 最初のページでは20件が表示される
    expect($response->instance()->jobs->count())->toBe(20);
});
