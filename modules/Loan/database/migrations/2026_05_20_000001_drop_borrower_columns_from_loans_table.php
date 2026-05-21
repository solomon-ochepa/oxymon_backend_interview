<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'borrower_name')) {
                $table->dropColumn('borrower_name');
            }

            if (Schema::hasColumn('loans', 'borrower_email')) {
                $table->dropColumn('borrower_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (! Schema::hasColumn('loans', 'borrower_name')) {
                $table->string('borrower_name')->after('id');
            }

            if (! Schema::hasColumn('loans', 'borrower_email')) {
                $table->string('borrower_email')->after('borrower_name');
            }
        });
    }
};
