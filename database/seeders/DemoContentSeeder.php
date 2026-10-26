<?php

namespace Database\Seeders;

use App\Content\ContentIcon;
use App\Content\HighlightGroup;
use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Content\SystemPage;
use App\Media\MediaLibrary;
use App\Models\Highlight;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Placeholder content for local development, so every page of the site can
 * be seen and tested. Every title is marked as a sample in its language,
 * and the images are plain generated graphics. Nothing here pretends to be
 * a real client, testimonial, figure, team member, company history or
 * contact detail: those stay empty until the company supplies them.
 *
 * Runs once: it does nothing when services or articles already exist.
 */
class DemoContentSeeder extends Seeder
{
    /** Marker put in front of every sample title. */
    private const SAMPLE = ['ar' => 'نموذج: ', 'en' => 'Sample: ', 'tr' => 'Örnek: '];

    private const PLACEHOLDER = [
        'ar' => 'نص تجريبي يُستبدل من لوحة التحكم.',
        'en' => 'Placeholder text, to be replaced in the dashboard.',
        'tr' => 'Örnek metin; yönetim panelinden değiştirilecek.',
    ];

    public function __construct(private readonly MediaLibrary $library) {}

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo content must never be loaded outside local environments.');
        }

        if (Service::query()->exists() || Post::query()->exists()) {
            $this->command?->info('Content already exists; demo content skipped.');

            return;
        }

        $editor = User::query()->where('email', 'editor@wasmsoft.test')->first();
        $author = User::query()->where('email', 'author@wasmsoft.test')->first();

        $this->homePage();
        $about = $this->aboutPage();
        $this->services();
        $this->projects($editor);
        $this->news($editor, $author);
        $this->highlights();
        $this->menus($about);
    }

    private function homePage(): void
    {
        $home = Page::query()->system(SystemPage::Home)->first() ?? $this->systemPage(SystemPage::Home);

        $home->syncTranslations($this->texts([
            'ar' => ['title' => 'برمجيات تُبنى حول أعمالك', 'summary' => 'نخطط لتطبيقات الويب والجوال ونصممها ونبنيها ونتولى صيانتها.'],
            'en' => ['title' => 'Software built around your business', 'summary' => 'We plan, design, build and maintain web and mobile applications.'],
            'tr' => ['title' => 'İşinize göre şekillenen yazılımlar', 'summary' => 'Web ve mobil uygulamaları planlıyor, tasarlıyor, geliştiriyor ve bakımını yapıyoruz.'],
        ], withBody: true));
    }

    private function aboutPage(): Page
    {
        $about = new Page(['template' => PageTemplate::About]);
        $about->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth()])->save();

        $about->syncTranslations([
            'ar' => [
                'title' => self::SAMPLE['ar'].'من نحن',
                'slug' => 'من-نحن',
                'summary' => self::PLACEHOLDER['ar'],
                'body' => "## رسالتنا\n\n".self::PLACEHOLDER['ar']."\n\n## رؤيتنا\n\n".self::PLACEHOLDER['ar'],
            ],
            'en' => [
                'title' => self::SAMPLE['en'].'About us',
                'slug' => 'about',
                'summary' => self::PLACEHOLDER['en'],
                'body' => "## Our mission\n\n".self::PLACEHOLDER['en']."\n\n## Our vision\n\n".self::PLACEHOLDER['en'],
            ],
            'tr' => [
                'title' => self::SAMPLE['tr'].'Hakkımızda',
                'slug' => 'hakkimizda',
                'summary' => self::PLACEHOLDER['tr'],
                'body' => "## Misyonumuz\n\n".self::PLACEHOLDER['tr']."\n\n## Vizyonumuz\n\n".self::PLACEHOLDER['tr'],
            ],
        ]);

        return $about;
    }

    private function services(): void
    {
        $services = [
            [ContentIcon::Code, ['ar' => 'تطوير تطبيقات الويب', 'en' => 'Web application development', 'tr' => 'Web uygulaması geliştirme']],
            [ContentIcon::Mobile, ['ar' => 'تطبيقات الجوال', 'en' => 'Mobile applications', 'tr' => 'Mobil uygulamalar']],
            [ContentIcon::Cloud, ['ar' => 'الاستضافة والصيانة', 'en' => 'Hosting and maintenance', 'tr' => 'Barındırma ve bakım']],
        ];

        foreach ($services as $position => [$icon, $titles]) {
            $service = new Service(['icon' => $icon, 'is_featured' => true]);
            $service->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth(), 'position' => $position + 1])->save();

            $service->syncTranslations($this->texts(array_map(fn (string $title): array => ['title' => $title], $titles), withBody: true, benefits: true));
        }
    }

    private function projects(?User $uploader): void
    {
        $category = new ProjectCategory;
        $category->save();
        $category->syncTranslations([
            'ar' => ['name' => self::SAMPLE['ar'].'تطبيقات الويب'],
            'en' => ['name' => self::SAMPLE['en'].'Web applications'],
            'tr' => ['name' => self::SAMPLE['tr'].'Web uygulamaları'],
        ]);

        $projects = [
            ['ar' => 'بوابة إدارة الحجوزات', 'en' => 'Booking management portal', 'tr' => 'Rezervasyon yönetim portalı'],
            ['ar' => 'تطبيق متابعة الطلبات', 'en' => 'Order tracking app', 'tr' => 'Sipariş takip uygulaması'],
            ['ar' => 'لوحة تقارير المبيعات', 'en' => 'Sales reporting dashboard', 'tr' => 'Satış raporlama paneli'],
        ];

        foreach ($projects as $index => $titles) {
            $project = new Project([
                'project_category_id' => $category->id,
                'cover_id' => $this->sampleImage("project-{$index}", $uploader)->id,
                'technologies' => ['Laravel', 'PostgreSQL'],
                'is_featured' => true,
            ]);
            $project->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subWeeks(3 - $index)])->save();
            $project->syncTranslations($this->texts(array_map(fn (string $title): array => ['title' => $title], $titles), withBody: true));
        }
    }

    private function news(?User $editor, ?User $author): void
    {
        $category = new PostCategory;
        $category->save();
        $category->syncTranslations([
            'ar' => ['name' => self::SAMPLE['ar'].'أخبار الشركة'],
            'en' => ['name' => self::SAMPLE['en'].'Company news'],
            'tr' => ['name' => self::SAMPLE['tr'].'Şirket haberleri'],
        ]);

        $tag = new Tag;
        $tag->save();
        $tag->syncTranslations([
            'ar' => ['name' => self::SAMPLE['ar'].'تقنية'],
            'en' => ['name' => self::SAMPLE['en'].'Technology'],
            'tr' => ['name' => self::SAMPLE['tr'].'Teknoloji'],
        ]);

        $articles = [
            [PublicationStatus::Published, now()->subDays(20), $editor, ['ar' => 'خبر تجريبي أول', 'en' => 'First sample article', 'tr' => 'Birinci örnek yazı']],
            [PublicationStatus::Published, now()->subDays(8), $editor, ['ar' => 'خبر تجريبي ثانٍ', 'en' => 'Second sample article', 'tr' => 'İkinci örnek yazı']],
            [PublicationStatus::Published, now()->addDays(5), $editor, ['ar' => 'خبر تجريبي مجدول', 'en' => 'Scheduled sample article', 'tr' => 'Zamanlanmış örnek yazı']],
            [PublicationStatus::Draft, null, $author, ['ar' => 'مسودة تجريبية من الكاتب', 'en' => 'Sample draft by the author', 'tr' => 'Yazarın örnek taslağı']],
        ];

        foreach ($articles as $index => [$status, $date, $writer, $titles]) {
            $post = new Post([
                'author_id' => $writer?->id,
                'post_category_id' => $category->id,
                'cover_id' => $this->sampleImage("article-{$index}", $editor)->id,
            ]);
            $post->forceFill(['status' => $status, 'published_at' => $date])->save();
            $post->syncTranslations($this->texts(array_map(fn (string $title): array => ['title' => $title], $titles), withBody: true));
            $post->tags()->attach($tag);
        }
    }

    private function highlights(): void
    {
        $items = [
            [HighlightGroup::Differentiator, ContentIcon::Communication, ['ar' => 'تواصل واضح', 'en' => 'Clear communication', 'tr' => 'Açık iletişim']],
            [HighlightGroup::Differentiator, ContentIcon::Security, ['ar' => 'الأمان أولًا', 'en' => 'Security first', 'tr' => 'Önce güvenlik']],
            [HighlightGroup::Differentiator, ContentIcon::Global, ['ar' => 'بثلاث لغات', 'en' => 'In three languages', 'tr' => 'Üç dilde']],
            [HighlightGroup::Value, ContentIcon::Quality, ['ar' => 'الجودة', 'en' => 'Quality', 'tr' => 'Kalite']],
            [HighlightGroup::Value, ContentIcon::Care, ['ar' => 'الاهتمام', 'en' => 'Care', 'tr' => 'Özen']],
            [HighlightGroup::Value, ContentIcon::Innovation, ['ar' => 'الابتكار', 'en' => 'Innovation', 'tr' => 'Yenilik']],
        ];

        foreach ($items as [$group, $icon, $titles]) {
            $sampleTitles = [];

            foreach ($titles as $locale => $title) {
                $sampleTitles[$locale] = self::SAMPLE[$locale].$title;
            }

            Highlight::query()->create([
                'group' => $group,
                'icon' => $icon,
                'title' => $sampleTitles,
                'description' => self::PLACEHOLDER,
                'is_visible' => true,
            ]);
        }
    }

    /**
     * Link the about page from the header (first) and the footer's first column.
     */
    private function menus(Page $about): void
    {
        MenuItem::query()->where('menu', MenuLocation::Header)->increment('position');
        MenuItem::query()->create(['menu' => MenuLocation::Header, 'type' => MenuLinkType::Page, 'page_id' => $about->id])
            ->forceFill(['position' => 0])->save();

        $column = MenuItem::query()->where('menu', MenuLocation::Footer)->where('type', MenuLinkType::Group)->ordered()->first();

        if ($column !== null) {
            MenuItem::query()->where('parent_id', $column->id)->increment('position');
            MenuItem::query()->create(['menu' => MenuLocation::Footer, 'parent_id' => $column->id, 'type' => MenuLinkType::Page, 'page_id' => $about->id])
                ->forceFill(['position' => 0])->save();
        }
    }

    private function systemPage(SystemPage $key): Page
    {
        $page = new Page;
        $page->forceFill(['system_key' => $key, 'status' => PublicationStatus::Published, 'published_at' => now()])->save();

        return $page;
    }

    /**
     * Sample titles and placeholder texts in every language.
     *
     * @param  array<string, array<string, mixed>>  $fields  locale => fields (at least a title)
     * @return array<string, array<string, mixed>>
     */
    private function texts(array $fields, bool $withBody = false, bool $benefits = false): array
    {
        foreach ($fields as $locale => $values) {
            if (isset($values['title'])) {
                $values['title'] = self::SAMPLE[$locale].$values['title'];
            }

            $values['summary'] ??= self::PLACEHOLDER[$locale];

            if ($withBody) {
                $values['body'] ??= self::PLACEHOLDER[$locale]."\n\n- ".self::PLACEHOLDER[$locale];
            }

            if ($benefits) {
                $values['benefits'] = [self::PLACEHOLDER[$locale]];
            }

            $fields[$locale] = $values;
        }

        return $fields;
    }

    /**
     * @return int<0, 255>
     */
    private function channel(float $value): int
    {
        return max(0, min(255, (int) round($value)));
    }

    /**
     * A plain generated image (brand colours, labelled as a sample) stored
     * through the media library like an upload.
     */
    private function sampleImage(string $name, ?User $uploader): Media
    {
        $width = 1600;
        $height = 1000;
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $color = imagecolorallocate($image, $this->channel(58 + 66 * $ratio), $this->channel(20 + 38 * $ratio), $this->channel(103 + 134 * $ratio));
            imageline($image, 0, $y, $width, $y, $color === false ? 0 : $color);
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 64, 64, 'SAMPLE IMAGE', $white === false ? 0 : $white);

        $path = (string) tempnam(sys_get_temp_dir(), 'demo');
        imagejpeg($image, $path, 85);

        try {
            return $this->library->store(new UploadedFile($path, "sample-{$name}.jpg", 'image/jpeg', null, true), $uploader);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
