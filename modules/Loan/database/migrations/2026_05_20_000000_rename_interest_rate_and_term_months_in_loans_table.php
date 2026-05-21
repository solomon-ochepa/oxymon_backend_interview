<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'interest_rate') and ! Schema::hasColumn('loans', 'interest')) {
                $table->renameColumn('interest_rate', 'interest');
            }

            if (Schema::hasColumn('loans', 'term_months') and ! Schema::hasColumn('loans', 'term')) {
                $table->renameColumn('term_months', 'term');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (! Schema::hasColumn('loans', 'interest_rate') and Schema::hasColumn('loans', 'interest')) {
                $table->renameColumn('interest', 'interest_rate');
            }

            if (! Schema::hasColumn('loans', 'term_months') and Schema::hasColumn('loans', 'term')) {
                $table->renameColumn('term', 'term_months');
            }
        });
    }
};
