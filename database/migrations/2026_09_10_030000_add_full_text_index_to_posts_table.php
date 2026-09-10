<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->fullText(['title', 'content'], 'posts_title_content_fulltext');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropFullText('posts_title_content_fulltext');
        });
    }
};
