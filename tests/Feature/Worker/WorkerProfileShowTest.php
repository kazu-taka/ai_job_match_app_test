<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;

test('未認証ユーザーはログイン画面にリダイレクトされる', function () {
    $response = $this->get(route('worker.profile'));
    $response->assertRedirect(route('login'));
});

test('企業ユーザーは403エラーになる', function () {
    $user = User::factory()->company()->create();
    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertForbidden();
});

test('ワーカープロフィールが未登録のワーカーユーザーは404エラーになる', function () {
    $user = User::factory()->worker()->create();
    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertNotFound();
});

test('ワーカーユーザーは自身のワーカープロフィールを表示できる', function () {
    $user = User::factory()->worker()->create([
        'name' => '佐藤太郎',
    ]);

    $desiredLocation = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'gender' => 'male',
        'birthdate' => '1990-05-15',
        'skills' => '接客、英語（TOEIC750点）、Excel',
        'experiences' => '飲食店勤務 5年、ホテルフロント 3年',
        'desired_jobs' => '接客・サービス',
        'desired_location_id' => $desiredLocation->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('ワーカープロフィール');
    $response->assertSee('佐藤太郎');
    $response->assertSee('男性');
    $response->assertSee('1990', false);
    $response->assertSee('5', false);
    $response->assertSee('15', false);
    $response->assertSee('接客、英語（TOEIC750点）、Excel');
    $response->assertSee('飲食店勤務 5年、ホテルフロント 3年');
    $response->assertSee('接客・サービス');
    $response->assertSee('東京都 千代田区');
});

test('性別が正しく日本語で表示される', function () {
    $user = User::factory()->worker()->create();

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'gender' => 'female',
        'birthdate' => '1995-08-22',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('女性');
});

test('年齢が正しく計算され表示される', function () {
    $user = User::factory()->worker()->create();

    $birthdate = now()->subYears(30)->format('Y-m-d');
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'birthdate' => $birthdate,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('30歳');
});

test('任意項目がNULLの場合は「未設定」と表示される', function () {
    $user = User::factory()->worker()->create();

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'skills' => null,
        'experiences' => null,
        'desired_jobs' => null,
        'desired_location_id' => null,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('未設定', false); // 複数箇所に表示されるため、falseでHTML検証を無効化
});

test('希望勤務地がcityなしの場合は都道府県のみ表示される', function () {
    $location = Location::factory()->prefectureOnly()->create([
        'prefecture' => '北海道',
        'city' => null,
    ]);

    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'desired_location_id' => $location->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('北海道');
});

test('ワーカープロフィールに登録日時と更新日時が表示される', function () {
    $user = User::factory()->worker()->create();
    $profile = WorkerProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('登録日時');
    $response->assertSee('更新日時');
    $response->assertSee($profile->created_at->format('Y年m月d日'));
    $response->assertSee($profile->updated_at->format('Y年m月d日'));
});

test('編集ボタンが表示される', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));
    $response->assertSuccessful();
    $response->assertSee('編集');
});
