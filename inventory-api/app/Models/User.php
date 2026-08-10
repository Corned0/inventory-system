<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'username',
        'employee_id',
        'password',
        'is_active',
        'name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /* public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->relationLoaded('permissions')
            ? $this->permissions->contains('name', $permission)
            : $this->permissions()
                ->where('name', $permission)
                ->exists();
    }

    public function givePermission(string $permission): void
    {
        $permission = Permission::where('name', $permission)
            ->firstOrFail();

        $this->permissions()
            ->syncWithoutDetaching([$permission->id]);
    }

    public function revokePermission(string $permission): void
    {
        $permission = Permission::where('name', $permission)
            ->first();

        if ($permission === null) {
            return;
        }

        $this->permissions()->detach($permission->id);
    } */
}
