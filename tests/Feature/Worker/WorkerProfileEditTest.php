<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Livewire\Volt\Volt;

test('未認証ユーザーはログイン画面にリダイレクトされる', function () {
    $response = $this->get(route('worker.edit'));
    $response->assertRedirect(route('login'));
});

test('企業ユーザーは403エラーになる', function () {
    $user = User::factory()->company()->create();
    $this->actingAs($user);

    $response = $this->get(route('worker.edit'));
    $response->assertForbidden();
});

test('ワーカープロフィールが未登録のワーカーユーザーは404エラーになる', function () {
    $user = User::factory()->worker()->create();
    $this->actingAs($user);

    $response = $this->get(route('worker.edit'));
    $response->assertNotFound();
});

test('ワーカーユーザーは編集画面を表示できる', function () {
    $user = User::factory()->worker()->create([
        'name' => '佐藤太郎',
    ]);

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'gender' => 'male',
        'birthdate' => '1990-05-15',
        'skills' => '接客、英語',
        'experiences' => '飲食店勤務 5年',
        'desired_jobs' => '接客・サービス',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.edit'));
    $response->assertSuccessful();
    $response->assertSee('ワーカープロフィール編集');
    $response->assertSee('性別');
    $response->assertSee('生年月日');
    $response->assertSee('スキル');
    $response->assertSee('経験');
    $response->assertSee('希望職種');
    $response->assertSee('希望勤務地');
});

test('既存データがフォームに表示される', function () {
    $user = User::factory()->worker()->create();

    $desiredLocation = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'gender' => 'female',
        'birthdate' => '1995-08-22',
        'skills' => 'プログラミング',
        'experiences' => 'Web開発 3年',
        'desired_jobs' => 'エンジニア',
        'desired_location_id' => $desiredLocation->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.edit'));
    $response->assertSuccessful();
});

test('有効なデータでプロフィール更新ができる', function () {
    $user = User::factory()->worker()->create();
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'gender' => 'male',
        'birthdate' => '1990-05-15',
    ]);

    $newLocation = Location::factory()->create([
        'prefecture' => '大阪府',
        'city' => '大阪市',
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'female')
        ->set('birthYear', 1995)
        ->set('birthMonth', 8)
        ->set('birthDay', 22)
        ->set('skills', '新しいスキル')
        ->set('experiences', '新しい経験')
        ->set('desired_jobs', '新しい希望職種')
        ->set('desired_location_id', $newLocation->id)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('worker.profile'));

    $profile->refresh();
    expect($profile->gender)->toBe('female');
    expect($profile->birthdate->format('Y-m-d'))->toBe('1995-08-22');
    expect($profile->skills)->toBe('新しいスキル');
    expect($profile->experiences)->toBe('新しい経験');
    expect($profile->desired_jobs)->toBe('新しい希望職種');
    expect($profile->desired_location_id)->toBe($newLocation->id);
});

test('性別が必須', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', '')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('update')
        ->assertHasErrors('gender');
});

test('生年月日が必須', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    // 年が未入力
    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', null)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('update')
        ->assertHasErrors('birthYear');

    // 月が未入力
    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', null)
        ->set('birthDay', 15)
        ->call('update')
        ->assertHasErrors('birthMonth');

    // 日が未入力
    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', null)
        ->call('update')
        ->assertHasErrors('birthDay');
});

test('無効な日付はエラーになる', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    // 2月30日（存在しない日付）
    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 2)
        ->set('birthDay', 30)
        ->call('update')
        ->assertHasErrors('birthDay');
});

test('任意項目は空でも更新できる', function () {
    $user = User::factory()->worker()->create();
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'skills' => 'スキル',
        'experiences' => '経験',
        'desired_jobs' => '希望職種',
        'desired_location_id' => Location::factory()->create()->id,
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('skills', '')
        ->set('experiences', '')
        ->set('desired_jobs', '')
        ->set('desired_location_id', null)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('worker.profile'));

    $profile->refresh();
    expect($profile->skills)->toBeNull();
    expect($profile->experiences)->toBeNull();
    expect($profile->desired_jobs)->toBeNull();
    expect($profile->desired_location_id)->toBeNull();
});

test('スキルは200文字以内', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('skills', str_repeat('あ', 201))
        ->call('update')
        ->assertHasErrors('skills');
});

test('経験は200文字以内', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('experiences', str_repeat('あ', 201))
        ->call('update')
        ->assertHasErrors('experiences');
});

test('希望職種は200文字以内', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('desired_jobs', str_repeat('あ', 201))
        ->call('update')
        ->assertHasErrors('desired_jobs');
});

test('存在しない希望勤務地はエラーになる', function () {
    $user = User::factory()->worker()->create();
    WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    Volt::actingAs($user)
        ->test('worker.edit')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('desired_location_id', 999999)
        ->call('update')
        ->assertHasErrors('desired_location_id');
});
