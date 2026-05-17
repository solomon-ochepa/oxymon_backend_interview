<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('borrower_name');
            $table->string('borrower_email');
            $table->decimal('amount', 12, 2);
            $table->decimal('interest_rate', 5, 2); // annual percentage, e.g. 12.50
            $table->unsignedSmallInteger('term_months');
            $table->enum('status', ['pending', 'approved', 'active', 'paid', 'rejected'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
