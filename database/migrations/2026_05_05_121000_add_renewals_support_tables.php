<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->foreignId('renewed_from_policy_id')->nullable()->after('archived_at')->constrained('policies')->nullOnDelete();
            $table->timestamp('renewal_notified_at')->nullable()->after('renewed_from_policy_id');
            $table->timestamp('renewal_quoted_at')->nullable()->after('renewal_notified_at');
            $table->index(['status', 'end_date']);
            $table->index(['renewed_from_policy_id']);
        });

        Schema::create('renewal_rules', function (Blueprint $table) {
            $table->id();
            $table->string('product_type');
            $table->unsignedTinyInteger('notify_days_before')->default(30);
            $table->boolean('auto_quote')->default(true);
            $table->boolean('bulk_eligible')->default(true);
            $table->decimal('risk_loading_pct', 5, 2)->default(0);
            $table->decimal('claims_discount_pct', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_type']);
        });

        Schema::create('renewal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'in_app'])->default('email');
            $table->unsignedTinyInteger('days_before');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_for']);
            $table->index(['policy_id', 'days_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_notifications');
        Schema::dropIfExists('renewal_rules');

        Schema::table('policies', function (Blueprint $table) {
            $table->dropIndex(['status', 'end_date']);
            $table->dropIndex(['renewed_from_policy_id']);
            $table->dropConstrainedForeignId('renewed_from_policy_id');
            $table->dropColumn(['renewal_notified_at', 'renewal_quoted_at']);
        });
    }
};
