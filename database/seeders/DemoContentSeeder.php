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
use App\Models\Milestone;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTranslation;
use App\Models\Service;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\SocialNetwork;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Placeholder content for local development, so every section of every
 * page can be seen with content in all three languages.
 *
 * Nothing here passes for real company information: every sample text is
 * marked as a sample in its language, people and companies are called
 * "Sample person" or "Sample client", the contact email uses the reserved
 * .test domain, social links point to the networks' home pages and the
 * images are plain generated graphics. Dhaman and Fantastic are the
 * company's products in development: their names are real, what they do
 * is left for the company to describe.
 *
 * It can be run again on a database that already has content: each
 * section only receives the samples it is short of, and existing content
 * and saved settings are never changed or removed.
 */
class DemoContentSeeder extends Seeder
{
    /** Marker put in front of every sample text. */
    private const SAMPLE = ['ar' => 'نموذج: ', 'en' => 'Sample: ', 'tr' => 'Örnek: '];

    private const PLACEHOLDER = [
        'ar' => 'نص تجريبي يُستبدل من لوحة التحكم.',
        'en' => 'Placeholder text, to be replaced in the dashboard.',
        'tr' => 'Örnek metin; yönetim panelinden değiştirilecek.',
    ];

    /** The company's products in development, by name in each language. */
    private const PRODUCTS = [
        ['ar' => 'ضمان', 'en' => 'Dhaman', 'tr' => 'Dhaman'],
        ['ar' => 'Fantastic', 'en' => 'Fantastic', 'tr' => 'Fantastic'],
    ];

    private const IN_DEVELOPMENT = [
        'ar' => 'منتج من منتجات Wasmsoft قيد التطوير. يُضاف وصفه لاحقًا من لوحة التحكم.',
        'en' => 'A Wasmsoft product in development. Its description will be added in the dashboard.',
        'tr' => 'Geliştirilmekte olan bir Wasmsoft ürünü. Açıklaması yönetim panelinden eklenecek.',
    ];

    /** Published projects that fill the portfolio (two rows), products included. */
    private const FULL_PORTFOLIO = 6;

