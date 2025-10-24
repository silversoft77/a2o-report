<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_service_titan_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->bigInteger('service_titan_job_id');
            $table->bigInteger('business_unit_id')->nullable();
            $table->bigInteger('job_type_id')->nullable();
            $table->json('tag_type_ids')->nullable();
            $table->bigInteger('technician_id')->nullable();
            $table->bigInteger('campaign_id')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->text('summary')->nullable();
            $table->boolean('chargebee')->default(0);
            $table->longText('web_session_data')->nullable();
            $table->boolean('attributions_sent')->default(0);
            $table->string('job_status')->nullable();
            $table->boolean('s2f')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('referral_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_service_titan_jobs');
    }
};
