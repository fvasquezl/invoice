<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $existing = DB::table('company_user')->get();

        Schema::drop('company_user');

        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unique(['company_id', 'user_id']);
            $table->timestamps();
        });

        foreach ($existing as $record) {
            DB::table('company_user')->insertOrIgnore((array) $record);
        }
    }

    public function down(): void
    {
        $existing = DB::table('company_user')->get();

        Schema::drop('company_user');

        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        foreach ($existing as $record) {
            DB::table('company_user')->insert((array) $record);
        }
    }
};
