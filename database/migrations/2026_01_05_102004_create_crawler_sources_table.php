<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $connection = 'mysql';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('mysql')->hasTable('crawler_sources')) {
            return;
        }

        Schema::connection('mysql')->create('crawler_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('domain', 100)->unique();
            $table->boolean('active')->default(1)->index();
            $table->string('data_type', 50)->index();
            $table->string('link_element')->nullable();
            $table->string('link_regex')->nullable();
            $table->json('components');
            $table->json('removes')->nullable();
            $table->websiteId();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('crawler_sources');
    }
};
