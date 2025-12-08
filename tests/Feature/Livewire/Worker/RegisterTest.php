<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

test('未認証ユーザーはワーカー登録画面を表示できる', function () {
    $response = $this->get(route('worker.register'));
    $response->assertSuccessful();
    $response->assertSee('ワーカー登録');
});

test('認証済みユーザーもワーカー登録画面を表示できる', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('worker.register'));
    $response->assertSuccessful();
});

test('有効なデータでワーカー登録ができる', function () {
    $prefecture = Location::factory()->prefectureOnly()->create([
        'prefecture' => '東京都',
        'city' => null,
        'code' => '130001',
    ]);

    $city = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
        'code' => '131016',
    ]);

    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('skills', '接客、英語（TOEIC750点）')
        ->set('experiences', '飲食店勤務 5年')
        ->set('desired_jobs', '接客・サービス')
        ->set('selectedPrefecture', $prefecture->id)
        ->set('desired_location_id', $city->id)
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('worker.profile'));

    // ユーザーが作成されたことを確認
    $user = User::query()->where('email', 'sato.taro@gmail.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('佐藤太郎');
    expect($user->role)->toBe('worker');
    expect($user->email_verified_at)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();

    // プロフィールが作成されたことを確認
    $profile = WorkerProfile::query()->where('user_id', $user->id)->first();
    expect($profile)->not->toBeNull();
    expect($profile->gender)->toBe('male');
    expect($profile->birthdate->format('Y-m-d'))->toBe('1990-05-15');
    expect($profile->skills)->toBe('接客、英語（TOEIC750点）');
    expect($profile->experiences)->toBe('飲食店勤務 5年');
    expect($profile->desired_jobs)->toBe('接客・サービス');
    expect($profile->desired_location_id)->toBe($city->id);
});

test('任意項目を空でワーカー登録ができる', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('skills', '')
        ->set('experiences', '')
        ->set('desired_jobs', '')
        ->set('desired_location_id', null)
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('worker.profile'));

    // プロフィールが作成されたことを確認
    $user = User::query()->where('email', 'sato.taro@gmail.com')->first();
    $profile = WorkerProfile::query()->where('user_id', $user->id)->first();
    expect($profile->skills)->toBeNull();
    expect($profile->experiences)->toBeNull();
    expect($profile->desired_jobs)->toBeNull();
    expect($profile->desired_location_id)->toBeNull();
});

test('氏名が必須である', function () {
    Volt::test('worker.register')
        ->set('name', '')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['name' => 'required']);
});

test('氏名は255文字以内である', function () {
    Volt::test('worker.register')
        ->set('name', str_repeat('あ', 256))
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['name' => 'max']);
});

test('メールアドレスが必須である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', '')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['email' => 'required']);
});

test('メールアドレスは有効な形式である必要がある', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'invalid-email')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['email' => 'email']);
});

test('メールアドレスはユニークである必要がある', function () {
    User::factory()->create(['email' => 'sato.taro@gmail.com']);

    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

test('パスワードが必須である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['password' => 'required']);
});

test('パスワードは8文字以上である必要がある', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'pass123')
        ->set('password_confirmation', 'pass123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['password' => 'min']);
});

test('パスワード確認が一致する必要がある', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'different123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('性別が必須である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', '')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['gender' => 'required']);
});

test('性別は指定された値である必要がある', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'invalid')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['gender' => 'in']);
});

test('生年月日（年）が必須である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', null)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['birthYear' => 'required']);
});

test('生年月日（月）が必須である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', null)
        ->set('birthDay', 15)
        ->call('register')
        ->assertHasErrors(['birthMonth' => 'required']);
});

test('生年月日（日）が必須である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', null)
        ->call('register')
        ->assertHasErrors(['birthDay' => 'required']);
});

test('スキルは200文字以内である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('skills', str_repeat('あ', 201))
        ->call('register')
        ->assertHasErrors(['skills' => 'max']);
});

test('経験は200文字以内である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('experiences', str_repeat('あ', 201))
        ->call('register')
        ->assertHasErrors(['experiences' => 'max']);
});

test('希望職種は200文字以内である', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('desired_jobs', str_repeat('あ', 201))
        ->call('register')
        ->assertHasErrors(['desired_jobs' => 'max']);
});

test('希望勤務地は存在する必要がある', function () {
    Volt::test('worker.register')
        ->set('name', '佐藤太郎')
        ->set('email', 'sato.taro@gmail.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('desired_location_id', 999999)
        ->call('register')
        ->assertHasErrors(['desired_location_id' => 'exists']);
});
