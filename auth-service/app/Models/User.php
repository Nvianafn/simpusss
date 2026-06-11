<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'nim', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_active' => 'boolean', 'password' => 'hashed'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
