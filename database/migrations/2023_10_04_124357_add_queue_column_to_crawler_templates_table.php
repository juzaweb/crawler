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
                $table->string('queue')->index()->default('default');
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
                $table->dropColumn(['queue']);
            }
        );
    }
};
