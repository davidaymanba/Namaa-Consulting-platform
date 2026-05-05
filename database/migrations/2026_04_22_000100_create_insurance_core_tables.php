<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['individual', 'company']);
            $table->string('national_id')->nullable()->unique();
            $table->string('contact');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact');
            $table->string('email')->nullable();
            $table->json('commission_rates');
            $table->timestamps();
        });

        Schema::create('tariff_rates', function (Blueprint $table) {
            $table->id();
            $table->string('insurance_type');
            $table->decimal('base_rate', 8, 4);
            $table->decimal('min_premium', 12, 2)->default(0);
            $table->json('rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_no')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['health', 'car', 'fire', 'marine', 'engineering', 'liability']);
            $table->enum('status', ['draft', 'active', 'renewed', 'cancelled', 'expired'])->default('draft');
            $table->decimal('premium', 14, 2)->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->decimal('loading_pct', 5, 2)->default(0);
            $table->decimal('net_premium', 14, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('issued_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->date('due_date');
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no')->unique();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'registered',
                'under_investigation',
                'surveyor_assigned',
                'report_received',
                'approved',
                'rejected',
                'paid',
            ])->default('registered');
            $table->date('incident_date');
            $table->date('report_date');
            $table->text('description')->nullable();
            $table->decimal('claimed_amount', 14, 2)->default(0);
            $table->decimal('estimated_loss', 14, 2)->default(0);
            $table->decimal('approved_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->boolean('is_large_claim')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('last_status_update_at')->nullable();
            $table->json('workflow_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('reinsurance_treaties', function (Blueprint $table) {
            $table->id();
            $table->string('reinsurer');
            $table->enum('type', ['quota_share', 'excess_of_loss']);
            $table->decimal('share_pct', 5, 2)->default(0);
            $table->decimal('retention', 14, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('reinsurance_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reinsurance_treaty_id')->constrained()->cascadeOnDelete();
            $table->decimal('policy_premium', 14, 2);
            $table->decimal('ri_share_amount', 14, 2);
            $table->decimal('claims_recovered', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('chart_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('category', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->string('currency', 3)->default('SAR');
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('debit_account');
            $table->string('credit_account');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('SAR');
            $table->string('reference')->nullable();
            $table->string('entry_type')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->foreignId('policy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('claim_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['policy_issue', 'premium_collection', 'claim_payment', 'policy_refund', 'endorsement']);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('SAR');
            $table->timestamp('transaction_date');
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->unsignedBigInteger('record_id');
            $table->string('request_type');
            $table->string('required_role');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('table_name');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('module')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_accounts');
        Schema::dropIfExists('reinsurance_distributions');
        Schema::dropIfExists('reinsurance_treaties');
        Schema::dropIfExists('claim_documents');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('policy_installments');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('tariff_rates');
        Schema::dropIfExists('brokers');
        Schema::dropIfExists('clients');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::dropIfExists('branches');
    }
};
