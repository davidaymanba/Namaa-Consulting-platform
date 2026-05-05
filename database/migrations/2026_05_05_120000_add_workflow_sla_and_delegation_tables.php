<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('request_type');
            $table->string('required_role');
            $table->decimal('min_amount', 14, 2)->nullable();
            $table->decimal('max_amount', 14, 2)->nullable();
            $table->unsignedInteger('sla_hours')->default(24);
            $table->unsignedTinyInteger('total_stages')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mod ule', 'request_type', 'is_active']);
        });

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_stage')->default(1)->after('required_role');
            $table->unsignedTinyInteger('total_stages')->default(1)->after('current_stage');
            $table->unsignedInteger('sla_hours')->default(24)->after('total_stages');
            $table->timestamp('due_at')->nullable()->after('sla_hours');
            $table->timestamp('escalated_at')->nullable()->after('due_at');
            $table->json('context')->nullable()->after('reason');

            $table->index(['status', 'due_at']);
            $table->index(['module', 'record_id']);
        });

        Schema::create('user_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['delegator_user_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_delegations');

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_at']);
            $table->dropIndex(['module', 'record_id']);
            $table->dropColumn([
                'current_stage',
                'total_stages',
                'sla_hours',
                'due_at',
                'escalated_at',
                'context',
            ]);
        });

        Schema::dropIfExists('approval_flow_rules');
    }
};
