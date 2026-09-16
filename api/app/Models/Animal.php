<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Animal extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'animaux';

    protected $fillable = [
        'exploitation_id', 'tru_trace_id', 'espece', 'race', 'sexe',
        'date_naissance', 'statut', 'mere_id', 'pere_id', 'photo_path', 'description',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Animal $animal) {
            // Identifiant TRU TRACE : encodé en QR côté mobile (T025), scanné pour
            // retrouver l'animal même hors ligne (recherche locale par ce code).
            if (! $animal->tru_trace_id) {
                $animal->tru_trace_id = 'TRU-'.strtoupper(Str::random(10));
            }
        });
    }

    public function mere(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'mere_id');
    }

    public function pere(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'pere_id');
    }

    public function pesees(): HasMany
    {
        return $this->hasMany(Pesee::class)->orderByDesc('date_pesee');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->orderByDesc('created_at');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(DistributionAlimentation::class);
    }

    public function dernierPoids(): ?float
    {
        return $this->pesees()->value('poids_kg');
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function traitements(): HasMany
    {
        return $this->hasMany(Traitement::class);
    }

    public function consultationsVeterinaires(): HasMany
    {
        return $this->hasMany(ConsultationVeterinaire::class);
    }

    /**
     * Blocage vente sous délai d'attente (T045) : un animal ayant reçu un
     * traitement dont le délai d'attente n'est pas écoulé ne peut pas être
     * vendu (résidus médicamenteux, sécurité sanitaire de la filière).
     */
    public function peutEtreVendu(): bool
    {
        return ! $this->traitements()
            ->where('date_fin_delai_attente', '>=', now()->toDateString())
            ->exists();
    }

    public function traitementBloquant(): ?Traitement
    {
        return $this->traitements()
            ->where('date_fin_delai_attente', '>=', now()->toDateString())
            ->orderByDesc('date_fin_delai_attente')
            ->first();
    }
}
