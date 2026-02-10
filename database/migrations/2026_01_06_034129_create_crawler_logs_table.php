<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('mongodb')->hasTable('crawler_logs')) {
            return;
        }

        Schema::connection('mongodb')->create('crawler_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url', 300);
            $table->string('url_hash', 64)->index();
            $table->uuid('source_id')->index();
            $table->uuid('page_id')->index();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'retrying'])
                ->default('pending')
                ->index();
            $table->jsonb('content_json')->nullable();
            $table->json('error')->nullable();
            $table->boolean('active')->default(1)->index();
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
        Schema::connection('mongodb')->dropIfExists('crawler_logs');
    }
};
