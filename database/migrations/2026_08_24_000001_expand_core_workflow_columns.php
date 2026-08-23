<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ekspansi kolom inti untuk workflow profesional:
 * - users.role  : enum → string (10 role baru)
 * - rooms       : status string 9 nilai + blocked_reason + index
 * - leases      : status string + approval/signature/renewal columns + index
 * - invoices    : created_by + index komposit
 * - deposits    : property_id + status string kanonik
 * - maintenance : status lifecycle lengkap + SLA/vendor/material/rating
 * - booking     : CRM funnel columns (status lama tetap utk backward compat)
 * - expenses    : approval workflow
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users ────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('property_manager')->change();
        });
        DB::table('users')->where('role', 'staff')->update(['role' => 'property_manager']);
        DB::table('users')->where('role', 'viewer')->update(['role' => 'auditor']);

        // ── rooms ────────────────────────────────────────────────────────
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('status', 30)->default('available')->index()->change();
            $table->text('blocked_reason')->nullable()->after('notes');
        });

        // ── leases ───────────────────────────────────────────────────────
        Schema::table('leases', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->change();
            $table->foreignId('renewed_from_lease_id')->nullable()->after('lease_number')
                ->constrained('leases')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('tenant_signed_at')->nullable()->after('approved_at');
            $table->timestamp('owner_signed_at')->nullable()->after('tenant_signed_at');
            $table->date('notice_given_at')->nullable()->after('terminated_at');
            $table->date('moved_out_at')->nullable()->after('notice_given_at');
            $table->index(['end_date']);
            $table->index(['occupant_id', 'status']);
        });

        // ── invoices ─────────────────────────────────────────────────────
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('notes')
                ->constrained('users')->nullOnDelete();
            $table->index(['lease_id', 'status']);
            $table->index(['paid_at']);
        });

        // ── deposits ─────────────────────────────────────────────────────
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->after('lease_id')
                ->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pending')->index()->change();
        });
        DB::table('deposits')->where('status', 'refunded_partial')->update(['status' => 'partially_used']);
        DB::table('deposits')->where('status', 'refunded_full')->update(['status' => 'refunded']);

        // ── maintenance_requests ─────────────────────────────────────────
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('status', 30)->default('open')->index()->change();
            $table->string('category', 40)->default('general')->after('title');
            $table->foreignId('vendor_id')->nullable()->after('assigned_to')
                ->constrained()->nullOnDelete();
            $table->unsignedInteger('sla_hours')->nullable()->after('vendor_id');
            $table->timestamp('sla_due_at')->nullable()->after('sla_hours');
            $table->json('materials')->nullable()->after('estimated_cost');
            $table->json('before_photos')->nullable()->after('photos');
            $table->json('after_photos')->nullable()->after('before_photos');
            $table->unsignedTinyInteger('tenant_rating')->nullable()->after('resolution_notes');
            $table->text('tenant_feedback')->nullable()->after('tenant_rating');
            $table->timestamp('completed_at')->nullable()->after('tenant_feedback');
            $table->timestamp('closed_at')->nullable()->after('completed_at');
            $table->text('internal_notes')->nullable()->after('closed_at');
        });
        DB::table('maintenance_requests')->where('status', 'resolved')->update(['status' => 'completed']);

        // ── booking_requests (CRM funnel; kolom lama tetap) ──────────────
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->string('stage', 30)->default('new_lead')->index()->after('status');
            $table->string('source', 30)->default('website')->after('stage');
            $table->string('campaign', 100)->nullable()->after('source');
            $table->foreignId('assigned_to')->nullable()->after('campaign')
                ->constrained('users')->nullOnDelete();
            $table->date('follow_up_date')->nullable()->after('assigned_to');
            $table->decimal('budget', 15, 2)->nullable()->after('follow_up_date');
            $table->text('lost_reason')->nullable()->after('budget');
            $table->timestamp('converted_at')->nullable()->after('lost_reason');
            $table->index(['property_id', 'stage']);
        });

        // ── expenses ─────────────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->index()->after('amount');
            $table->foreignId('approved_by')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('created_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'created_by']);
        });

        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'stage']);
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn([
                'stage', 'source', 'campaign', 'follow_up_date',
                'budget', 'lost_reason', 'converted_at',
            ]);
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn([
                'category', 'sla_hours', 'sla_due_at', 'materials', 'before_photos',
                'after_photos', 'tenant_rating', 'tenant_feedback', 'completed_at',
                'closed_at', 'internal_notes',
            ]);
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropIndex(['lease_id', 'status']);
            $table->dropIndex(['paid_at']);
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('renewed_from_lease_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'approved_at', 'tenant_signed_at', 'owner_signed_at',
                'notice_given_at', 'moved_out_at',
            ]);
            $table->dropIndex(['end_date']);
            $table->dropIndex(['occupant_id', 'status']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['blocked_reason']);
        });
    }
};
