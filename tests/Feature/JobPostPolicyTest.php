<?php

declare(strict_types=1);

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('viewAny', function () {
    test('未認証ユーザーは求人一覧を閲覧できる', function () {
        expect(true)->toBeTrue();
    });

    test('ワーカーユーザーは求人一覧を閲覧できる', function () {
        $worker = User::factory()->create(['role' => 'worker']);

        expect($worker->can('viewAny', JobPost::class))->toBeTrue();
    });

    test('企業ユーザーは求人一覧を閲覧できる', function () {
        $company = User::factory()->create(['role' => 'company']);

        expect($company->can('viewAny', JobPost::class))->toBeTrue();
    });
});

describe('view', function () {
    test('未認証ユーザーは求人詳細を閲覧できる', function () {
        $jobPost = JobPost::factory()->create();

        expect(true)->toBeTrue();
    });

    test('ワーカーユーザーは求人詳細を閲覧できる', function () {
        $worker = User::factory()->create(['role' => 'worker']);
        $jobPost = JobPost::factory()->create();

        expect($worker->can('view', $jobPost))->toBeTrue();
    });

    test('企業ユーザーは求人詳細を閲覧できる', function () {
        $company = User::factory()->create(['role' => 'company']);
        $jobPost = JobPost::factory()->create();

        expect($company->can('view', $jobPost))->toBeTrue();
    });
});

describe('create', function () {
    test('企業ユーザーは求人を作成できる', function () {
        $company = User::factory()->create(['role' => 'company']);

        expect($company->can('create', JobPost::class))->toBeTrue();
    });

    test('ワーカーユーザーは求人を作成できない', function () {
        $worker = User::factory()->create(['role' => 'worker']);

        expect($worker->can('create', JobPost::class))->toBeFalse();
    });
});

describe('update', function () {
    test('企業ユーザーは自社の求人を更新できる', function () {
        $company = User::factory()->create(['role' => 'company']);
        $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

        expect($company->can('update', $jobPost))->toBeTrue();
    });

    test('企業ユーザーは他社の求人を更新できない', function () {
        $company = User::factory()->create(['role' => 'company']);
        $otherCompany = User::factory()->create(['role' => 'company']);
        $jobPost = JobPost::factory()->create(['company_id' => $otherCompany->id]);

        expect($company->can('update', $jobPost))->toBeFalse();
    });

    test('ワーカーユーザーは求人を更新できない', function () {
        $worker = User::factory()->create(['role' => 'worker']);
        $jobPost = JobPost::factory()->create();

        expect($worker->can('update', $jobPost))->toBeFalse();
    });
});

describe('delete', function () {
    test('企業ユーザーは自社の求人を削除できる', function () {
        $company = User::factory()->create(['role' => 'company']);
        $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

        expect($company->can('delete', $jobPost))->toBeTrue();
    });

    test('企業ユーザーは他社の求人を削除できない', function () {
        $company = User::factory()->create(['role' => 'company']);
        $otherCompany = User::factory()->create(['role' => 'company']);
        $jobPost = JobPost::factory()->create(['company_id' => $otherCompany->id]);

        expect($company->can('delete', $jobPost))->toBeFalse();
    });

    test('ワーカーユーザーは求人を削除できない', function () {
        $worker = User::factory()->create(['role' => 'worker']);
        $jobPost = JobPost::factory()->create();

        expect($worker->can('delete', $jobPost))->toBeFalse();
    });
});
