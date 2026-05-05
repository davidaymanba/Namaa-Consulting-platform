<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reinsurance_treaties', function (Blueprint $table) {
            $table->decimal('limit_amount', 14, 2)->nullable()->after('retention');
            $table->json('rules')->nullable()->after('limit_amount');
            $table->timestamp('last_bordereaux_sent_at')->nullable()->after('is_active');
        });

        Schema::create('reinsurance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reinsurance_treaty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reinsurance_distribution_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['retention_breached', 'limit_breached', 'bordereaux_pending'])->default('retention_breached');
            $table->string('message');
            $table->enum('status', ['pending', 'resolved'])->default('pending');
            $table->json('context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('bordereaux_batches', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->unsignedInteger('total_records')->default(0);
            $table->decimal('total_ri_share', 14, 2)->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereaux_batches');
        Schema::dropIfExists('reinsurance_alerts');

        Schema::table('reinsurance_treaties', function (Blueprint $table) {
            $table->dropColumn(['limit_amount', 'rules', 'last_bordereaux_sent_at']);
        });
    }
};
