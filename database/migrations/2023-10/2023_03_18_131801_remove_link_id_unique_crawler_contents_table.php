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
                $table->boolean('is_source')->default(false)->index();
                /*$table->dropForeign('crawler_contents_link_id_foreign');
                $table->dropUnique('crawler_contents_link_id_unique');
                $table->foreign('link_id')
                    ->references('id')
                    ->on('crawler_links')
                    ->onDelete('cascade');*/
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
                $table->dropColumn('is_source');
            }
        );
    }
};
