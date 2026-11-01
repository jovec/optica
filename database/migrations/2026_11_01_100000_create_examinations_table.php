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
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('examined_at');

            foreach (['od', 'os'] as $eye) {
                $table->decimal("{$eye}_sphere", 5, 2)->nullable();
                $table->decimal("{$eye}_cylinder", 5, 2)->nullable();
                $table->unsignedSmallInteger("{$eye}_axis")->nullable();
                $table->decimal("{$eye}_addition", 4, 2)->nullable();
            }

            $table->decimal('pd', 4, 1)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
