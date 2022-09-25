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
                $table->string('url', 200);
                $table->string('url_hash', 40)->unique();
                $table->string('url_with_page', 200)->nullable();
                $table->string('post_type', 50)->default('posts');
                $table->unsignedBigInteger('website_id')->index();
                $table->text('error')->nullable();
                $table->json('category_ids')->nullable();
                $table->integer('next_page')->default(1);
                $table->boolean('active')->default(1);
                $table->timestamp('crawler_date')->default('2020-01-01 00:00:00');
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
