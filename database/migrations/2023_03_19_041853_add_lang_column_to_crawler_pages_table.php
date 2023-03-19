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
            'crawler_websites',
            function (Blueprint $table) {
                $table->json('translate_replaces')->nullable();
            }
        );

        Schema::table(
            'crawler_pages',
            function (Blueprint $table) {
                $table->string('lang', 5)->default('en')->index();
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
            'crawler_websites',
            function (Blueprint $table) {
                $table->dropColumn('translate_replaces');
            }
        );

        Schema::table(
            'crawler_pages',
            function (Blueprint $table) {
                $table->dropColumn('lang');
            }
        );
    }
};
