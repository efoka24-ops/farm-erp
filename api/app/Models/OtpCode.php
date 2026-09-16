<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class OtpCode extends Model
{
    protected $fillable = ['user_id', 'code_hash', 'expire_at', 'consomme_at', 'tentatives'];

    protected $casts = [
        'expire_at' => 'datetime',
        'consomme_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generatePour(User $user): array
    {
        $code = (string) random_int(100000, 999999);

        $otp = self::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expire_at' => now()->addMinutes(10),
        ]);

        return [$otp, $code];
    }

    public function estValide(string $code): bool
    {
        return ! $this->consomme_at
            && $this->expire_at->isFuture()
            && $this->tentatives < 5
            && Hash::check($code, $this->code_hash);
    }
}
