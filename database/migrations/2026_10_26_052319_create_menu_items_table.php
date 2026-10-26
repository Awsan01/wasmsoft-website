<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header and footer menus managed in the dashboard. Footer links sit in
     * groups (the footer columns). The menus the site had before they became
     * editable are created here, so nothing changes until editors edit them.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('menu', 20);
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('system_page', 20)->nullable();
            $table->foreignId('page_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('url', 500)->nullable();
            $table->json('label')->nullable();
            $table->boolean('opens_in_new_tab')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['menu', 'parent_id', 'position']);
        });

        $now = now();
        $item = fn (array $attributes): array => [
            'parent_id' => null, 'system_page' => null, 'page_id' => null, 'url' => null, 'label' => null,
            'opens_in_new_tab' => false, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now, ...$attributes,
        ];
        $label = fn (string $key): string => (string) json_encode(
            ['ar' => __($key, [], 'ar'), 'en' => __($key, [], 'en'), 'tr' => __($key, [], 'tr')],
            JSON_UNESCAPED_UNICODE,
        );

        foreach (['services', 'projects', 'news'] as $position => $page) {
            DB::table('menu_items')->insert($item(['menu' => 'header', 'type' => 'system', 'system_page' => $page, 'position' => $position + 1]));
        }

        $groups = [
            ['site.footer.company', ['news', 'contact']],
            ['site.footer.work', ['services', 'projects']],
        ];

        foreach ($groups as $groupPosition => [$key, $pages]) {
            $groupId = DB::table('menu_items')->insertGetId($item(['menu' => 'footer', 'type' => 'group', 'label' => $label($key), 'position' => $groupPosition + 1]));

            foreach ($pages as $position => $page) {
                DB::table('menu_items')->insert($item(['menu' => 'footer', 'parent_id' => $groupId, 'type' => 'system', 'system_page' => $page, 'position' => $position + 1]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
