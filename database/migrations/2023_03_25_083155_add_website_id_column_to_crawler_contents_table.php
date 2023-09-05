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
    public function up(): void
    {
        Schema::table(
            'crawler_contents',
            function (Blueprint $table) {
                $table->unsignedBigInteger('website_id')->nullable();
                $table->foreign('website_id')
                    ->references('id')
                    ->on('crawler_websites')
                    ->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(
            'crawler_contents',
            function (Blueprint $table) {
                $table->dropForeign('website_id');
                $table->dropColumn('website_id');
            }
        );
    }
};
