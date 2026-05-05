<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policy_installments', function (Blueprint $table) {
            $table->unsignedTinyInteger('reminder_count')->default(0)->after('status');
            $table->timestamp('last_reminded_at')->nullable()->after('reminder_count');
            $table->unsignedSmallInteger('overdue_days')->default(0)->after('last_reminded_at');
            $table->index(['status', 'due_date']);
            $table->index(['due_date', 'status']);
        });

        Schema::create('payment_reminder_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_installment_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'in_app'])->default('email');
            $table->unsignedSmallInteger('days_offset');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_for']);
            $table->index(['policy_installment_id', 'days_offset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
        Schema::dropIfExists('payment_reminder_rules');

        Schema::table('policy_installments', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropIndex(['due_date', 'status']);
            $table->dropColumn(['reminder_count', 'last_reminded_at', 'overdue_days']);
        });
    }
};
