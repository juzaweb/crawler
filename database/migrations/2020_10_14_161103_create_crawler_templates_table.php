<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(
            'crawler_templates',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('link_element')->nullable();
                $table->json('data_elements')->nullable();
                $table->string('custom_class')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('crawler_templates');
    }
};
