<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawler_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url', 300);
            $table->string('url_hash', 64)->index();
            $table->uuid('source_id')->index();
            $table->uuid('page_id')->index();
            $table->string('status', 20)
                ->default('pending')
                ->index();
            $table->jsonb('content_json')->nullable();
            $table->json('error')->nullable();
            $table->string('locale', 10)->index();
            $table->integer('attempt')->default(0);
            $table->nullableUuidMorphs('post');
            $table->datetimes();

            $table->unique(['url_hash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawler_logs');
    }
};
