<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'exploitation_id',
        'role_id',
        'two_factor_enabled',
        'actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'actif' => 'boolean',
        'pin_locked_until' => 'datetime',
    ];

    public function exploitation(): BelongsTo
    {
        return $this->belongsTo(Exploitation::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string ...$slugs): bool
    {
        return $this->role && in_array($this->role->slug, $slugs, true);
    }

    /**
     * IDs des exploitations visibles par cet utilisateur : la sienne, et si
     * gestionnaire de coopérative, celles des membres (US6 — consultation
     * individuelle soumise au consentement explicite du membre, vérifié en
     * amont au niveau de la policy/endpoint, pas ici).
     */
    public function exploitationsAccessibles(): array
    {
        if (! $this->exploitation_id) {
            return [];
        }

        if ($this->hasRole(Role::GESTIONNAIRE_COOPERATIVE)) {
            $membreIds = Exploitation::withoutGlobalScopes()
                ->where('cooperative_id', $this->exploitation_id)
                ->pluck('id')
                ->all();

            return array_merge([$this->exploitation_id], $membreIds);
        }

        return [$this->exploitation_id];
    }

    public function definirPin(string $pin): void
    {
        $this->pin_hash = Hash::make($pin);
        $this->pin_attempts = 0;
        $this->pin_locked_until = null;
        $this->save();
    }

    public function verifierPin(string $pin): bool
    {
        return $this->pin_hash && Hash::check($pin, $this->pin_hash);
    }
}
