<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    protected array $tables = [
        'crawler_websites',
        'crawler_pages',
        'crawler_links',
        'crawler_contents',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table(
                $table,
                function (Blueprint $table) {
                    $table->unsignedBigInteger('site_id')->default(0)->index();
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table(
                $table,
                function (Blueprint $table) {
                    $table->dropColumn(['site_id']);
                }
            );
        }
    }
};
