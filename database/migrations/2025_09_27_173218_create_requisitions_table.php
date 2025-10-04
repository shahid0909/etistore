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
        Schema::create('requisitions', function (Blueprint $table) {
             $table->id();
            $table->unsignedBigInteger('staff_id');       // requester
            $table->unsignedBigInteger('department_id');  // auto from staff
            $table->unsignedBigInteger('designation_id'); // auto from staff
            $table->string('rationale');                  // purpose
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->unsignedBigInteger('approver_id')->nullable(); // (next step)
            $table->timestamp('approved_at')->nullable();          // (next step)
            $table->text('remarks')->nullable();                   // (next step)
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
