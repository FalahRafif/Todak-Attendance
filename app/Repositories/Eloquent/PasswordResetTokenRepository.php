<?php

namespace App\Repositories\Eloquent;

use App\Models\PasswordResetToken;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;

class PasswordResetTokenRepository extends BaseRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(PasswordResetToken $model)
    {
        parent::__construct($model);
    }
}
