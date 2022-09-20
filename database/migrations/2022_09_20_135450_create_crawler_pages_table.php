<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create(
            'crawler_pages',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('list_url', 200);
                $table->string('list_url_page', 200)->nullable();
                $table->string('element_item');
                $table->unsignedBigInteger('website_id')->index();
                $table->json('category_ids');
                $table->integer('next_page')->default(1);
                $table->string('status', 10)->default('active');
                $table->timestamp('crawler_date')->nullable();
                $table->timestamps();

                $table->foreign('website_id')
                    ->references('id')
                    ->on('crawler_websites')
                    ->onDelete('cascade');
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('crawler_pages');
    }
};
