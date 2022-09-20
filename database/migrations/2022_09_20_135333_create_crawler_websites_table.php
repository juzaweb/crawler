<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'crawler_websites',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('domain', 100);
                $table->boolean('has_ssl')->default(1);
                $table->boolean('active')->default(1);
                $table->unsignedBigInteger('template_id')->index();
                $table->timestamps();

                $table->foreign('template_id')
                    ->references('id')
                    ->on('crawler_templates')
                    ->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawler_websites');
    }
};
