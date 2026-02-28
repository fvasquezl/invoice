<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not allow DROP COLUMN when the column has a FK constraint,
        // even with foreign_keys disabled. Recreate the table without company_id.
        DB::statement('PRAGMA foreign_keys = OFF');

        // Save existing company_id mappings before dropping the column.
        DB::statement('CREATE TEMP TABLE _client_company_backup AS
            SELECT id AS client_id, company_id FROM clients WHERE company_id IS NOT NULL');

        DB::statement('CREATE TABLE clients_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR NOT NULL,
            address TEXT,
            email VARCHAR,
            phone VARCHAR,
            created_at DATETIME,
            updated_at DATETIME
        )');

        DB::statement('INSERT INTO clients_new (id, name, address, email, phone, created_at, updated_at)
            SELECT id, name, address, email, phone, created_at, updated_at FROM clients');

        DB::statement('DROP TABLE clients');
        DB::statement('ALTER TABLE clients_new RENAME TO clients');

        Schema::create('client_company', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['client_id', 'company_id']);
        });

        // Migrate saved mappings into the new pivot table.
        DB::statement("INSERT OR IGNORE INTO client_company (client_id, company_id, created_at, updated_at)
            SELECT client_id, company_id, datetime('now'), datetime('now')
            FROM _client_company_backup");

        DB::statement('DROP TABLE _client_company_backup');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::dropIfExists('client_company');

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
