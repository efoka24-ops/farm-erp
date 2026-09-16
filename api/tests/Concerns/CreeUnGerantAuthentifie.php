<?php

namespace Tests\Concerns;

use App\Models\Exploitation;
use App\Models\Role;
use App\Models\User;

trait CreeUnGerantAuthentifie
{
    protected function gerantAuthentifie(?Exploitation $exploitation = null): User
    {
        $exploitation ??= Exploitation::create(['nom' => 'Exploitation Test', 'type' => 'individuelle']);

        $user = User::factory()->create([
            'exploitation_id' => $exploitation->id,
            'role_id' => Role::where('slug', Role::GERANT)->value('id'),
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
