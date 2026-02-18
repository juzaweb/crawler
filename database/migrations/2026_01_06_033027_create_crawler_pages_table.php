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
        Schema::create('crawler_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_id')->index()
                ->constrained('crawler_sources')
                ->onDelete('cascade');
            $table->string('url', 200);
            $table->string('url_hash', 40)->unique();
            $table->string('url_with_page', 200)->nullable();
            $table->integer('next_page')->default(1);
            $table->boolean('active')->default(1)->index();
            $table->datetime('crawled_at')->default('2020-01-01 00:00:00')->index();
            $table->json('error')->nullable();
            $table->string('locale', 10)->index();
            $table->datetimes();
        });

        Schema::create('crawler_page_category', function (Blueprint $table) {
            $table->uuid('crawler_category_id');
            $table->unsignedBigInteger('crawler_page_id');

            $table->primary(['crawler_category_id', 'crawler_page_id']);
            $table->foreign('crawler_page_id')
                ->references('id')
                ->on('crawler_pages')
                ->onDelete('cascade');
            $table->foreign('crawler_category_id')
                ->references('id')
                ->on('crawler_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawler_page_category');
        Schema::dropIfExists('crawler_pages');
    }
};
