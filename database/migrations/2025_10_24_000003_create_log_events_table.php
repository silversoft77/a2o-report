<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->foreignId('event_name_id')->constrained('event_names')->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->longText('data')->nullable();
            $table->string('input_item_radio_image_card_id')->nullable();
            $table->string('input_item_modal_selection_job_type_service_titan_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_events');
    }
};
