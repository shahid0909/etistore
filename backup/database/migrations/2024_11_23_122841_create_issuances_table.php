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
        Schema::create('issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade'); // Employee
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade'); // Issuance Department
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Product Name
            $table->integer('quantity'); // Issued Quantity
            $table->foreignId('issued_by')->constrained('admins')->onDelete('cascade'); // Auth User ID
            $table->text('description')->nullable(); // Description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuances');
    }
};
