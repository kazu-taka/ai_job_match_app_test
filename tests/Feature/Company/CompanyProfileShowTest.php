<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\User;

test('未認証ユーザーはログイン画面にリダイレクトされる', function () {
    $response = $this->get(route('company.profile'));
    $response->assertRedirect(route('login'));
});

test('ワーカーユーザーは403エラーになる', function () {
    $user = User::factory()->worker()->create();
    $this->actingAs($user);

    $response = $this->get(route('company.profile'));
    $response->assertForbidden();
});

test('企業プロフィールが未登録の企業ユーザーは404エラーになる', function () {
    $user = User::factory()->company()->create();
    $this->actingAs($user);

    $response = $this->get(route('company.profile'));
    $response->assertNotFound();
});

test('企業ユーザーは自身の企業プロフィールを表示できる', function () {
    $location = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $user = User::factory()->company()->create([
        'name' => '株式会社テスト',
    ]);

    $profile = CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'address' => '東京都千代田区丸の内1-1-1',
        'representative' => '山田太郎',
        'phone_number' => '03-1234-5678',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));
    $response->assertSuccessful();
    $response->assertSee('企業プロフィール');
    $response->assertSee('株式会社テスト');
    $response->assertSee('東京都 千代田区');
    $response->assertSee('東京都千代田区丸の内1-1-1');
    $response->assertSee('山田太郎');
    $response->assertSee('03-1234-5678');
});

test('企業プロフィールに登録日時と更新日時が表示される', function () {
    $user = User::factory()->company()->create();
    $profile = CompanyProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));
    $response->assertSuccessful();
    $response->assertSee('登録日時');
    $response->assertSee('更新日時');
    $response->assertSee($profile->created_at->format('Y年m月d日'));
    $response->assertSee($profile->updated_at->format('Y年m月d日'));
});

test('編集ボタンが準備中として表示される', function () {
    $user = User::factory()->company()->create();
    CompanyProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));
    $response->assertSuccessful();
    $response->assertSee('編集（準備中）');
});

test('所在地がcityなしの場合は都道府県のみ表示される', function () {
    $location = Location::factory()->prefectureOnly()->create([
        'prefecture' => '北海道',
        'city' => null,
    ]);

    $user = User::factory()->company()->create();
    CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));
    $response->assertSuccessful();
    $response->assertSee('北海道');
});
