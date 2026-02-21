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
        Schema::create('crawler_taxonomies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crawler_page_id')->index()
                ->constrained('crawler_pages')
                ->onDelete('cascade');
            $table->unsignedBigInteger('taxonomy_id')->index();
            $table->string('taxonomy_type', 100)->index();
            $table->unique(['crawler_page_id', 'taxonomy_id', 'taxonomy_type'], 'crawler_taxonomies_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawler_taxonomies');
    }
};
