<?php

use App\Content\ContentCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The summaries the demonstration content gave the about, partnerships
     * and news pages, and the shorter headlines it gives them now, per
     * language: [page, locale, old summary, new summary].
     *
     * @var list<array{string, string, string, string}>
     */
    private const HEADLINES = [
        ['about', 'ar', 'نبني المواقع الإلكترونية وتطبيقات الجوال وحلول الذكاء الاصطناعي وأنظمة الأعمال للشركات التي تريد أن تنمو بالتقنية.', 'نبني برمجيات تساعد الشركات على النمو'],
        ['about', 'en', 'We build websites, mobile apps, AI solutions and business systems for companies that want to grow with technology.', 'We build software that helps businesses grow'],
        ['about', 'tr', 'Teknolojiyle büyümek isteyen şirketler için web siteleri, mobil uygulamalar, yapay zekâ çözümleri ve iş sistemleri geliştiriyoruz.', 'İşletmelerin büyümesine yardımcı olan yazılımlar geliştiriyoruz'],
        ['partnerships', 'ar', 'نرحّب بالعمل مع شركات تكمّل مهاراتها ومنتجاتها ما نقدّمه، ليحصل العملاء على حلول متكاملة.', 'لنبنِ معًا حلولًا متكاملة'],
        ['partnerships', 'en', 'We are open to working with companies whose skills and products complement ours, so clients get complete solutions.', 'Let’s build complete solutions together'],
        ['partnerships', 'tr', 'Müşterilerin eksiksiz çözümler alabilmesi için yetkinlikleri ve ürünleri bizimkileri tamamlayan şirketlerle çalışmaya açığız.', 'Eksiksiz çözümleri birlikte geliştirelim'],
        ['news', 'ar', 'أخبار الشركة ومقالات عن بناء البرمجيات وتشغيلها، نكتبها لأصحاب الأعمال وفرقهم.', 'أخبار وأفكار من فريق Wasmsoft'],
        ['news', 'en', 'Company news and articles about building and running software, written for business owners and their teams.', 'News and ideas from the Wasmsoft team'],
        ['news', 'tr', 'Şirketimizden haberler ve yazılımın geliştirilmesi ile işletilmesi üzerine yazılar; işletme sahipleri ve ekipleri için.', 'Wasmsoft ekibinden haberler ve fikirler'],
    ];

    /**
     * The about, partnerships and news pages show their summary as a large
     * headline. Where a summary is still the one the demonstration content
     * wrote, it becomes the demonstration content's shorter headline, and
     * the longer text becomes the search description if there is none yet.
     * Summaries written in the dashboard are left as they are.
     */
    public function up(): void
    {
        foreach (self::HEADLINES as [$page, $locale, $old, $new]) {
            $this->translations($page, $locale)->where('summary', $old)->where(fn (Builder $query) => $query->whereNull('meta_description')->orWhere('meta_description', ''))
                ->update(['summary' => $new, 'meta_description' => $old, 'updated_at' => now()]);
            $this->translations($page, $locale)->where('summary', $old)->update(['summary' => $new, 'updated_at' => now()]);
        }

        // Pages are cached with the other public content.
        app(ContentCache::class)->flush();
    }

    public function down(): void
    {
        foreach (self::HEADLINES as [$page, $locale, $old, $new]) {
            $this->translations($page, $locale)->where('summary', $new)->where('meta_description', $old)
                ->update(['summary' => $old, 'meta_description' => null, 'updated_at' => now()]);
            $this->translations($page, $locale)->where('summary', $new)->update(['summary' => $old, 'updated_at' => now()]);
        }

        app(ContentCache::class)->flush();
    }

    /**
     * The translations in $locale of the about or partnerships page (by
     * their template) or of the news page.
     */
    private function translations(string $page, string $locale): Builder
    {
        $pages = DB::table('pages')->select('id');
        $pages = $page === 'news' ? $pages->where('system_key', 'news') : $pages->whereNull('system_key')->where('template', $page);

        return DB::table('page_translations')->where('locale', $locale)->whereIn('page_id', $pages);
    }
};
