<?php

use App\Content\ContentCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The header menu starts with a link to the home page. An existing menu
     * gets it in front of its links, unless it already links to the home
     * page; editors can move or remove it like any other link.
     */
    public function up(): void
    {
        if ($this->homeLinks()->exists()) {
            return;
        }

        DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->increment('position');

        $now = now();

        DB::table('menu_items')->insert([
            'menu' => 'header',
            'parent_id' => null,
            'type' => 'system',
            'system_page' => 'home',
            'page_id' => null,
            'url' => null,
            'label' => null,
            'opens_in_new_tab' => false,
            'is_visible' => true,
            'position' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // The menus are cached with the other public content.
        app(ContentCache::class)->flush();
    }

    public function down(): void
    {
        $this->homeLinks()->delete();

        app(ContentCache::class)->flush();
    }

    private function homeLinks(): Builder
    {
        return DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->where('type', 'system')->where('system_page', 'home');
    }
};
