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
            $table->uuid('page_id')->index();
            $table->uuid('taxonomy_id')->index();

            $table->foreign('page_id')
                ->references('id')
                ->on('crawler_pages')
                ->onDelete('cascade');

            $table->primary(['page_id', 'taxonomy_id']);
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
