<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        User::query()->each(function (User $user): void {
            $base = Str::slug(Str::before($user->email, '@'), '_');
            $base = $base !== '' ? $base : 'user';
            $username = $base;
            $suffix = 1;

            while (User::query()->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.'_'.$suffix;
                $suffix++;
            }

            $user->update(['username' => $username]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
