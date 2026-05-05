<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->json('wizard_state')->nullable()->after('workflow_notes');
            $table->enum('coverage_status', ['pending', 'covered', 'not_covered'])->default('pending')->after('wizard_state');
            $table->timestamp('coverage_checked_at')->nullable()->after('coverage_status');
            $table->unsignedTinyInteger('fraud_score')->default(0)->after('coverage_checked_at');
            $table->json('fraud_flags')->nullable()->after('fraud_score');

            $table->index(['coverage_status', 'fraud_score']);
        });

        Schema::create('claim_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_automation_rules');

        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex(['coverage_status', 'fraud_score']);
            $table->dropColumn([
                'wizard_state',
                'coverage_status',
                'coverage_checked_at',
                'fraud_score',
                'fraud_flags',
            ]);
        });
    }
};
