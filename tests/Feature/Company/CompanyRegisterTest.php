<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

test('未認証ユーザーは企業登録画面を表示できる', function () {
    $response = $this->get(route('company.register'));
    $response->assertSuccessful();
    $response->assertSee('企業登録');
});

test('認証済みユーザーも企業登録画面を表示できる', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('company.register'));
    $response->assertSuccessful();
});

test('有効なデータで企業登録ができる', function () {
    $prefecture = Location::factory()->prefectureOnly()->create([
        'prefecture' => '北海道',
        'city' => null,
        'code' => '010006',
    ]);

    $city = Location::factory()->create([
        'prefecture' => '北海道',
        'city' => '札幌市',
        'code' => '011002',
    ]);

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('selectedPrefecture', $prefecture->id)
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('company.profile'));

    // ユーザーが作成されたことを確認
    $user = User::query()->where('email', 'test@company.co.jp')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('株式会社テスト');
    expect($user->role)->toBe('company');
    expect($user->email_verified_at)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();

    // プロフィールが作成されたことを確認
    $profile = CompanyProfile::query()->where('user_id', $user->id)->first();
    expect($profile)->not->toBeNull();
    expect($profile->location_id)->toBe($city->id);
    expect($profile->address)->toBe('中央区北1条西5丁目');
    expect($profile->representative)->toBe('山田太郎');
    expect($profile->phone_number)->toBe('011-123-4567');
});

test('企業名が必須である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['name' => 'required']);
});

test('企業名は255文字以内である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', str_repeat('あ', 256))
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['name' => 'max']);
});

test('メールアドレスが必須である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', '')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['email' => 'required']);
});

test('メールアドレスは有効な形式である必要がある', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'invalid-email')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['email' => 'email']);
});

test('メールアドレスはユニークである必要がある', function () {
    User::factory()->create(['email' => 'test@company.co.jp']);
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

test('パスワードが必須である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['password' => 'required']);
});

test('パスワードは8文字以上である必要がある', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'pass123')
        ->set('password_confirmation', 'pass123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['password' => 'min']);
});

test('パスワード確認が一致する必要がある', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'different123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('所在地が必須である', function () {
    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', null)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['location_id' => 'required']);
});

test('所在地は存在する必要がある', function () {
    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', 999999)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['location_id' => 'exists']);
});

test('所在地住所が必須である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '')
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['address' => 'required']);
});

test('所在地住所は200文字以内である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', str_repeat('あ', 201))
        ->set('representative', '山田太郎')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['address' => 'max']);
});

test('担当者名が必須である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '')
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['representative' => 'required']);
});

test('担当者名は50文字以内である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', str_repeat('あ', 51))
        ->set('phone_number', '011-123-4567')
        ->call('register')
        ->assertHasErrors(['representative' => 'max']);
});

test('担当者連絡先が必須である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', '')
        ->call('register')
        ->assertHasErrors(['phone_number' => 'required']);
});

test('担当者連絡先は30文字以内である', function () {
    $city = Location::factory()->create();

    Volt::test('company.register')
        ->set('name', '株式会社テスト')
        ->set('email', 'test@company.co.jp')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('location_id', $city->id)
        ->set('address', '中央区北1条西5丁目')
        ->set('representative', '山田太郎')
        ->set('phone_number', str_repeat('0', 31))
        ->call('register')
        ->assertHasErrors(['phone_number' => 'max']);
});
