<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'crawler_translate_histories',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('content_id')->index();
                $table->unsignedBigInteger('post_id')->index();
                $table->string('lang', 5)->index();
                $table->timestamps();
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('crawler_translate_histories');
    }
};
