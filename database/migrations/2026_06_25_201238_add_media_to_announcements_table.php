<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'featured_image')) {
                $table->string('featured_image', 255)->nullable()->after('content');
            }
            if (!Schema::hasColumn('announcements', 'media_gallery')) {
                $table->json('media_gallery')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('announcements', 'author')) {
                $table->string('author', 100)->nullable()->after('media_gallery');
            }
            if (!Schema::hasColumn('announcements', 'tags')) {
                $table->json('tags')->nullable()->after('author');
            }
            if (!Schema::hasColumn('announcements', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('tags');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['featured_image', 'media_gallery', 'author', 'tags', 'views_count']);
        });
    }
};
