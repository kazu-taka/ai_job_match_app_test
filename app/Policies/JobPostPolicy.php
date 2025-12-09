<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;

class JobPostPolicy
{
    /**
     * 求人一覧の閲覧権限
     * 誰でも閲覧可能
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * 求人詳細の閲覧権限
     * 誰でも閲覧可能
     */
    public function view(?User $user, JobPost $jobPost): bool
    {
        return true;
    }

    /**
     * 求人作成権限
     * 企業ユーザーのみ作成可能
     */
    public function create(User $user): bool
    {
        return $user->role === 'company';
    }

    /**
     * 求人更新権限
     * 自社の求人のみ更新可能
     */
    public function update(User $user, JobPost $jobPost): bool
    {
        return $user->role === 'company' && $user->id === $jobPost->company_id;
    }

    /**
     * 求人削除権限
     * 自社の求人のみ削除可能
     */
    public function delete(User $user, JobPost $jobPost): bool
    {
        return $user->role === 'company' && $user->id === $jobPost->company_id;
    }
}
