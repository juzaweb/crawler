<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create(
            'crawler_links',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('url', 300);
                $table->string('url_hash', 40)->unique();
                $table->unsignedBigInteger('website_id')->index();
                $table->unsignedBigInteger('page_id')->nullable()->index();
                $table->json('category_ids')->nullable();
                $table->string('status', 10)->default('active');
                $table->json('error')->nullable();
                $table->boolean('auto_craw')->default(0);
                $table->boolean('active')->default(1);
                $table->timestamps();

                $table->foreign('website_id')
                    ->references('id')
                    ->on('crawler_websites')
                    ->onDelete('cascade');

                $table->foreign('page_id')
                    ->references('id')
                    ->on('crawler_pages')
                    ->onDelete('set null');
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('crawler_links');
    }
};
