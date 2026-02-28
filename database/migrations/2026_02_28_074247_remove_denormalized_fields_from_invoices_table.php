<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'company_address', 'company_email', 'company_phone', 'company_logo',
                'client_name', 'client_address', 'client_email', 'client_phone',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('client_name')->nullable();
            $table->text('client_address')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
        });
    }
};
