<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    /**
     * 応募一覧の閲覧権限
     * 企業: 自社求人への応募のみ閲覧可能
     * ワーカー: 自分の応募のみ閲覧可能
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'company' || $user->role === 'worker';
    }

    /**
     * 応募詳細の閲覧権限
     * 企業: 自社求人への応募のみ閲覧可能
     * ワーカー: 自分の応募のみ閲覧可能
     */
    public function view(User $user, JobApplication $jobApplication): bool
    {
        if ($user->role === 'company') {
            return $jobApplication->jobPost->company_id === $user->id;
        }

        if ($user->role === 'worker') {
            return $jobApplication->worker_id === $user->id;
        }

        return false;
    }

    /**
     * 応募作成権限
     * ワーカーのみ応募可能
     */
    public function create(User $user): bool
    {
        return $user->role === 'worker';
    }

    /**
     * 応募更新権限（採用判定）
     * 企業: 自社求人への応募のみ承認・不承認可能
     * ワーカー: 自分の応募のみ辞退可能
     */
    public function update(User $user, JobApplication $jobApplication): bool
    {
        if ($user->role === 'company') {
            return $jobApplication->jobPost->company_id === $user->id;
        }

        if ($user->role === 'worker') {
            return $jobApplication->worker_id === $user->id;
        }

        return false;
    }

    /**
     * 応募削除権限
     * 削除は許可しない（ステータス更新で対応）
     */
    public function delete(User $user, JobApplication $jobApplication): bool
    {
        return false;
    }
}
