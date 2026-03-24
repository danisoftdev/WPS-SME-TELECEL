<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class PasswordResetRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'code_encrypted',
        'status',
        'expires_at',
        'used_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setCodePlain(string $code): void
    {
        $this->code_encrypted = Crypt::encryptString($code);
    }

    public function getCodePlain(): ?string
    {
        try {
            return Crypt::decryptString($this->code_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }
}

