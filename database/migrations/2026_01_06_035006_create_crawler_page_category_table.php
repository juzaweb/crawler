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
        Schema::create('crawler_page_category', function (Blueprint $table) {
            $table->uuidMorphs('category');
            $table->uuid('crawler_page_id');

            $table->primary(['category_id', 'category_type', 'crawler_page_id']);
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
    }
};
