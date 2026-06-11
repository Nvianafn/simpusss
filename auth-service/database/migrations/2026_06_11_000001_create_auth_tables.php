<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->string('route');
            $table->string('icon')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->nullOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('nim')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('role_menus', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->primary(['role_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menus');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
