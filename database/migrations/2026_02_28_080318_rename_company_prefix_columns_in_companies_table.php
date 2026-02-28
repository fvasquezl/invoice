<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('company_name', 'name');
            $table->renameColumn('company_address', 'address');
            $table->renameColumn('company_email', 'email');
            $table->renameColumn('company_phone', 'phone');
            $table->renameColumn('company_logo', 'logo');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('name', 'company_name');
            $table->renameColumn('address', 'company_address');
            $table->renameColumn('email', 'company_email');
            $table->renameColumn('phone', 'company_phone');
            $table->renameColumn('logo', 'company_logo');
        });
    }
};
