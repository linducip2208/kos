<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Blast/manual send tidak selalu punya model notifiable.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `whatsapp_logs` MODIFY `notifiable_type` VARCHAR(255) NULL, MODIFY `notifiable_id` BIGINT UNSIGNED NULL');

            return;
        }

        // Fallback SQLite (dev/test): rebuild tabel dengan data utuh.
        Schema::create('whatsapp_logs_new', function (Blueprint $table) {
            $table->id();
            $table->string('to_number', 20);
            $table->string('to_name')->nullable();
            $table->text('message');
            $table->enum('type', ['invoice', 'reminder', 'overdue', 'blast', 'welcome', 'checklist', 'maintenance', 'custom'])->default('custom');
            $table->nullableMorphs('notifiable');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->index(['status', 'type']);
        });
        DB::statement('INSERT INTO whatsapp_logs_new (id, to_number, to_name, message, type, notifiable_type, notifiable_id, status, error_message, gateway_response, sent_at, tenant_id, created_at, updated_at) SELECT id, to_number, to_name, message, type, notifiable_type, notifiable_id, status, error_message, gateway_response, sent_at, tenant_id, created_at, updated_at FROM whatsapp_logs');
        Schema::drop('whatsapp_logs');
        Schema::rename('whatsapp_logs_new', 'whatsapp_logs');
    }

    public function down(): void
    {
        $nulls = DB::table('whatsapp_logs')->whereNull('notifiable_type')->count();
        if ($nulls > 0) {
            return; // tidak aman mengembalikan NOT NULL
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `whatsapp_logs` MODIFY `notifiable_type` VARCHAR(255) NOT NULL, MODIFY `notifiable_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