    public function __construct(
        private readonly MediaLibrary $library,
        private readonly SiteSettings $settings,
    ) {}

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo content must never be loaded outside local environments.');
        }

        $editor = User::query()->where('email', 'editor@wasmsoft.test')->first();
        $author = User::query()->where('email', 'author@wasmsoft.test')->first();

        $this->homePage();
        $this->aboutPage();
        $this->partnershipsPage();
        $this->siteSettings();
        $this->services();
        $this->products($editor);
        $this->projects($editor);
        $this->news($editor, $author);
        $this->highlights();
        $this->testimonials();
        $this->team();
        $this->milestones();
    }

    /**
     * The home page headline and introduction, unless they have been written.
     */
    private function homePage(): void
    {
        $home = Page::query()->system(SystemPage::Home)->first() ?? $this->systemPage(SystemPage::Home);

        if ($home->translations()->exists()) {
            return;
        }

        $home->syncTranslations($this->texts([
            'ar' => ['title' => 'برمجيات تُبنى حول أعمالك', 'summary' => 'نخطط لتطبيقات الويب والجوال ونصممها ونبنيها ونتولى صيانتها.'],
            'en' => ['title' => 'Software built around your business', 'summary' => 'We plan, design, build and maintain web and mobile applications.'],
            'tr' => ['title' => 'İşinize göre şekillenen yazılımlar', 'summary' => 'Web ve mobil uygulamaları planlıyor, tasarlıyor, geliştiriyor ve bakımını yapıyoruz.'],
        ], withBody: true));
    }

    /**
     * An about page linked from the menus, unless the site already has one
     * (or its addresses are taken).
     */
    private function aboutPage(): void
    {
        $slugs = ['ar' => 'من-نحن', 'en' => 'about', 'tr' => 'hakkimizda'];

        if (Page::query()->where('template', PageTemplate::About)->exists() || PageTranslation::query()->whereIn('slug', $slugs)->exists()) {
            return;
        }

        $about = new Page(['template' => PageTemplate::About]);
        $about->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth()])->save();

        $about->syncTranslations([
            'ar' => [
                'title' => self::SAMPLE['ar'].'من نحن',
                'slug' => $slugs['ar'],
                'summary' => self::PLACEHOLDER['ar'],
                'body' => "## رسالتنا\n\n".self::PLACEHOLDER['ar']."\n\n## رؤيتنا\n\n".self::PLACEHOLDER['ar'],
            ],
            'en' => [
                'title' => self::SAMPLE['en'].'About us',
                'slug' => $slugs['en'],
                'summary' => self::PLACEHOLDER['en'],
                'body' => "## Our mission\n\n".self::PLACEHOLDER['en']."\n\n## Our vision\n\n".self::PLACEHOLDER['en'],
            ],
            'tr' => [
                'title' => self::SAMPLE['tr'].'Hakkımızda',
                'slug' => $slugs['tr'],
                'summary' => self::PLACEHOLDER['tr'],
                'body' => "## Misyonumuz\n\n".self::PLACEHOLDER['tr']."\n\n## Vizyonumuz\n\n".self::PLACEHOLDER['tr'],
            ],
        ]);

        $this->menus($about);
    }

    /**
     * A partnerships page (its ways to partner are highlights), linked at the
     * end of the header and of the footer's first column, unless the site
     * already has one (or its addresses are taken).
     */
    private function partnershipsPage(): void
    {
        $slugs = ['ar' => 'الشراكات', 'en' => 'partnerships', 'tr' => 'is-ortakliklari'];

        if (Page::query()->where('template', PageTemplate::Partnerships)->exists() || PageTranslation::query()->whereIn('slug', $slugs)->exists()) {
            return;
        }

        $page = new Page(['template' => PageTemplate::Partnerships]);
        $page->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth()])->save();

        $page->syncTranslations([
            'ar' => [
                'title' => self::SAMPLE['ar'].'الشراكات',
                'slug' => $slugs['ar'],
                'summary' => self::PLACEHOLDER['ar'],
                'body' => "## العمل مع Wasmsoft\n\n".self::PLACEHOLDER['ar'],
            ],
            'en' => [
                'title' => self::SAMPLE['en'].'Partnerships',
                'slug' => $slugs['en'],
                'summary' => self::PLACEHOLDER['en'],
                'body' => "## Working with Wasmsoft\n\n".self::PLACEHOLDER['en'],
            ],
            'tr' => [
                'title' => self::SAMPLE['tr'].'İş ortaklıkları',
                'slug' => $slugs['tr'],
                'summary' => self::PLACEHOLDER['tr'],
                'body' => "## Wasmsoft ile çalışmak\n\n".self::PLACEHOLDER['tr'],
            ],
        ]);

        // New menu items go last (Sortable).
        MenuItem::query()->create(['menu' => MenuLocation::Header, 'type' => MenuLinkType::Page, 'page_id' => $page->id]);

        $column = MenuItem::query()->where('menu', MenuLocation::Footer)->where('type', MenuLinkType::Group)->ordered()->first();

        if ($column !== null) {
            MenuItem::query()->create(['menu' => MenuLocation::Footer, 'parent_id' => $column->id, 'type' => MenuLinkType::Page, 'page_id' => $page->id]);
        }
    }

    /**
     * Tagline, description, footer note, contact details and social links:
     * each one that has not been filled in yet.
     */
    private function siteSettings(): void
    {
        $samples = [
            SettingKey::SiteTagline->value => $this->marked([
                'ar' => 'برمجيات ومنتجات رقمية',
                'en' => 'Software and digital products',
                'tr' => 'Yazılım ve dijital ürünler',
            ]),
            SettingKey::SiteDescription->value => $this->marked([
                'ar' => 'وصف قصير لشركة Wasmsoft يظهر في تذييل الموقع وتستخدمه محركات البحث، ويُعدَّل من الإعدادات.',
                'en' => 'A short description of Wasmsoft, shown in the footer and used by search engines. Edit it in Settings.',
                'tr' => 'Alt bilgide görünen ve arama motorlarının kullandığı kısa bir Wasmsoft açıklaması. Ayarlar bölümünden düzenlenir.',
            ]),
            SettingKey::FooterNote->value => $this->marked([
                'ar' => 'ملاحظة التذييل، مثل بيانات السجل التجاري للشركة.',
                'en' => 'Footer note, for example the company registration details.',
                'tr' => 'Alt bilgi notu, örneğin şirketin ticaret sicil bilgileri.',
            ]),
            SettingKey::ContactEmail->value => 'hello@wasmsoft.test',
            SettingKey::ContactPhone->value => '+90 000 000 00 00',
            SettingKey::ContactAddress->value => $this->marked([
                'ar' => "الشارع ورقم المبنى\nالمدينة، الدولة",
                'en' => "Street and building number\nCity, Country",
                'tr' => "Cadde ve bina numarası\nŞehir, Ülke",
            ]),
            SettingKey::ContactHours->value => $this->marked([
                'ar' => 'من الإثنين إلى الجمعة، 09:00–18:00',
                'en' => 'Monday to Friday, 09:00–18:00',
                'tr' => 'Pazartesi–Cuma, 09:00–18:00',
            ]),
            SettingKey::ContactMapUrl->value => 'https://www.openstreetmap.org/',
            SettingKey::SocialLinks->value => [
                ['network' => SocialNetwork::LinkedIn->value, 'url' => 'https://www.linkedin.com/'],
                ['network' => SocialNetwork::GitHub->value, 'url' => 'https://github.com/'],
                ['network' => SocialNetwork::X->value, 'url' => 'https://x.com/'],
                ['network' => SocialNetwork::Instagram->value, 'url' => 'https://www.instagram.com/'],
            ],
        ];

        $unset = [];

        foreach ($samples as $key => $value) {
            if (! $this->isFilled($this->settings->get(SettingKey::from($key)))) {
                $unset[$key] = $value;
            }
        }

        if ($unset !== []) {
            $this->settings->put($unset);
        }
    }

    private function services(): void
    {
        $services = [
            [ContentIcon::Code, ['ar' => 'تطوير تطبيقات الويب', 'en' => 'Web application development', 'tr' => 'Web uygulaması geliştirme']],
            [ContentIcon::Mobile, ['ar' => 'تطبيقات الجوال', 'en' => 'Mobile applications', 'tr' => 'Mobil uygulamalar']],
            [ContentIcon::Cloud, ['ar' => 'الاستضافة والصيانة', 'en' => 'Hosting and maintenance', 'tr' => 'Barındırma ve bakım']],
            [ContentIcon::Design, ['ar' => 'تصميم الواجهات وتجربة المستخدم', 'en' => 'UI and UX design', 'tr' => 'Arayüz ve kullanıcı deneyimi tasarımı']],
            [ContentIcon::Analytics, ['ar' => 'البيانات والتقارير', 'en' => 'Data and reporting', 'tr' => 'Veri ve raporlama']],
            [ContentIcon::Support, ['ar' => 'الدعم الفني', 'en' => 'Technical support', 'tr' => 'Teknik destek']],
        ];

        foreach ($this->stillMissing($services, Service::query()->published()->count()) as [$icon, $titles]) {
            $service = new Service(['icon' => $icon, 'is_featured' => true]);
            $service->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth()])->save();

            $service->syncTranslations($this->texts(array_map(fn (string $title): array => ['title' => $title], $titles), withBody: true, benefits: true));
        }
    }

    /**
     * Dhaman and Fantastic, newest in the portfolio so they lead the home
     * page. Their descriptions say they are in development and wait for the
     * company's own text.
     */
    private function products(?User $uploader): void
    {
        $category = null;

        foreach (self::PRODUCTS as $index => $names) {
            if (ProjectTranslation::query()->where('locale', 'en')->where('title', $names['en'])->exists()) {
                continue;
            }

            $category ??= $this->sampleTaxonomy(ProjectCategory::class, ['ar' => 'منتجاتنا', 'en' => 'Our products', 'tr' => 'Ürünlerimiz']);

            $project = new Project([
                'project_category_id' => $category->id,
                'cover_id' => $this->sampleImage("product-{$index}", $uploader)->id,
                'is_featured' => true,
            ]);
            $project->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subDays($index + 1)])->save();

            $translations = [];

            foreach ($names as $locale => $name) {
                $translations[$locale] = [
                    'title' => $name,
                    'summary' => self::IN_DEVELOPMENT[$locale],
                    'body' => self::IN_DEVELOPMENT[$locale]."\n\n".self::PLACEHOLDER[$locale],
                ];
            }

            $project->syncTranslations($translations);
        }
    }

    private function projects(?User $uploader): void
    {
        $projects = [
            ['ar' => 'بوابة إدارة الحجوزات', 'en' => 'Booking management portal', 'tr' => 'Rezervasyon yönetim portalı'],
            ['ar' => 'تطبيق متابعة الطلبات', 'en' => 'Order tracking app', 'tr' => 'Sipariş takip uygulaması'],
            ['ar' => 'لوحة تقارير المبيعات', 'en' => 'Sales reporting dashboard', 'tr' => 'Satış raporlama paneli'],
            ['ar' => 'بوابة دعم العملاء', 'en' => 'Customer support portal', 'tr' => 'Müşteri destek portalı'],
        ];

        $missing = $this->stillMissing($projects, Project::query()->published()->count(), self::FULL_PORTFOLIO);

        if ($missing === []) {
            return;
        }

        $category = $this->sampleTaxonomy(ProjectCategory::class, ['ar' => 'تطبيقات الويب', 'en' => 'Web applications', 'tr' => 'Web uygulamaları']);

        foreach ($missing as $index => $titles) {
            $project = new Project([
                'project_category_id' => $category->id,
                'cover_id' => $this->sampleImage("project-{$index}", $uploader)->id,
                'technologies' => ['Laravel', 'PostgreSQL'],
                'is_featured' => true,
            ]);
            $project->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subWeeks($index + 1)])->save();
            $project->syncTranslations($this->texts(array_map(fn (string $title): array => ['title' => $title], $titles), withBody: true));
        }
    }

    /**
     * Published articles; on an empty news section also a scheduled article
     * and an author's draft, which show the dashboard's publishing states.
     */
    private function news(?User $editor, ?User $author): void
    {
        $published = [
            ['ar' => 'خبر تجريبي أول', 'en' => 'First sample article', 'tr' => 'Birinci örnek yazı'],
            ['ar' => 'خبر تجريبي ثانٍ', 'en' => 'Second sample article', 'tr' => 'İkinci örnek yazı'],
            ['ar' => 'خبر تجريبي ثالث', 'en' => 'Third sample article', 'tr' => 'Üçüncü örnek yazı'],
            ['ar' => 'خبر تجريبي رابع', 'en' => 'Fourth sample article', 'tr' => 'Dördüncü örnek yazı'],
            ['ar' => 'خبر تجريبي خامس', 'en' => 'Fifth sample article', 'tr' => 'Beşinci örnek yazı'],
            ['ar' => 'خبر تجريبي سادس', 'en' => 'Sixth sample article', 'tr' => 'Altıncı örnek yazı'],
        ];

        $articles = [];

        foreach ($this->stillMissing($published, Post::query()->published()->count()) as $index => $titles) {
            $articles[] = [PublicationStatus::Published, now()->subDays(40 - 7 * $index), $editor, $titles];
        }

        if (Post::query()->doesntExist()) {
            $articles[] = [PublicationStatus::Published, now()->addDays(5), $editor, ['ar' => 'خبر تجريبي مجدول', 'en' => 'Scheduled sample article', 'tr' => 'Zamanlanmış örnek yazı']];
            $articles[] = [PublicationStatus::Draft, null, $author, ['ar' => 'مسودة تجريبية من الكاتب', 'en' => 'Sample draft by the author', 'tr' => 'Yazarın örnek taslağı']];
        }

        if ($articles === []) {
            return;
        }

        $category = $this->sampleTaxonomy(PostCategory::class, ['ar' => 'أخبار الشركة', 'en' => 'Company news', 'tr' => 'Şirket haberleri']);
        $tag = $this->sampleTaxonomy(Tag::class, ['ar' => 'تقنية', 'en' => 'Technology', 'tr' => 'Teknoloji']);

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

    /**
     * Differentiators and figures for the home page, values for the about
     * page, ways to partner for the partnerships page. Figures are shown
     * with their label only, without a description.
     */
    private function highlights(): void
    {
        $groups = [
            [HighlightGroup::Differentiator, [
                ['icon' => ContentIcon::Communication, 'figure' => null, 'title' => ['ar' => 'تواصل واضح', 'en' => 'Clear communication', 'tr' => 'Açık iletişim']],
                ['icon' => ContentIcon::Security, 'figure' => null, 'title' => ['ar' => 'الأمان أولًا', 'en' => 'Security first', 'tr' => 'Önce güvenlik']],
                ['icon' => ContentIcon::Global, 'figure' => null, 'title' => ['ar' => 'بثلاث لغات', 'en' => 'In three languages', 'tr' => 'Üç dilde']],
            ]],
            [HighlightGroup::Value, [
                ['icon' => ContentIcon::Quality, 'figure' => null, 'title' => ['ar' => 'الجودة', 'en' => 'Quality', 'tr' => 'Kalite']],
                ['icon' => ContentIcon::Care, 'figure' => null, 'title' => ['ar' => 'الاهتمام', 'en' => 'Care', 'tr' => 'Özen']],
                ['icon' => ContentIcon::Innovation, 'figure' => null, 'title' => ['ar' => 'الابتكار', 'en' => 'Innovation', 'tr' => 'Yenilik']],
            ]],
            [HighlightGroup::Metric, [
                ['icon' => null, 'figure' => '6+', 'title' => ['ar' => 'مشاريع منجزة', 'en' => 'Projects delivered', 'tr' => 'Tamamlanan projeler']],
                ['icon' => null, 'figure' => '3', 'title' => ['ar' => 'لغات مدعومة', 'en' => 'Languages supported', 'tr' => 'Desteklenen diller']],
                ['icon' => null, 'figure' => '2', 'title' => ['ar' => 'منتجات قيد التطوير', 'en' => 'Products in development', 'tr' => 'Geliştirilen ürünler']],
                ['icon' => null, 'figure' => '10+', 'title' => ['ar' => 'تقنيات نعمل بها', 'en' => 'Technologies we work with', 'tr' => 'Kullandığımız teknolojiler']],
            ]],
            [HighlightGroup::Partnership, [
                ['icon' => ContentIcon::Code, 'figure' => null, 'title' => ['ar' => 'شركاء التقنية', 'en' => 'Technology partners', 'tr' => 'Teknoloji ortakları']],
                ['icon' => ContentIcon::Communication, 'figure' => null, 'title' => ['ar' => 'شركاء الإحالة', 'en' => 'Referral partners', 'tr' => 'Yönlendirme ortakları']],
                ['icon' => ContentIcon::Team, 'figure' => null, 'title' => ['ar' => 'شركاء التنفيذ', 'en' => 'Delivery partners', 'tr' => 'Uygulama ortakları']],
            ]],
        ];

        foreach ($groups as [$group, $samples]) {
            $shown = Highlight::query()->where('group', $group)->where('is_visible', true)->count();

            foreach ($this->stillMissing($samples, $shown) as $sample) {
                Highlight::query()->create([
                    'group' => $group,
                    'icon' => $sample['icon'],
                    'figure' => $sample['figure'],
                    'title' => $this->marked($sample['title']),
                    'description' => $sample['figure'] === null ? self::PLACEHOLDER : null,
                    'is_visible' => true,
                ]);
            }
        }
    }

    /**
     * Sample quotes of different lengths. They are shown like quotes a client
     * agreed to, so the section can be previewed.
     */
    private function testimonials(): void
    {
        $samples = [
            ['Sample client A', ['ar' => 'مدير العمليات', 'en' => 'Operations manager', 'tr' => 'Operasyon müdürü'], [
                'ar' => 'هنا يظهر اقتباس قصير من أحد العملاء عن العمل مع Wasmsoft، بعد موافقته على نشره باسمه.',
                'en' => 'A short quote from a client about working with Wasmsoft appears here, once they agree to be quoted by name.',
                'tr' => 'Bir müşterinin Wasmsoft ile çalışmaya dair kısa bir alıntısı, adıyla yayımlanmasına onay verdiğinde burada yer alır.',
            ]],
            ['Sample client B', ['ar' => 'مؤسس', 'en' => 'Founder', 'tr' => 'Kurucu'], [
                'ar' => 'اقتباس أطول يبيّن شكل البطاقة عندما يتحدث العميل عن المشروع وما تغيّر بعد إطلاقه، في سطرين أو ثلاثة.',
                'en' => 'A longer quote shows how the card looks when a client talks about the project and what changed after launch, over two or three lines.',
                'tr' => 'Daha uzun bir alıntı, müşteri projeden ve yayına alındıktan sonra nelerin değiştiğinden iki üç satırda bahsettiğinde kartın nasıl göründüğünü gösterir.',
            ]],
            ['Sample client C', ['ar' => 'مدير تقنية المعلومات', 'en' => 'IT director', 'tr' => 'BT direktörü'], [
                'ar' => 'اقتباس ثالث قصير.',
                'en' => 'A third, short quote.',
                'tr' => 'Üçüncü, kısa bir alıntı.',
            ]],
        ];

        $shown = Testimonial::query()->where('is_visible', true)->where('has_consent', true)->count();

        foreach ($this->stillMissing($samples, $shown) as [$name, $roles, $quotes]) {
            Testimonial::query()->create([
                'quote' => $this->marked($quotes),
                'author_name' => $name,
                'author_role' => $this->marked($roles),
                'company' => 'Sample company',
                'has_consent' => true,
                'is_visible' => true,
            ]);
        }
    }

    private function team(): void
    {
        $samples = [
            ['Sample person A', ['ar' => 'مهندس برمجيات', 'en' => 'Software engineer', 'tr' => 'Yazılım mühendisi']],
            ['Sample person B', ['ar' => 'مصمم منتجات', 'en' => 'Product designer', 'tr' => 'Ürün tasarımcısı']],
            ['Sample person C', ['ar' => 'مدير مشاريع', 'en' => 'Project manager', 'tr' => 'Proje yöneticisi']],
        ];

        foreach ($this->stillMissing($samples, TeamMember::query()->where('is_visible', true)->count()) as [$name, $roles]) {
            TeamMember::query()->create([
                'name' => $name,
                'role' => $this->marked($roles),
                'bio' => self::PLACEHOLDER,
                'is_visible' => true,
            ]);
        }
    }

    /**
     * Consecutive years ending with the current one.
     */
    private function milestones(): void
    {
        $samples = [
            ['ar' => 'المحطة الأولى', 'en' => 'First milestone', 'tr' => 'Birinci kilometre taşı'],
            ['ar' => 'المحطة الثانية', 'en' => 'Second milestone', 'tr' => 'İkinci kilometre taşı'],
            ['ar' => 'المحطة الثالثة', 'en' => 'Third milestone', 'tr' => 'Üçüncü kilometre taşı'],
        ];

        $missing = $this->stillMissing($samples, Milestone::query()->where('is_visible', true)->count());

        foreach ($missing as $index => $titles) {
            Milestone::query()->create([
                'year' => now()->year - count($missing) + 1 + $index,
                'title' => $this->marked($titles),
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
     * The samples a section still needs: as many as it is short of $full
     * (by default, of the number of samples) while it shows $shown items.
     *
     * @template TSample
     *
     * @param  list<TSample>  $samples
     * @return list<TSample>
     */
    private function stillMissing(array $samples, int $shown, ?int $full = null): array
    {
        return array_slice($samples, 0, max(0, ($full ?? count($samples)) - $shown));
    }

    /**
     * The sample category or tag with these names, created the first time
     * it is needed.
     *
     * @param  class-string<PostCategory|ProjectCategory|Tag>  $model
     * @param  array<string, string>  $names  locale => name, without the sample marker
     */
    private function sampleTaxonomy(string $model, array $names): PostCategory|ProjectCategory|Tag
    {
        $names = $this->marked($names);

        $existing = $model::query()
            ->whereHas('translations', fn (Builder $translation) => $translation->where('locale', 'en')->where('name', $names['en']))
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $taxonomy = new $model;
        $taxonomy->save();
        $taxonomy->syncTranslations(array_map(fn (string $name): array => ['name' => $name], $names));

        return $taxonomy;
    }

    /**
     * Whether a setting has been filled in; a locale => text map counts once
     * it is written in any language.
     */
    private function isFilled(mixed $value): bool
    {
        if (is_array($value)) {
            return array_filter($value, fn (mixed $item): bool => filled($item)) !== [];
        }

        return filled($value);
    }

    /**
     * The same texts with the sample marker of their language in front.
     *
     * @param  array<string, string>  $texts  locale => text
     * @return array<string, string>
     */
    private function marked(array $texts): array
    {
        foreach ($texts as $locale => $text) {
            $texts[$locale] = self::SAMPLE[$locale].$text;
        }

        return $texts;
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
