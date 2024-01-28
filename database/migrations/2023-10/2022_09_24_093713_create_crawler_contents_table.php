<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(
            'crawler_contents',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->json('components');
                $table->string('lang', 5)->default('en')->index();
                $table->unsignedBigInteger('link_id');
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('post_id')->nullable();
                $table->unsignedBigInteger('resource_id')->nullable()->index();
                $table->string('status')->default('pending');
                $table->timestamps();

                $table->foreign('page_id')
                    ->references('id')
                    ->on('crawler_pages')
                    ->onDelete('cascade');

                $table->foreign('link_id')
                    ->references('id')
                    ->on('crawler_links')
                    ->onDelete('cascade');

                $table->foreign('post_id')
                    ->references('id')
                    ->on('posts')
                    ->onDelete('set null');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('crawler_contents');
    }
};
