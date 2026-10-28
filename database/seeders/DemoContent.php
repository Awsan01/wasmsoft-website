<?php

namespace Database\Seeders;

/**
 * The texts of the demonstration content (DemoContentSeeder), in every
 * language of the site.
 *
 * The six services are the company's own, with their summaries as the
 * company wrote them. Projects and articles show the kind of content the
 * site is made for: they name no client, partner or result and are meant
 * to be replaced in the dashboard before launch. Dhaman and Fantastic are
 * the company's products in development; what they do is left for the
 * company to describe.
 */
final class DemoContent
{
    public const HOME = [
        'ar' => [
            'title' => 'برمجيات تدفع نمو أعمالك',
            'summary' => 'نبني المواقع الإلكترونية وتطبيقات الجوال وحلول الذكاء الاصطناعي وأنظمة إدارة الأعمال، ونحافظ على البنية التحتية التي تشغّلها. فريق واحد من الفكرة الأولى إلى الإطلاق وما بعده.',
            'body' => 'من الموقع الإلكتروني للشركة إلى نظام أعمال متكامل، نصمم ونبني وندعم برمجيات تناسب طريقة عملك.',
        ],
        'en' => [
            'title' => 'Software that powers your business growth',
            'summary' => 'We build websites, mobile apps, AI solutions and business systems, and keep the infrastructure behind them running. One team, from the first idea to launch and beyond.',
            'body' => 'From a company website to a complete business system, we design, build and support software that fits the way you work.',
        ],
        'tr' => [
            'title' => 'İşinizin büyümesine güç veren yazılımlar',
            'summary' => 'Web siteleri, mobil uygulamalar, yapay zekâ çözümleri ve iş yönetim sistemleri geliştiriyor, bunları çalıştıran altyapıyı ayakta tutuyoruz. İlk fikirden lansmana ve sonrasına tek ekip.',
            'body' => 'Kurumsal bir web sitesinden eksiksiz bir iş sistemine kadar, çalışma biçiminize uyan yazılımları tasarlıyor, geliştiriyor ve destekliyoruz.',
        ],
    ];

    public const ABOUT = [
        'ar' => [
            'title' => 'من نحن',
            'slug' => 'من-نحن',
            'summary' => 'نبني المواقع الإلكترونية وتطبيقات الجوال وحلول الذكاء الاصطناعي وأنظمة الأعمال للشركات التي تريد أن تنمو بالتقنية.',
            'body' => <<<'MD'
                ## من نحن

                Wasmsoft شركة لتطوير البرمجيات. نساعد الشركات على التخطيط للبرمجيات التي تعتمد عليها وبنائها وتشغيلها، من المواقع الإلكترونية وبوابات العملاء إلى تطبيقات الجوال والأتمتة الذكية والأنظمة التي تدير العمليات اليومية.

                ## ما نؤمن به

                تبدأ البرمجيات الجيدة بفهم طريقة عمل الشركة. نستمع أولًا، ونُشرك عملاءنا في كل خطوة، ونبني حلولًا آمنة وسهلة الصيانة وجاهزة للنمو.

                ## كيف نعمل

                - **الاستكشاف**: نتعرّف على أهدافك ومستخدميك وقيودك.
                - **التصميم**: نرسم الحل والتجربة قبل كتابة الشيفرة.
                - **البناء**: نطوّر على مراحل قصيرة يمكنك رؤيتها واختبارها.
                - **الدعم**: نُبقي أنظمتك تعمل بأمان ومحدّثة باستمرار.
                MD,
        ],
        'en' => [
            'title' => 'About us',
            'slug' => 'about',
            'summary' => 'We build websites, mobile apps, AI solutions and business systems for companies that want to grow with technology.',
            'body' => <<<'MD'
                ## Who we are

                Wasmsoft is a software development company. We help businesses plan, build and run the software they depend on, from public websites and customer portals to mobile apps, intelligent automation and the systems behind daily operations.

                ## What we believe

                Good software starts with understanding how a business works. We listen first, keep our clients involved at every step and build solutions that are secure, maintainable and ready to grow.

                ## How we work

                - **Discover**: we learn your goals, your users and your constraints.
                - **Design**: we shape the solution and the experience before writing code.
                - **Build**: we develop in short steps that you can see and test.
                - **Support**: we keep your systems running, secure and up to date.
                MD,
        ],
        'tr' => [
            'title' => 'Hakkımızda',
            'slug' => 'hakkimizda',
            'summary' => 'Teknolojiyle büyümek isteyen şirketler için web siteleri, mobil uygulamalar, yapay zekâ çözümleri ve iş sistemleri geliştiriyoruz.',
            'body' => <<<'MD'
                ## Biz kimiz

                Wasmsoft bir yazılım geliştirme şirketidir. İşletmelerin güvendikleri yazılımları planlamasına, geliştirmesine ve çalıştırmasına yardımcı oluyoruz: kurumsal web sitelerinden ve müşteri portallarından mobil uygulamalara, akıllı otomasyona ve günlük operasyonları yöneten sistemlere kadar.

                ## Neye inanıyoruz

                İyi yazılım, bir işletmenin nasıl çalıştığını anlamakla başlar. Önce dinliyor, müşterilerimizi her adımda sürecin içinde tutuyor; güvenli, bakımı kolay ve büyümeye hazır çözümler geliştiriyoruz.

                ## Nasıl çalışıyoruz

                - **Keşif**: hedeflerinizi, kullanıcılarınızı ve kısıtlarınızı öğreniyoruz.
                - **Tasarım**: kod yazmadan önce çözümü ve deneyimi şekillendiriyoruz.
                - **Geliştirme**: görebileceğiniz ve test edebileceğiniz kısa adımlarla ilerliyoruz.
                - **Destek**: sistemlerinizi çalışır, güvenli ve güncel tutuyoruz.
                MD,
        ],
    ];

    public const PARTNERSHIPS = [
        'ar' => [
            'title' => 'الشراكات',
            'slug' => 'الشراكات',
            'summary' => 'نرحّب بالعمل مع شركات تكمّل مهاراتها ومنتجاتها ما نقدّمه، ليحصل العملاء على حلول متكاملة.',
            'body' => <<<'MD'
                ## العمل مع Wasmsoft

                كثيرًا ما تأتي أفضل النتائج من فرق يكمّل بعضها بعضًا. إذا كانت شركتك تقدّم خدمات أو تقنيات تتكامل مع ما نقدّمه، فيسعدنا أن نسمع منك.

                أخبرنا عن شركتك ونوع الشراكة التي تفكّر فيها، وسنرتّب لمحادثة أولى.
                MD,
        ],
        'en' => [
            'title' => 'Partnerships',
            'slug' => 'partnerships',
            'summary' => 'We are open to working with companies whose skills and products complement ours, so clients get complete solutions.',
            'body' => <<<'MD'
                ## Working with Wasmsoft

                The best results often come from teams that complement each other. If your company offers services or technology that work well alongside ours, we would be glad to hear from you.

                Tell us about your company and the kind of partnership you have in mind, and we will arrange a first conversation.
                MD,
        ],
        'tr' => [
            'title' => 'İş ortaklıkları',
            'slug' => 'is-ortakliklari',
            'summary' => 'Müşterilerin eksiksiz çözümler alabilmesi için yetkinlikleri ve ürünleri bizimkileri tamamlayan şirketlerle çalışmaya açığız.',
            'body' => <<<'MD'
                ## Wasmsoft ile çalışmak

                En iyi sonuçlar çoğu zaman birbirini tamamlayan ekiplerden gelir. Şirketiniz bizimkilerle uyumlu hizmetler veya teknolojiler sunuyorsa, sizden haber almaktan memnuniyet duyarız.

                Bize şirketinizden ve aklınızdaki iş birliğinden bahsedin; ilk görüşmeyi birlikte planlayalım.
                MD,
        ],
    ];

    public const TAGLINE = [
        'ar' => 'تطوير البرمجيات والحلول الرقمية',
        'en' => 'Software development and digital solutions',
        'tr' => 'Yazılım geliştirme ve dijital çözümler',
    ];

    public const DESCRIPTION = [
        'ar' => 'تبني Wasmsoft المواقع الإلكترونية وتطبيقات الجوال وحلول الذكاء الاصطناعي وأنظمة إدارة الأعمال، وتدير البنية التحتية السحابية التي تشغّلها.',
        'en' => 'Wasmsoft builds websites, mobile apps, AI solutions and business management systems, and runs the cloud infrastructure behind them.',
        'tr' => 'Wasmsoft web siteleri, mobil uygulamalar, yapay zekâ çözümleri ve iş yönetim sistemleri geliştirir; bunları çalıştıran bulut altyapısını da yönetir.',
    ];

    /** The company's services, in the order it listed them. */
    public const SERVICES = [
        [
            'icon' => 'code',
            'ar' => [
                'title' => 'تطوير المواقع الإلكترونية',
                'summary' => 'نبني مواقع إلكترونية حديثة ومتجاوبة وآمنة تُصمَّم وفق احتياجات أعمالك، من مواقع الشركات ومنصات التجارة الإلكترونية إلى بوابات العملاء وتطبيقات الويب المخصصة.',
                'body' => <<<'MD'
                    غالبًا ما يكون موقعك الإلكتروني أول مكان يلتقي فيه العملاء بأعمالك. نصمم ونطوّر مواقع وتطبيقات ويب سريعة التحميل، تعمل على جميع الشاشات، ويسهل على فريقك إدارتها.

                    ## ما نبنيه

                    - مواقع الشركات التي تعرض شركتك وخدماتك بوضوح
                    - منصات التجارة الإلكترونية مع الكتالوجات والمدفوعات وإدارة الطلبات
                    - بوابات العملاء التي يسجّل فيها العملاء دخولهم ويتابعون طلباتهم وينزّلون مستنداتهم
                    - تطبيقات ويب مخصصة تُبنى حول إجراءات عملك

                    ## كيف نبنيه

                    كل مشروع متجاوب وسهل الوصول، والأمان حاضر فيه منذ البداية. ويمكننا أيضًا إضافة لغات متعددة، بما في ذلك الواجهات من اليمين إلى اليسار للغة العربية.
                    MD,
                'benefits' => ['تصميم متجاوب يعمل على الهواتف والأجهزة اللوحية والحواسيب', 'أمان مدمج منذ السطر الأول من الشيفرة', 'لوحة تحكم لإدارة المحتوى يستخدمها فريقك بسهولة', 'دعم تعدد اللغات والكتابة من اليمين إلى اليسار عند الحاجة'],
            ],
            'en' => [
                'title' => 'Web Development',
                'summary' => 'We build modern, responsive, and secure websites tailored to your business needs, including corporate websites, e-commerce platforms, customer portals, and custom web applications.',
                'body' => <<<'MD'
                    Your website is often the first place customers meet your business. We design and develop sites and web applications that load quickly, work on every screen and are easy for your team to manage.

                    ## What we build

                    - Corporate websites that present your company and services clearly
                    - E-commerce platforms with catalogues, payments and order management
                    - Customer portals where clients sign in, follow their requests and download documents
                    - Custom web applications built around your own processes

                    ## How we build it

                    Every project is responsive, accessible and built with security in mind from the start. We can also add several languages, including right-to-left layouts for Arabic.
                    MD,
                'benefits' => ['A responsive design that works on phones, tablets and desktops', 'Security built in from the first line of code', 'A content management dashboard your team can use', 'Multilingual and right-to-left support when you need it'],
            ],
            'tr' => [
                'title' => 'Web Geliştirme',
                'summary' => 'İşletmenizin ihtiyaçlarına göre modern, duyarlı ve güvenli web siteleri geliştiriyoruz: kurumsal web siteleri, e-ticaret platformları, müşteri portalları ve özel web uygulamaları.',
                'body' => <<<'MD'
                    Web siteniz, müşterilerin işletmenizle ilk tanıştığı yer olabilir. Hızlı açılan, her ekranda çalışan ve ekibinizin kolayca yönetebileceği siteler ve web uygulamaları tasarlıyor ve geliştiriyoruz.

                    ## Neler geliştiriyoruz

                    - Şirketinizi ve hizmetlerinizi açıkça anlatan kurumsal web siteleri
                    - Katalog, ödeme ve sipariş yönetimi içeren e-ticaret platformları
                    - Müşterilerin giriş yapıp taleplerini takip ettiği ve belgelerini indirdiği müşteri portalları
                    - Kendi iş süreçlerinize göre geliştirilen özel web uygulamaları

                    ## Nasıl geliştiriyoruz

                    Her proje duyarlı ve erişilebilir olarak, güvenlik en baştan düşünülerek geliştirilir. Gerektiğinde Arapça için sağdan sola düzen dahil birden fazla dil de ekleyebiliriz.
                    MD,
                'benefits' => ['Telefon, tablet ve bilgisayarda çalışan duyarlı tasarım', 'Kodun ilk satırından itibaren yerleşik güvenlik', 'Ekibinizin kolayca kullanabileceği bir içerik yönetim paneli', 'Gerektiğinde çok dilli ve sağdan sola destek'],
            ],
        ],
        [
            'icon' => 'smartphone',
            'ar' => [
                'title' => 'تطوير تطبيقات الجوال',
                'summary' => 'نطوّر تطبيقات عالية الجودة لنظامي Android وiOS تحوّل أفكارك إلى تجارب جوال موثوقة وسهلة الاستخدام وقابلة للتوسع.',
                'body' => <<<'MD'
                    التطبيق الجيد يبدو بسيطًا لمستخدميه، حتى عندما يحدث الكثير خلف الشاشة. ننقل فكرتك من الرسومات الأولى إلى تطبيق منشور يُقاس أداؤه ويُحسَّن باستمرار.

                    ## ما نقدمه

                    - تطبيقات Android وiOS، أصلية أو متعددة المنصات
                    - تصميم تجربة المستخدم والواجهات للجوال
                    - ربط التطبيق بأنظمتك الحالية عبر واجهات برمجية آمنة
                    - النشر على App Store وGoogle Play، والتحديثات بعد الإطلاق

                    ## مبني للنمو

                    نخطط للتوسع منذ البداية، كي لا تعني الميزات الجديدة وزيادة المستخدمين البدء من جديد.
                    MD,
                'benefits' => ['مشروع واحد لنظامي Android وiOS عندما يناسب ذلك تطبيقك', 'تصميم واضح وسهل الاستخدام يُختبر بمهام حقيقية', 'اتصال آمن بأنظمتك الخلفية', 'دعم في النشر والتحديثات والإصدارات الجديدة'],
            ],
            'en' => [
                'title' => 'Mobile App Development',
                'summary' => 'We develop high-quality Android and iOS applications that transform your ideas into reliable, user-friendly, and scalable mobile experiences.',
                'body' => <<<'MD'
                    A good app feels simple to the people who use it, even when a lot happens behind the screen. We take your idea from the first sketches to an app that is published, measured and improved.

                    ## What we offer

                    - Android and iOS apps, native or cross-platform
                    - User experience and interface design for mobile
                    - Connections to your existing systems through secure APIs
                    - Publishing to the App Store and Google Play, and updates after launch

                    ## Built to grow

                    We plan for growth from the start, so new features and more users do not mean starting over.
                    MD,
                'benefits' => ['One project for both Android and iOS when it suits your app', 'Clear, user-friendly design tested with real tasks', 'Secure connections to your back-end systems', 'Support with publishing, updates and new versions'],
            ],
            'tr' => [
                'title' => 'Mobil Uygulama Geliştirme',
                'summary' => 'Fikirlerinizi güvenilir, kullanıcı dostu ve ölçeklenebilir mobil deneyimlere dönüştüren yüksek kaliteli Android ve iOS uygulamaları geliştiriyoruz.',
                'body' => <<<'MD'
                    İyi bir uygulama, ekranın arkasında çok şey olsa bile kullanıcılarına basit gelir. Fikrinizi ilk eskizlerden yayınlanan, ölçülen ve geliştirilen bir uygulamaya taşıyoruz.

                    ## Neler sunuyoruz

                    - Yerel veya çapraz platform Android ve iOS uygulamaları
                    - Mobil için kullanıcı deneyimi ve arayüz tasarımı
                    - Güvenli API'lerle mevcut sistemlerinize bağlantı
                    - App Store ve Google Play'de yayınlama ve lansman sonrası güncellemeler

                    ## Büyümeye hazır

                    Büyümeyi baştan planlıyoruz; böylece yeni özellikler ve daha fazla kullanıcı, sıfırdan başlamak anlamına gelmez.
                    MD,
                'benefits' => ['Uygulamanıza uygun olduğunda Android ve iOS için tek proje', 'Gerçek görevlerle test edilen açık, kullanıcı dostu tasarım', 'Arka uç sistemlerinize güvenli bağlantılar', 'Yayınlama, güncellemeler ve yeni sürümlerde destek'],
            ],
        ],
        [
            'icon' => 'sparkles',
            'ar' => [
                'title' => 'حلول الذكاء الاصطناعي وتعلّم الآلة',
                'summary' => 'نطوّر حلولًا ذكية باستخدام الذكاء الاصطناعي وتعلّم الآلة لأتمتة العمليات وتحليل البيانات وتوليد التنبؤات ودعم قرارات أعمال أكثر ذكاءً.',
                'body' => <<<'MD'
                    يكون الذكاء الاصطناعي أكثر فائدة عندما يحل مشكلة محددة. نبدأ من بياناتك وعملك اليومي، ونحدد أين يمكن للأتمتة والتنبؤ أن يوفّرا الوقت أو يكشفا ما كان خفيًا.

                    ## ما يمكننا بناؤه

                    - أتمتة المهام المتكررة، مثل فرز المستندات أو الإجابة عن الأسئلة الشائعة
                    - تحليل البيانات ولوحات المعلومات التي تحوّل السجلات إلى رؤى
                    - نماذج تنبؤ بالطلب أو المبيعات أو الصيانة
                    - مساعدات وواجهات محادثة مرتبطة بمعرفتك الخاصة

                    ## مسؤول منذ التصميم

                    نوضّح ما يستطيع النموذج فعله وما لا يستطيع، ونحمي البيانات التي يستخدمها، ونُبقي القرارات المهمة بيد الإنسان.
                    MD,
                'benefits' => ['حلول مبنية حول هدف عمل قابل للقياس', 'بياناتك آمنة وخاصة', 'نماذج تُراقَب وتُحسَّن مع الوقت', 'تفسيرات واضحة يستطيع فريقك العمل بها'],
            ],
            'en' => [
                'title' => 'AI & Machine Learning Solutions',
                'summary' => 'We develop intelligent solutions using artificial intelligence and machine learning to automate processes, analyze data, generate predictions, and support smarter business decisions.',
                'body' => <<<'MD'
                    Artificial intelligence is most useful when it solves a specific problem. We start from your data and your daily work, and find where automation and prediction can save time or reveal what was hidden.

                    ## What we can build

                    - Automation of repetitive tasks, such as sorting documents or answering common questions
                    - Data analysis and dashboards that turn records into insight
                    - Prediction models for demand, sales or maintenance
                    - Assistants and chat interfaces connected to your own knowledge

                    ## Responsible by design

                    We are clear about what a model can and cannot do, protect the data it uses and keep people in control of important decisions.
                    MD,
                'benefits' => ['Solutions built around a measurable business goal', 'Your data kept secure and private', 'Models that are monitored and improved over time', 'Clear explanations your team can act on'],
            ],
            'tr' => [
                'title' => 'Yapay Zekâ ve Makine Öğrenimi Çözümleri',
                'summary' => 'Süreçleri otomatikleştirmek, verileri analiz etmek, tahminler üretmek ve daha akıllı iş kararlarını desteklemek için yapay zekâ ve makine öğrenimiyle akıllı çözümler geliştiriyoruz.',
                'body' => <<<'MD'
                    Yapay zekâ, belirli bir sorunu çözdüğünde en faydalı hâlini alır. Verilerinizden ve günlük işinizden yola çıkıyor, otomasyonun ve tahminin nerede zaman kazandırabileceğini ya da gizli kalanı ortaya çıkarabileceğini buluyoruz.

                    ## Neler geliştirebiliriz

                    - Belgeleri sınıflandırmak veya sık sorulan soruları yanıtlamak gibi tekrarlayan işlerin otomasyonu
                    - Kayıtları içgörüye dönüştüren veri analizi ve paneller
                    - Talep, satış veya bakım için tahmin modelleri
                    - Kendi bilgi birikiminize bağlı asistanlar ve sohbet arayüzleri

                    ## Tasarımdan itibaren sorumlu

                    Bir modelin neyi yapıp neyi yapamayacağını açıkça anlatıyor, kullandığı verileri koruyor ve önemli kararları insanların kontrolünde tutuyoruz.
                    MD,
                'benefits' => ['Ölçülebilir bir iş hedefi etrafında kurulan çözümler', 'Güvenli ve gizli tutulan verileriniz', 'Zamanla izlenen ve geliştirilen modeller', 'Ekibinizin uygulayabileceği açık açıklamalar'],
            ],
        ],
        [
            'icon' => 'layers',
            'ar' => [
                'title' => 'أنظمة تخطيط الموارد وإدارة الأعمال',
                'summary' => 'نصمم وننفذ أنظمة متكاملة لإدارة الأعمال تُبسّط المحاسبة والمخزون والمبيعات والموارد البشرية والعمليات اليومية.',
                'body' => <<<'MD'
                    عندما يحتفظ كل قسم بجداوله الخاصة، تضيع المعلومات ويتكرر العمل. يمنح النظام المتكامل الجميع صورة واحدة ومحدّثة عن الأعمال.

                    ## الوحدات التي ننفذها

                    - المحاسبة والفوترة
                    - إدارة المخزون والمستودعات
                    - المبيعات والمشتريات وإدارة العملاء
                    - الموارد البشرية والرواتب
                    - التقارير ولوحات المعلومات للإدارة

                    ## مُكيَّف لشركتك

                    نكيّف النظام مع إجراءات عملك، وننقل بياناتك الحالية، وندرّب فريقك، ونبقى متاحين بعد التشغيل.
                    MD,
                'benefits' => ['مصدر واحد موثوق للمعلومات لجميع الأقسام', 'عمل يدوي أقل وأخطاء أقل', 'تقارير متاحة متى احتجت إليها', 'تدريب ودعم لفريقك'],
            ],
            'en' => [
                'title' => 'ERP & Business Management Systems',
                'summary' => 'We design and implement integrated business management systems that streamline accounting, inventory, sales, human resources, and daily operations.',
                'body' => <<<'MD'
                    When every department keeps its own spreadsheets, information gets lost and work is done twice. An integrated system gives everyone the same, up-to-date picture of the business.

                    ## Modules we implement

                    - Accounting and invoicing
                    - Inventory and warehouse management
                    - Sales, purchasing and customer management
                    - Human resources and payroll
                    - Reports and dashboards for management

                    ## Fitted to your company

                    We adapt the system to your processes, move your existing data across, train your team and stay available after go-live.
                    MD,
                'benefits' => ['One source of truth for every department', 'Less manual work and fewer errors', 'Reports available whenever you need them', 'Training and support for your team'],
            ],
            'tr' => [
                'title' => 'ERP ve İş Yönetim Sistemleri',
                'summary' => 'Muhasebe, stok, satış, insan kaynakları ve günlük operasyonları kolaylaştıran entegre iş yönetim sistemleri tasarlıyor ve uyguluyoruz.',
                'body' => <<<'MD'
                    Her departman kendi tablolarını tuttuğunda bilgi kaybolur ve işler iki kez yapılır. Entegre bir sistem, herkese işletmenin aynı ve güncel resmini sunar.

                    ## Uyguladığımız modüller

                    - Muhasebe ve faturalama
                    - Stok ve depo yönetimi
                    - Satış, satın alma ve müşteri yönetimi
                    - İnsan kaynakları ve bordro
                    - Yönetim için raporlar ve paneller

                    ## Şirketinize uyarlanmış

                    Sistemi süreçlerinize uyarlıyor, mevcut verilerinizi aktarıyor, ekibinizi eğitiyor ve canlıya geçişten sonra da yanınızda oluyoruz.
                    MD,
                'benefits' => ['Tüm departmanlar için tek doğru bilgi kaynağı', 'Daha az elle iş ve daha az hata', 'İhtiyaç duyduğunuz anda hazır raporlar', 'Ekibiniz için eğitim ve destek'],
            ],
        ],
        [
            'icon' => 'cloud',
            'ar' => [
                'title' => 'الحوسبة السحابية والبنية التحتية لتقنية المعلومات',
                'summary' => 'نساعد الشركات على بناء بيئات تقنية موثوقة وصيانتها، من خلال إعداد الخوادم والنشر السحابي وإدارة قواعد البيانات والبنية التحتية للشبكات وتحسين الأداء.',
                'body' => <<<'MD'
                    تكون التطبيقات موثوقة بقدر موثوقية البنية التحتية التي تعمل عليها. نُعدّ بيئات تبقى سريعة وآمنة ومتاحة، ونتولى رعايتها.

                    ## ما نتولاه

                    - إعداد الخوادم وتهيئتها
                    - النشر على المنصات السحابية والانتقال من الخوادم المحلية
                    - إدارة قواعد البيانات والنسخ الاحتياطي والاستعادة
                    - البنية التحتية للشبكات وأمانها
                    - المراقبة وتحسين الأداء

                    ## جاهزة عندما تنمو

                    نصمم بيئات قابلة للتوسع مع زيادة الزيارات والبيانات، دون أن تدفع مقابل سعة لا تستخدمها.
                    MD,
                'benefits' => ['بيئات موثوقة مع المراقبة والنسخ الاحتياطي', 'تحديثات أمنية تُطبَّق في وقتها', 'تطبيقات أسرع بفضل تحسين الأداء', 'بنية تحتية تتوسع مع أعمالك'],
            ],
            'en' => [
                'title' => 'Cloud & IT Infrastructure',
                'summary' => 'We help businesses build and maintain reliable IT environments through server configuration, cloud deployment, database management, network infrastructure, and performance optimization.',
                'body' => <<<'MD'
                    Applications are only as dependable as the infrastructure they run on. We set up and look after environments that stay fast, secure and available.

                    ## What we take care of

                    - Server setup and configuration
                    - Deployment to cloud platforms and migration from on-premises servers
                    - Database management, backups and recovery
                    - Network infrastructure and security
                    - Monitoring and performance optimization

                    ## Ready when you grow

                    We design environments that can scale with your traffic and data, without paying for capacity you do not use.
                    MD,
                'benefits' => ['Reliable environments with monitoring and backups', 'Security updates applied on time', 'Faster applications through performance tuning', 'Infrastructure that scales with your business'],
            ],
            'tr' => [
                'title' => 'Bulut ve BT Altyapısı',
                'summary' => 'Sunucu yapılandırması, bulut dağıtımı, veritabanı yönetimi, ağ altyapısı ve performans optimizasyonuyla işletmelerin güvenilir BT ortamları kurmasına ve sürdürmesine yardımcı oluyoruz.',
                'body' => <<<'MD'
                    Uygulamalar, üzerinde çalıştıkları altyapı kadar güvenilirdir. Hızlı, güvenli ve erişilebilir kalan ortamlar kuruyor ve bakımını üstleniyoruz.

                    ## Neleri üstleniyoruz

                    - Sunucu kurulumu ve yapılandırması
                    - Bulut platformlarına dağıtım ve yerel sunuculardan geçiş
                    - Veritabanı yönetimi, yedekleme ve kurtarma
                    - Ağ altyapısı ve güvenliği
                    - İzleme ve performans optimizasyonu

                    ## Büyüdüğünüzde hazır

                    Trafiğiniz ve verinizle birlikte ölçeklenebilen, kullanmadığınız kapasite için ödeme yapmanızı gerektirmeyen ortamlar tasarlıyoruz.
                    MD,
                'benefits' => ['İzleme ve yedeklemeyle güvenilir ortamlar', 'Zamanında uygulanan güvenlik güncellemeleri', 'Performans iyileştirmeleriyle daha hızlı uygulamalar', 'İşinizle birlikte ölçeklenen altyapı'],
            ],
        ],
        [
            'icon' => 'trending-up',
            'ar' => [
                'title' => 'الاستشارات التقنية والتحول الرقمي',
                'summary' => 'نقدّم استشارات تقنية استراتيجية تساعد الشركات على اختيار الحلول المناسبة، وتحديث عملياتها، وتحسين سير العمل، وتحقيق نمو رقمي مستدام.',
                'body' => <<<'MD'
                    اختيار التقنية قرار يخص الأعمال. نساعدك على فهم خياراتك، واتخاذ القرار بثقة، والمضي قدمًا خطوة بخطوة.

                    ## كيف نساعدك

                    - مراجعة أنظمتك وإجراءاتك الحالية
                    - خارطة طريق للتحول الرقمي بأولويات واضحة
                    - اختيار البرمجيات والموردين بما يناسب احتياجاتك وميزانيتك
                    - تحسين سير العمل وأتمتة الخطوات اليدوية
                    - مرافقة فريقك خلال التغيير

                    ## نمو يدوم

                    نركّز على تغييرات يستطيع فريقك تبنّيها والمحافظة عليها، لتستمر الفوائد بعد انتهاء المشروع بوقت طويل.
                    MD,
                'benefits' => ['رؤية واضحة لخياراتك التقنية', 'خارطة طريق عملية بأولويات واضحة', 'سير عمل أفضل وعمل يدوي أقل', 'تغيير يستطيع فريقك تبنّيه والمحافظة عليه'],
            ],
            'en' => [
                'title' => 'IT Consulting & Digital Transformation',
                'summary' => 'We provide strategic technology consulting to help businesses choose the right solutions, modernize their operations, improve workflows, and achieve sustainable digital growth.',
                'body' => <<<'MD'
                    Choosing technology is a business decision. We help you understand your options, decide with confidence and move forward step by step.

                    ## How we help

                    - Reviewing your current systems and processes
                    - A digital transformation roadmap with clear priorities
                    - Choosing software and vendors that fit your needs and budget
                    - Improving workflows and automating manual steps
                    - Guiding your team through the change

                    ## Growth that lasts

                    We focus on changes your team can adopt and keep, so the benefits continue long after the project ends.
                    MD,
                'benefits' => ['A clear view of your technology options', 'A practical roadmap with clear priorities', 'Better workflows and less manual work', 'Change your team can adopt and sustain'],
            ],
            'tr' => [
                'title' => 'BT Danışmanlığı ve Dijital Dönüşüm',
                'summary' => 'İşletmelerin doğru çözümleri seçmesine, operasyonlarını modernleştirmesine, iş akışlarını iyileştirmesine ve sürdürülebilir dijital büyümeye ulaşmasına yardımcı olan stratejik teknoloji danışmanlığı sunuyoruz.',
                'body' => <<<'MD'
                    Teknoloji seçmek bir iş kararıdır. Seçeneklerinizi anlamanıza, güvenle karar vermenize ve adım adım ilerlemenize yardımcı oluyoruz.

                    ## Nasıl yardımcı oluyoruz

                    - Mevcut sistemlerinizi ve süreçlerinizi incelemek
                    - Öncelikleri net bir dijital dönüşüm yol haritası
                    - İhtiyaçlarınıza ve bütçenize uygun yazılım ve tedarikçi seçimi
                    - İş akışlarını iyileştirmek ve elle yapılan adımları otomatikleştirmek
                    - Değişim sürecinde ekibinize rehberlik etmek

                    ## Kalıcı büyüme

                    Ekibinizin benimseyip sürdürebileceği değişikliklere odaklanıyoruz; böylece faydalar proje bittikten sonra da devam eder.
                    MD,
                'benefits' => ['Teknoloji seçenekleriniz hakkında net bir bakış', 'Öncelikleri net, uygulanabilir bir yol haritası', 'Daha iyi iş akışları ve daha az elle iş', 'Ekibinizin benimseyip sürdürebileceği değişim'],
            ],
        ],
    ];

    /** Project categories, by key. */
    public const PROJECT_CATEGORIES = [
        'web' => ['ar' => 'منصات الويب', 'en' => 'Web platforms', 'tr' => 'Web platformları'],
        'mobile' => ['ar' => 'تطبيقات الجوال', 'en' => 'Mobile apps', 'tr' => 'Mobil uygulamalar'],
        'business' => ['ar' => 'أنظمة الأعمال', 'en' => 'Business systems', 'tr' => 'İş sistemleri'],
        'ai' => ['ar' => 'حلول الذكاء الاصطناعي', 'en' => 'AI solutions', 'tr' => 'Yapay zekâ çözümleri'],
        'products' => ['ar' => 'منتجاتنا', 'en' => 'Our products', 'tr' => 'Ürünlerimiz'],
    ];

    /** Examples of the work the company does: no client, partner or result is named. */
    public const PROJECTS = [
        [
            'category' => 'web',
            'artwork' => 'web',
            'technologies' => ['Laravel', 'React', 'MySQL'],
            'ar' => [
                'title' => 'منصة مواعيد للعيادات',
                'summary' => 'منصة ويب يحجز فيها المرضى مواعيدهم إلكترونيًا، ويتلقون التذكيرات، ويديرون زياراتهم في عدة فروع.',
                'body' => <<<'MD'
                    ## التحدي

                    كانت المواعيد تُحجز هاتفيًا وتُدوَّن في تقويم منفصل لكل فرع، ما جعل الحجوزات المكررة والزيارات الفائتة أمرًا شائعًا.

                    ## ما بنيناه

                    - حجز إلكتروني مع عرض المواعيد المتاحة لكل طبيب وفرع في الوقت الفعلي
                    - تذكيرات تلقائية عبر البريد الإلكتروني والرسائل النصية
                    - لوحة تحكم يدير منها موظفو الاستقبال جدول اليوم
                    - واجهات باللغتين العربية والإنجليزية

                    ## النتيجة

                    يستطيع المرضى الحجز في أي وقت، ويعمل الموظفون على جدول واحد مشترك ومحدّث.
                    MD,
            ],
            'en' => [
                'title' => 'Clinic appointment platform',
                'summary' => 'A web platform where patients book appointments online, receive reminders and manage their visits across several branches.',
                'body' => <<<'MD'
                    ## The challenge

                    Appointments were taken by phone and kept in a separate calendar for each branch, which made double bookings and missed visits common.

                    ## What we built

                    - Online booking with real-time availability for every doctor and branch
                    - Automatic email and text message reminders
                    - A dashboard where reception staff manage the day's schedule
                    - Arabic and English interfaces

                    ## The outcome

                    Patients can book at any time, and staff work from one shared, up-to-date schedule.
                    MD,
            ],
            'tr' => [
                'title' => 'Klinik randevu platformu',
                'summary' => 'Hastaların çevrim içi randevu aldığı, hatırlatma aldığı ve birden fazla şubedeki ziyaretlerini yönettiği bir web platformu.',
                'body' => <<<'MD'
                    ## Sorun

                    Randevular telefonla alınıyor ve her şube için ayrı bir takvimde tutuluyordu; bu da çakışan randevuları ve kaçırılan ziyaretleri sıradan hâle getiriyordu.

                    ## Ne geliştirdik

                    - Her doktor ve şube için anlık uygunluk bilgisiyle çevrim içi randevu
                    - Otomatik e-posta ve SMS hatırlatmaları
                    - Resepsiyon ekibinin günün programını yönettiği bir panel
                    - Arapça ve İngilizce arayüzler

                    ## Sonuç

                    Hastalar istedikleri zaman randevu alabiliyor, ekip ise tek, ortak ve güncel bir program üzerinden çalışıyor.
                    MD,
            ],
        ],
        [
            'category' => 'mobile',
            'artwork' => 'mobile',
            'technologies' => ['Flutter', 'Laravel', 'PostgreSQL'],
            'ar' => [
                'title' => 'تطبيق تتبع التوصيل',
                'summary' => 'تطبيقات Android وiOS تتيح للعملاء متابعة طلباتهم مباشرة، وتساعد السائقين على تخطيط مساراتهم.',
                'body' => <<<'MD'
                    ## التحدي

                    كان العملاء يتصلون للسؤال عن طلباتهم، وكان السائقون يخططون مساراتهم على الورق.

                    ## ما بنيناه

                    - تطبيق للعملاء يتيح تتبع الطلب مباشرة مع الإشعارات
                    - تطبيق للسائقين يقترح المسارات ويوثّق التسليم
                    - لوحة عمليات لتوزيع الطلبات ومتابعة كل مركبة

                    ## النتيجة

                    يعرف العملاء متى يصل طلبهم، ويرى فريق العمليات كل عمليات التوصيل في مكان واحد.
                    MD,
            ],
            'en' => [
                'title' => 'Delivery tracking app',
                'summary' => 'Android and iOS apps that let customers follow their orders live and help drivers plan their routes.',
                'body' => <<<'MD'
                    ## The challenge

                    Customers called to ask where their orders were, and drivers planned their routes on paper.

                    ## What we built

                    - A customer app with live order tracking and notifications
                    - A driver app with suggested routes and proof of delivery
                    - An operations dashboard to assign orders and follow every vehicle

                    ## The outcome

                    Customers know when to expect their order, and the operations team sees every delivery in one place.
                    MD,
            ],
            'tr' => [
                'title' => 'Teslimat takip uygulaması',
                'summary' => 'Müşterilerin siparişlerini canlı takip etmesini sağlayan ve sürücülerin rotalarını planlamasına yardımcı olan Android ve iOS uygulamaları.',
                'body' => <<<'MD'
                    ## Sorun

                    Müşteriler siparişlerinin nerede olduğunu sormak için arıyor, sürücüler rotalarını kâğıt üzerinde planlıyordu.

                    ## Ne geliştirdik

                    - Canlı sipariş takibi ve bildirimler sunan bir müşteri uygulaması
                    - Rota önerileri ve teslimat kanıtı içeren bir sürücü uygulaması
                    - Siparişleri atamak ve her aracı takip etmek için bir operasyon paneli

                    ## Sonuç

                    Müşteriler siparişlerinin ne zaman geleceğini biliyor, operasyon ekibi ise tüm teslimatları tek bir yerden görüyor.
                    MD,
            ],
        ],
        [
            'category' => 'business',
            'artwork' => 'erp',
            'technologies' => ['Laravel', 'React', 'MySQL'],
            'ar' => [
                'title' => 'نظام تخطيط موارد لشركة تجارية',
                'summary' => 'نظام متكامل يربط المحاسبة والمخزون والمبيعات والمشتريات لشركة جملة لديها عدة مستودعات.',
                'body' => <<<'MD'
                    ## التحدي

                    كان كل قسم يحتفظ بجداوله الخاصة، فنادرًا ما تطابقت مستويات المخزون مع أرقام المبيعات.

                    ## ما بنيناه

                    - محاسبة مع الفوترة ومتابعة المدفوعات
                    - إدارة المخزون في عدة مستودعات، مع التحويلات والجرد
                    - المبيعات والمشتريات مع خطوات الموافقة
                    - تقارير ولوحات معلومات للإدارة

                    ## النتيجة

                    تعمل الأقسام كلها الآن بالأرقام نفسها، محدّثة دائمًا.
                    MD,
            ],
            'en' => [
                'title' => 'ERP system for a trading company',
                'summary' => 'An integrated system connecting accounting, inventory, sales and purchasing for a wholesale business with several warehouses.',
                'body' => <<<'MD'
                    ## The challenge

                    Each department kept its own spreadsheets, so stock levels and sales figures rarely matched.

                    ## What we built

                    - Accounting with invoicing and payment tracking
                    - Inventory across several warehouses, with transfers and stock counts
                    - Sales and purchasing with approval steps
                    - Reports and dashboards for management

                    ## The outcome

                    Every department now works from the same, up-to-date figures.
                    MD,
            ],
            'tr' => [
                'title' => 'Bir ticaret şirketi için ERP sistemi',
                'summary' => 'Birden fazla deposu olan bir toptan satış işletmesi için muhasebe, stok, satış ve satın almayı birbirine bağlayan entegre bir sistem.',
                'body' => <<<'MD'
                    ## Sorun

                    Her departman kendi tablolarını tutuyordu; bu yüzden stok seviyeleri ile satış rakamları nadiren birbirini tutuyordu.

                    ## Ne geliştirdik

                    - Faturalama ve ödeme takibiyle muhasebe
                    - Transferler ve sayımlarla birden fazla depoda stok yönetimi
                    - Onay adımlarıyla satış ve satın alma
                    - Yönetim için raporlar ve paneller

                    ## Sonuç

                    Artık tüm departmanlar aynı ve güncel rakamlarla çalışıyor.
                    MD,
            ],
        ],
        [
            'category' => 'ai',
            'artwork' => 'ai',
            'technologies' => ['Python', 'scikit-learn', 'PostgreSQL'],
            'ar' => [
                'title' => 'لوحة التنبؤ بالمبيعات',
                'summary' => 'نموذج تعلّم آلة ولوحة معلومات يتنبآن بالطلب حسب المنتج والمنطقة، ليخطط قسم المشتريات مسبقًا.',
                'body' => <<<'MD'
                    ## التحدي

                    كانت قرارات الشراء تعتمد على أرقام العام الماضي والخبرة، فنفدت بعض المنتجات وتكدّس بعضها الآخر.

                    ## ما بنيناه

                    - مسارات بيانات تجمع سجل المبيعات من الأنظمة الحالية
                    - نموذج تنبؤ يتعلّم المواسم والاتجاهات
                    - لوحة معلومات تعرض الطلب المتوقع وتنبّه إلى التغيرات غير المعتادة

                    ## النتيجة

                    يخطط فريق المشتريات بتنبؤات يستطيع تفسيرها والوثوق بها.
                    MD,
            ],
            'en' => [
                'title' => 'Sales forecasting dashboard',
                'summary' => 'A machine learning model and dashboard that forecast demand by product and region, so purchasing can plan ahead.',
                'body' => <<<'MD'
                    ## The challenge

                    Purchasing decisions were based on last year's figures and experience, which left some products out of stock and others in surplus.

                    ## What we built

                    - Data pipelines that collect sales history from existing systems
                    - A forecasting model that learns seasonality and trends
                    - A dashboard that shows expected demand and flags unusual changes

                    ## The outcome

                    The purchasing team plans with forecasts it can explain and trust.
                    MD,
            ],
            'tr' => [
                'title' => 'Satış tahmin paneli',
                'summary' => 'Satın alma ekibinin önceden plan yapabilmesi için ürün ve bölgeye göre talebi tahmin eden bir makine öğrenimi modeli ve paneli.',
                'body' => <<<'MD'
                    ## Sorun

                    Satın alma kararları geçen yılın rakamlarına ve deneyime dayanıyordu; bu da bazı ürünlerin tükenmesine, bazılarının ise fazla kalmasına yol açıyordu.

                    ## Ne geliştirdik

                    - Mevcut sistemlerden satış geçmişini toplayan veri akışları
                    - Mevsimselliği ve eğilimleri öğrenen bir tahmin modeli
                    - Beklenen talebi gösteren ve olağandışı değişiklikleri işaretleyen bir panel

                    ## Sonuç

                    Satın alma ekibi, açıklayabildiği ve güvenebildiği tahminlerle plan yapıyor.
                    MD,
            ],
        ],
    ];

    /** The company's products in development: their names, and that they are coming. */
    public const PRODUCTS = [
        [
            'artwork' => 'shield',
            'ar' => [
                'title' => 'ضمان',
                'summary' => 'منتج من منتجات Wasmsoft قيد التطوير حاليًا. سنشارك تفاصيله مع اقتراب موعد إطلاقه.',
                'body' => "ضمان أحد المنتجات التي تطوّرها Wasmsoft داخليًا. سنشارك ما يقدّمه وكيفية الوصول إليه مع اقتراب موعد إطلاقه.\n\nهل تودّ أن تكون أول من يعرف؟ أرسل لنا رسالة واذكر ضمان.",
            ],
            'en' => [
                'title' => 'Dhaman',
                'summary' => 'A Wasmsoft product, currently in development. We will share the details closer to its launch.',
                'body' => "Dhaman is one of the products Wasmsoft is developing in-house. We will share what it does, and how to get access, closer to its launch.\n\nWould you like to hear about it first? Send us a message and mention Dhaman.",
            ],
            'tr' => [
                'title' => 'Dhaman',
                'summary' => 'Şu anda geliştirilmekte olan bir Wasmsoft ürünü. Ayrıntıları lansmanına yaklaştıkça paylaşacağız.',
                'body' => "Dhaman, Wasmsoft'un kendi bünyesinde geliştirdiği ürünlerden biri. Ne yaptığını ve nasıl erişilebileceğini lansmanına yaklaştıkça paylaşacağız.\n\nİlk siz mi haberdar olmak istersiniz? Bize bir mesaj gönderin ve Dhaman'dan bahsedin.",
            ],
        ],
        [
            'artwork' => 'sparkle',
            'ar' => [
                'title' => 'Fantastic',
                'summary' => 'منتج من منتجات Wasmsoft قيد التطوير حاليًا. سنشارك تفاصيله مع اقتراب موعد إطلاقه.',
                'body' => "Fantastic أحد المنتجات التي تطوّرها Wasmsoft داخليًا. سنشارك ما يقدّمه وكيفية الوصول إليه مع اقتراب موعد إطلاقه.\n\nهل تودّ أن تكون أول من يعرف؟ أرسل لنا رسالة واذكر Fantastic.",
            ],
            'en' => [
                'title' => 'Fantastic',
                'summary' => 'A Wasmsoft product, currently in development. We will share the details closer to its launch.',
                'body' => "Fantastic is one of the products Wasmsoft is developing in-house. We will share what it does, and how to get access, closer to its launch.\n\nWould you like to hear about it first? Send us a message and mention Fantastic.",
            ],
            'tr' => [
                'title' => 'Fantastic',
                'summary' => 'Şu anda geliştirilmekte olan bir Wasmsoft ürünü. Ayrıntıları lansmanına yaklaştıkça paylaşacağız.',
                'body' => "Fantastic, Wasmsoft'un kendi bünyesinde geliştirdiği ürünlerden biri. Ne yaptığını ve nasıl erişilebileceğini lansmanına yaklaştıkça paylaşacağız.\n\nİlk siz mi haberdar olmak istersiniz? Bize bir mesaj gönderin ve Fantastic'ten bahsedin.",
            ],
        ],
    ];

    public const POST_CATEGORIES = [
        'news' => ['ar' => 'أخبار الشركة', 'en' => 'Company news', 'tr' => 'Şirket haberleri'],
        'insights' => ['ar' => 'مقالات', 'en' => 'Insights', 'tr' => 'Makaleler'],
    ];

    public const TAGS = [
        'technology' => ['ar' => 'تقنية', 'en' => 'Technology', 'tr' => 'Teknoloji'],
        'transformation' => ['ar' => 'التحول الرقمي', 'en' => 'Digital transformation', 'tr' => 'Dijital dönüşüm'],
    ];

    /**
     * Articles, newest first. `days` is how long ago each was published;
     * the last two (a scheduled article and an author's draft) show the
     * dashboard's publishing states on an empty news section.
     */
    public const POSTS = [
        [
            'category' => 'news',
            'tags' => [],
            'artwork' => 'launch',
            'days' => 2,
            'featured' => true,
            'ar' => [
                'title' => 'مرحبًا بكم في موقع Wasmsoft الجديد',
                'summary' => 'يجمع موقعنا الجديد خدماتنا ومشاريعنا وأخبارنا في مكان واحد، باللغات العربية والإنجليزية والتركية.',
                'body' => <<<'MD'
                    يسعدنا أن نرحّب بكم في موقعنا الجديد. يجمع الموقع كل ما نقوم به: الخدمات التي نقدّمها، والمشاريع التي نعمل عليها، وأخبار فريقنا.

                    ## متاح بثلاث لغات

                    الموقع متاح باللغات العربية والإنجليزية والتركية، مع واجهة من اليمين إلى اليسار للعربية. ويمكنكم تغيير اللغة في أي وقت من أعلى كل صفحة.

                    ## أخبرونا عن مشروعكم

                    أسرع طريقة للتواصل معنا هي النموذج القصير في نهاية الصفحة الرئيسية. أخبرونا قليلًا عمّا تحتاجون إليه، وسنعود إليكم.
                    MD,
            ],
            'en' => [
                'title' => 'Welcome to the new Wasmsoft website',
                'summary' => 'Our new website brings our services, projects and news together, in Arabic, English and Turkish.',
                'body' => <<<'MD'
                    We are happy to welcome you to our new website. It brings together everything we do: the services we offer, the projects we work on and news from our team.

                    ## Available in three languages

                    The website is available in Arabic, English and Turkish, with a right-to-left layout for Arabic. You can switch languages at any time from the top of every page.

                    ## Tell us about your project

                    The quickest way to reach us is the short form at the end of the home page. Tell us a little about what you need, and we will get back to you.
                    MD,
            ],
            'tr' => [
                'title' => 'Yeni Wasmsoft web sitesine hoş geldiniz',
                'summary' => 'Yeni web sitemiz hizmetlerimizi, projelerimizi ve haberlerimizi Arapça, İngilizce ve Türkçe olarak bir araya getiriyor.',
                'body' => <<<'MD'
                    Sizi yeni web sitemizde ağırlamaktan mutluluk duyuyoruz. Site, yaptığımız her şeyi bir araya getiriyor: sunduğumuz hizmetler, üzerinde çalıştığımız projeler ve ekibimizden haberler.

                    ## Üç dilde

                    Web sitesi Arapça, İngilizce ve Türkçe olarak, Arapça için sağdan sola düzenle birlikte sunuluyor. Dili her sayfanın üst kısmından istediğiniz zaman değiştirebilirsiniz.

                    ## Projenizi anlatın

                    Bize ulaşmanın en hızlı yolu, ana sayfanın sonundaki kısa form. İhtiyacınızdan biraz bahsedin, size dönüş yapalım.
                    MD,
            ],
        ],
        [
            'category' => 'news',
            'tags' => [],
            'artwork' => 'products',
            'days' => 9,
            'featured' => false,
            'ar' => [
                'title' => 'منتجاتنا قيد التطوير: ضمان وFantastic',
                'summary' => 'إلى جانب العمل لعملائنا، يطوّر فريقنا منتجين خاصين بنا.',
                'body' => <<<'MD'
                    إلى جانب المشاريع التي نبنيها لعملائنا، يعمل فريقنا على تطوير منتجين خاصين بنا: ضمان وFantastic.

                    ما زال المنتجان قيد التطوير. وسنشارك المزيد عمّا يقدّمه كل منتج، وكيفية الوصول إليه، مع اقتراب موعد إطلاقه.

                    ## كونوا أول من يعرف

                    أرسلوا لنا رسالة عبر نموذج التواصل واذكروا المنتج الذي يهمّكم، وسنبلغكم عند وجود أي جديد.
                    MD,
            ],
            'en' => [
                'title' => 'Our products in development: Dhaman and Fantastic',
                'summary' => 'Alongside our client work, our team is developing two products of its own.',
                'body' => <<<'MD'
                    Alongside the projects we build for our clients, our team is developing two products of its own: Dhaman and Fantastic.

                    Both are still in development. We will share more about what each product does, and how to get access, as they get closer to launch.

                    ## Hear about them first

                    Send us a message through the contact form and mention the product you are interested in. We will let you know when there is news.
                    MD,
            ],
            'tr' => [
                'title' => 'Geliştirdiğimiz ürünler: Dhaman ve Fantastic',
                'summary' => 'Müşterilerimiz için yaptığımız işlerin yanında ekibimiz kendi iki ürününü geliştiriyor.',
                'body' => <<<'MD'
                    Müşterilerimiz için geliştirdiğimiz projelerin yanında ekibimiz kendi iki ürünü üzerinde çalışıyor: Dhaman ve Fantastic.

                    İki ürün de hâlâ geliştirme aşamasında. Lansmana yaklaştıkça her ürünün ne yaptığını ve nasıl erişilebileceğini paylaşacağız.

                    ## İlk siz haberdar olun

                    İletişim formundan bize bir mesaj gönderin ve ilgilendiğiniz ürünü belirtin. Yeni bir gelişme olduğunda size haber verelim.
                    MD,
            ],
        ],
        [
            'category' => 'insights',
            'tags' => ['technology'],
            'artwork' => 'compare',
            'days' => 16,
            'featured' => false,
            'ar' => [
                'title' => 'موقع إلكتروني أم تطبيق ويب: أيهما تحتاج أعمالك؟',
                'summary' => 'كلاهما يعمل في المتصفح، لكنهما يحلّان مشكلات مختلفة. إليك كيف تعرف أيهما يناسب أهدافك.',
                'body' => <<<'MD'
                    يعمل الموقع الإلكتروني وتطبيق الويب كلاهما في المتصفح، لكن كلًّا منهما مبني لمهمة مختلفة.

                    ## الموقع يُعرّف

                    يعرض الموقع الإلكتروني شركتك: من أنتم، وماذا تقدّمون، وكيف يمكن الوصول إليكم. ويعتمد نجاحه على وضوح المحتوى، والظهور في محركات البحث، وسرعة التحميل.

                    ## تطبيق الويب يُنجز العمل

                    يتيح تطبيق الويب للمستخدمين إنجاز مهام: حجز موعد، أو تقديم طلب، أو متابعة معاملة، أو إدارة بيانات. ويحتاج إلى حسابات وصلاحيات وأنظمة خلفية موثوقة.

                    ## أسئلة تساعدك على الاختيار

                    - هل يكتفي الزوار بالقراءة، أم يحتاجون إلى تسجيل الدخول واتخاذ إجراءات؟
                    - هل يجب أن يتصل النظام ببرمجياتك الأخرى؟
                    - من سيحدّث المحتوى، وكم مرة؟

                    تبدأ كثير من الشركات بموقع إلكتروني ثم تضيف إليه ميزات التطبيقات مع نموها. والتخطيط لذلك منذ البداية يجعل الخطوة التالية أسهل.
                    MD,
            ],
            'en' => [
                'title' => 'Website or web application: which one does your business need?',
                'summary' => 'Both live in the browser, but they solve different problems. Here is how to tell which one fits your goals.',
                'body' => <<<'MD'
                    Both run in a web browser, but a website and a web application are built for different jobs.

                    ## A website informs

                    A website presents your company: who you are, what you offer and how to reach you. Its success depends on clear content, good search visibility and fast loading.

                    ## A web application does work

                    A web application lets people complete tasks: book an appointment, place an order, follow a request or manage data. It needs accounts, permissions and a reliable back end.

                    ## Questions to ask

                    - Do visitors only read, or do they need to sign in and act?
                    - Does the system need to connect to your other software?
                    - Who will update the content, and how often?

                    Many businesses start with a website and add application features as they grow. Planning for that from the start makes the next step easier.
                    MD,
            ],
            'tr' => [
                'title' => 'Web sitesi mi, web uygulaması mı: İşletmenizin hangisine ihtiyacı var?',
                'summary' => 'İkisi de tarayıcıda çalışır ama farklı sorunları çözer. Hedeflerinize hangisinin uyduğunu nasıl anlayacağınızı anlatıyoruz.',
                'body' => <<<'MD'
                    Web sitesi de web uygulaması da tarayıcıda çalışır, ancak her biri farklı bir iş için tasarlanır.

                    ## Web sitesi bilgilendirir

                    Web sitesi şirketinizi tanıtır: kim olduğunuzu, ne sunduğunuzu ve size nasıl ulaşılacağını. Başarısı açık içeriğe, arama motorlarındaki görünürlüğe ve hızlı açılmaya bağlıdır.

                    ## Web uygulaması iş görür

                    Web uygulaması, kullanıcıların işlerini tamamlamasını sağlar: randevu almak, sipariş vermek, bir talebi takip etmek ya da veri yönetmek. Hesaplara, yetkilere ve güvenilir bir arka uca ihtiyaç duyar.

                    ## Kendinize sorun

                    - Ziyaretçiler yalnızca okuyor mu, yoksa giriş yapıp işlem yapmaları mı gerekiyor?
                    - Sistemin diğer yazılımlarınıza bağlanması gerekiyor mu?
                    - İçeriği kim, ne sıklıkla güncelleyecek?

                    Birçok işletme bir web sitesiyle başlar ve büyüdükçe uygulama özellikleri ekler. Bunu baştan planlamak bir sonraki adımı kolaylaştırır.
                    MD,
            ],
        ],
        [
            'category' => 'insights',
            'tags' => ['transformation'],
            'artwork' => 'modules',
            'days' => 23,
            'featured' => false,
            'ar' => [
                'title' => 'خمس علامات تدل على أن أعمالك جاهزة لنظام تخطيط الموارد',
                'summary' => 'الجداول تؤدي الغرض جيدًا، إلى أن تتوقف عن ذلك. هذه العلامات تدل على أن الوقت قد حان لنظام متكامل.',
                'body' => <<<'MD'
                    تبدأ معظم الشركات بالجداول والأدوات المنفصلة. ومع نمو الأعمال تبدأ الثغرات بالظهور.

                    ## 1. البيانات نفسها تُدخل مرتين

                    عندما تُسجَّل المبيعات والمخزون والفواتير في أماكن مختلفة، تتبعها الأخطاء والتأخير.

                    ## 2. لا أحد يثق بالأرقام

                    عندما لا تتطابق تقارير الأقسام المختلفة، يتباطأ اتخاذ القرار.

                    ## 3. إغلاق نهاية الشهر يستغرق وقتًا طويلًا

                    لا ينبغي أن يعني إغلاق الحسابات أيامًا من جمع الملفات.

                    ## 4. المخزون مفاجأة دائمة

                    إذا كنت لا تعرف بنقص البضاعة إلا عندما يطلبها العميل، فالمخزون يحتاج إلى نظام أفضل.

                    ## 5. النمو يضيف عملًا بدل أن يضيف قيمة

                    لا ينبغي أن تضاعف الفروع أو المنتجات أو الموظفون الجدد العمل اليدوي.

                    يربط نظام تخطيط الموارد هذه الجوانب معًا، ليعمل الجميع بالمعلومات نفسها.
                    MD,
            ],
            'en' => [
                'title' => 'Five signs your business is ready for an ERP system',
                'summary' => 'Spreadsheets work well, until they don’t. These signs show it may be time for an integrated system.',
                'body' => <<<'MD'
                    Most companies start with spreadsheets and separate tools. As the business grows, the cracks start to show.

                    ## 1. The same data is entered twice

                    When sales, stock and invoices are recorded in different places, mistakes and delays follow.

                    ## 2. Nobody trusts the numbers

                    When reports from different departments do not match, decisions slow down.

                    ## 3. Month-end takes too long

                    Closing the books should not mean days of collecting files.

                    ## 4. Stock is a surprise

                    If you only learn about a shortage when a customer orders, your inventory needs a better system.

                    ## 5. Growth adds work instead of value

                    New branches, products or staff should not multiply manual work.

                    An ERP system connects these areas, so that everyone works from the same information.
                    MD,
            ],
            'tr' => [
                'title' => 'İşletmenizin ERP sistemine hazır olduğunu gösteren beş işaret',
                'summary' => 'Tablolar iyi çalışır; ta ki çalışmayana kadar. Bu işaretler entegre bir sistemin zamanının geldiğini gösteriyor.',
                'body' => <<<'MD'
                    Çoğu şirket tablolar ve ayrı araçlarla başlar. İşletme büyüdükçe çatlaklar görünmeye başlar.

                    ## 1. Aynı veri iki kez giriliyor

                    Satışlar, stok ve faturalar farklı yerlerde kaydedildiğinde hatalar ve gecikmeler kaçınılmaz olur.

                    ## 2. Kimse rakamlara güvenmiyor

                    Farklı departmanların raporları birbirini tutmadığında kararlar yavaşlar.

                    ## 3. Ay sonu kapanışı çok uzun sürüyor

                    Defterleri kapatmak, günlerce dosya toplamak anlamına gelmemeli.

                    ## 4. Stok sürekli sürpriz

                    Eksikleri ancak müşteri sipariş verdiğinde öğreniyorsanız, stok yönetiminiz daha iyi bir sisteme ihtiyaç duyuyor demektir.

                    ## 5. Büyüme değer yerine iş ekliyor

                    Yeni şubeler, ürünler veya çalışanlar elle yapılan işleri katlamamalı.

                    Bir ERP sistemi bu alanları birbirine bağlar; böylece herkes aynı bilgiyle çalışır.
                    MD,
            ],
        ],
        [
            'category' => 'insights',
            'tags' => ['technology', 'transformation'],
            'artwork' => 'network',
            'days' => 31,
            'featured' => false,
            'ar' => [
                'title' => 'من أين تبدأ مع الذكاء الاصطناعي في الشركات الصغيرة والمتوسطة',
                'summary' => 'لا تحتاج إلى فريق أبحاث كي تستفيد من الذكاء الاصطناعي. ابدأ بمشكلة واحدة واضحة.',
                'body' => <<<'MD'
                    قد يبدو الذكاء الاصطناعي مشروعًا للشركات الكبيرة فقط. لكن كثيرًا من تطبيقاته المفيدة صغيرة ومركّزة.

                    ## ابدأ بالمشكلة لا بالتقنية

                    ابحث عن المهام المتكررة، أو التي تتبع قواعد واضحة، أو تعتمد على قراءة كميات كبيرة من البيانات، مثل فرز الرسائل أو مراجعة المستندات أو التنبؤ بالطلب.

                    ## تحقق من بياناتك

                    النتائج الجيدة تحتاج إلى بيانات جيدة. تأكد من أن معلوماتك دقيقة، ويمكن الوصول إليها، ويُسمح باستخدامها لهذا الغرض.

                    ## قِس النتيجة

                    اتفق مسبقًا على كيفية الحكم على النجاح، مثل الوقت الذي يُوفَّر أو تقليل الأخطاء، وجرّب الحل على نطاق صغير قبل تعميمه.

                    ## أبقِ الإنسان في الصورة

                    أفضل الحلول تدعم فريقك بدل أن تحل محل حكمه.
                    MD,
            ],
            'en' => [
                'title' => 'Where to start with AI in a small or medium business',
                'summary' => 'You do not need a research team to benefit from artificial intelligence. Start with one clear problem.',
                'body' => <<<'MD'
                    Artificial intelligence can sound like a project for large companies only. In practice, many useful applications are small and focused.

                    ## Start with a problem, not a technology

                    Look for tasks that are repetitive, follow clear rules or depend on reading large amounts of data, such as sorting messages, checking documents or forecasting demand.

                    ## Check your data

                    Good results need good data. Make sure the information you have is accurate, accessible and allowed to be used for the purpose.

                    ## Measure the result

                    Agree in advance how you will judge success, for example time saved or fewer errors, and test with a small pilot before rolling out.

                    ## Keep people involved

                    The best solutions support your team rather than replace its judgement.
                    MD,
            ],
            'tr' => [
                'title' => 'Küçük ve orta ölçekli işletmelerde yapay zekâya nereden başlamalı?',
                'summary' => 'Yapay zekâdan yararlanmak için bir araştırma ekibine ihtiyacınız yok. Net bir sorunla başlayın.',
                'body' => <<<'MD'
                    Yapay zekâ yalnızca büyük şirketlere göreymiş gibi görünebilir. Oysa faydalı uygulamaların çoğu küçük ve odaklıdır.

                    ## Teknolojiyle değil, sorunla başlayın

                    Mesajları sınıflandırmak, belgeleri kontrol etmek veya talebi tahmin etmek gibi tekrarlayan, net kurallara dayanan ya da çok miktarda veri okumayı gerektiren işleri arayın.

                    ## Verilerinizi kontrol edin

                    İyi sonuçlar iyi veri ister. Elinizdeki bilgilerin doğru, erişilebilir ve bu amaçla kullanılmasına izin verilen bilgiler olduğundan emin olun.

                    ## Sonucu ölçün

                    Başarıyı nasıl değerlendireceğinizi, örneğin kazanılan zamanı veya azalan hataları, önceden belirleyin ve yaygınlaştırmadan önce küçük bir pilotla deneyin.

                    ## İnsanları sürecin içinde tutun

                    En iyi çözümler ekibinizin muhakemesinin yerini almaz, onu destekler.
                    MD,
            ],
        ],
        [
            'category' => 'insights',
            'tags' => ['technology'],
            'artwork' => 'cloud',
            'days' => 38,
            'featured' => false,
            'ar' => [
                'title' => 'السحابة أم خوادمك الخاصة؟ أسئلة تطرحها قبل الانتقال',
                'summary' => 'قد يخفّض الانتقال إلى السحابة التكاليف ويحسّن الموثوقية، لكن فقط مع خطة صحيحة.',
                'body' => <<<'MD'
                    تسهّل المنصات السحابية بدء تطبيقاتك وتوسيعها وحمايتها، لكن الانتقال إليها قرار يستحق التخطيط.

                    ## ماذا تشغّل اليوم؟

                    اكتب قائمة بتطبيقاتك وقواعد بياناتك والروابط بينها. بعضها يمكن نقله كما هو، وبعضها يحتاج إلى تعديلات أولًا.

                    ## ماذا تحتاج منها؟

                    التوفر والأداء وموقع البيانات والميزانية، كلها تحدد الإعداد المناسب.

                    ## من سيتولى رعايتها؟

                    التحديثات والنسخ الاحتياطي والمراقبة والأمان لا تختفي في السحابة، بل يتغير شكلها. تأكد من وجود مسؤول عنها.

                    ## انتقل خطوة بخطوة

                    ابدأ بنظام مهم لكنه ليس حرجًا، وتعلّم منه، ثم انقل البقية.
                    MD,
            ],
            'en' => [
                'title' => 'Cloud or your own servers? Questions to ask before you move',
                'summary' => 'Moving to the cloud can reduce costs and improve reliability, but only with the right plan.',
                'body' => <<<'MD'
                    Cloud platforms make it easier to start, scale and protect your applications, but moving is a decision worth planning.

                    ## What do you run today?

                    List your applications, your databases and the connections between them. Some can move as they are; others need changes first.

                    ## What do you need from it?

                    Availability, performance, where your data is kept and your budget all shape the right setup.

                    ## Who will look after it?

                    Updates, backups, monitoring and security do not disappear in the cloud; they change shape. Make sure someone is responsible for them.

                    ## Move step by step

                    Start with a system that matters but is not critical, learn from it, then move the rest.
                    MD,
            ],
            'tr' => [
                'title' => 'Bulut mu, kendi sunucularınız mı? Geçmeden önce sorulacak sorular',
                'summary' => 'Buluta geçmek maliyetleri düşürebilir ve güvenilirliği artırabilir; ama ancak doğru bir planla.',
                'body' => <<<'MD'
                    Bulut platformları uygulamalarınızı başlatmayı, ölçeklemeyi ve korumayı kolaylaştırır; ancak geçiş, planlamaya değer bir karardır.

                    ## Bugün neler çalıştırıyorsunuz?

                    Uygulamalarınızı, veritabanlarınızı ve aralarındaki bağlantıları listeleyin. Bazıları olduğu gibi taşınabilir, bazıları önce değişiklik gerektirir.

                    ## Ondan ne bekliyorsunuz?

                    Erişilebilirlik, performans, verilerinizin nerede tutulduğu ve bütçeniz doğru kurulumu belirler.

                    ## Bakımını kim üstlenecek?

                    Güncellemeler, yedekler, izleme ve güvenlik bulutta ortadan kalkmaz; yalnızca biçim değiştirir. Bunlardan birinin sorumlu olduğundan emin olun.

                    ## Adım adım geçin

                    Önemli ama kritik olmayan bir sistemle başlayın, ondan öğrenin, sonra geri kalanını taşıyın.
                    MD,
            ],
        ],
    ];

    /** Shown on an empty news section only: a scheduled article and an author's draft. */
    public const SCHEDULED_POST = [
        'category' => 'insights',
        'tags' => ['technology'],
        'artwork' => 'access',
        'ar' => [
            'title' => 'اجعل موقعك متاحًا للجميع',
            'summary' => 'المواقع سهلة الوصول أسهل استخدامًا لكل زائر. إليك ما تتحقق منه أولًا.',
            'body' => <<<'MD'
                يمكن للجميع استخدام الموقع سهل الوصول، بمن فيهم من يعتمدون على لوحة المفاتيح أو قارئ الشاشة أو النص المكبّر.

                ## ابدأ بالأساسيات

                - نص يتباين بوضوح مع خلفيته
                - وصف نصي بديل لكل صورة
                - نماذج بتسميات ظاهرة ورسائل خطأ واضحة
                - إمكانية استخدام كل شيء بلوحة المفاتيح وحدها

                وتُحسّن سهولة الوصول أيضًا الظهور في محركات البحث، وتجعل موقعك أسهل استخدامًا على الشاشات الصغيرة.
                MD,
        ],
        'en' => [
            'title' => 'Making your website accessible to everyone',
            'summary' => 'Accessible websites are easier to use for every visitor. Here is what to check first.',
            'body' => <<<'MD'
                An accessible website can be used by everyone, including people who rely on a keyboard, a screen reader or larger text.

                ## Start with the basics

                - Text that contrasts clearly with its background
                - A text alternative for every image
                - Forms with visible labels and clear error messages
                - Everything usable with the keyboard alone

                Accessibility also improves search visibility and makes your site easier to use on small screens.
                MD,
        ],
        'tr' => [
            'title' => 'Web sitenizi herkes için erişilebilir kılın',
            'summary' => 'Erişilebilir web siteleri her ziyaretçi için daha kolay kullanılır. İlk olarak kontrol etmeniz gerekenler.',
            'body' => <<<'MD'
                Erişilebilir bir web sitesini klavyeye, ekran okuyucuya ya da büyük metne ihtiyaç duyanlar dahil herkes kullanabilir.

                ## Temellerle başlayın

                - Arka planıyla belirgin bir kontrast oluşturan metinler
                - Her görsel için bir metin alternatifi
                - Görünür etiketleri ve açık hata mesajları olan formlar
                - Yalnızca klavyeyle kullanılabilen her şey

                Erişilebilirlik, arama motorlarındaki görünürlüğü de artırır ve sitenizi küçük ekranlarda daha kullanışlı hâle getirir.
                MD,
        ],
    ];

    public const DRAFT_POST = [
        'category' => 'news',
        'tags' => [],
        'artwork' => 'design',
        'ar' => [
            'title' => 'كيف نخطط لمشروع جديد',
            'summary' => 'نظرة على الأسابيع الأولى من المشروع، من أول محادثة إلى أول إصدار.',
            'body' => <<<'MD'
                يبدأ كل مشروع بمحادثة حول الأهداف والمستخدمين والقيود. ومن هناك نتفق على نسخة أولى يمكن بناؤها واختبارها وتحسينها.

                ## الأسابيع الأولى

                - ورش عمل لفهم الاحتياجات مع فريقك
                - مخططات أولية ونموذج تفاعلي
                - خطة للإصدار الأول
                MD,
        ],
        'en' => [
            'title' => 'How we plan a new project',
            'summary' => 'A look at the first weeks of a project, from the first conversation to the first release.',
            'body' => <<<'MD'
                Every project starts with a conversation about goals, users and constraints. From there we agree on a first version that can be built, tested and improved.

                ## The first weeks

                - Discovery workshops with your team
                - Wireframes and a clickable prototype
                - A plan for the first release
                MD,
        ],
        'tr' => [
            'title' => 'Yeni bir projeyi nasıl planlıyoruz',
            'summary' => 'İlk görüşmeden ilk sürüme, bir projenin ilk haftalarına bakış.',
            'body' => <<<'MD'
                Her proje hedefler, kullanıcılar ve kısıtlar üzerine bir görüşmeyle başlar. Ardından geliştirilip test edilebilecek ve iyileştirilebilecek bir ilk sürümde uzlaşırız.

                ## İlk haftalar

                - Ekibinizle keşif atölyeleri
                - Tel çerçeveler ve tıklanabilir bir prototip
                - İlk sürüm için bir plan
                MD,
        ],
    ];

    /**
     * Highlights by group: reasons to choose the company (home page), values
     * (about page), true figures (home page) and ways to partner.
     */
    public const DIFFERENTIATORS = [
        ['icon' => 'users', 'title' => ['ar' => 'فريق واحد من الفكرة إلى الإطلاق', 'en' => 'One team from idea to launch', 'tr' => 'Fikirden lansmana tek ekip'], 'description' => [
            'ar' => 'الاستراتيجية والتصميم والتطوير والبنية التحتية في مكان واحد، ليبقى مشروعك في تقدّم مستمر.',
            'en' => 'Strategy, design, development and infrastructure in one place, so your project keeps moving.',
            'tr' => 'Strateji, tasarım, geliştirme ve altyapı tek yerde; projeniz hiç durmadan ilerler.',
        ]],
        ['icon' => 'shield-check', 'title' => ['ar' => 'الأمان أولًا', 'en' => 'Security first', 'tr' => 'Önce güvenlik'], 'description' => [
            'ar' => 'ندمج الأمان في كل طبقة، من الشيفرة إلى الخوادم التي تعمل عليها.',
            'en' => 'We build security into every layer, from the code to the servers it runs on.',
            'tr' => 'Güvenliği koddan üzerinde çalıştığı sunuculara kadar her katmana yerleştiriyoruz.',
        ]],
        ['icon' => 'globe', 'title' => ['ar' => 'مبني لثلاث لغات', 'en' => 'Built for three languages', 'tr' => 'Üç dil için tasarlandı'], 'description' => [
            'ar' => 'العربية والإنجليزية والتركية، مع واجهات من اليمين إلى اليسار منفّذة كما ينبغي.',
            'en' => 'Arabic, English and Turkish, with right-to-left layouts done properly.',
            'tr' => 'Arapça, İngilizce ve Türkçe; sağdan sola düzenler de eksiksiz.',
        ]],
    ];

    public const VALUES = [
        ['icon' => 'award', 'title' => ['ar' => 'الجودة', 'en' => 'Quality', 'tr' => 'Kalite'], 'description' => [
            'ar' => 'نختبر عملنا بعناية ونهتم بأدق التفاصيل.',
            'en' => 'We test our work carefully and take pride in the details.',
            'tr' => 'İşimizi özenle test ediyor, ayrıntılarla gurur duyuyoruz.',
        ]],
        ['icon' => 'message-square', 'title' => ['ar' => 'الشفافية', 'en' => 'Transparency', 'tr' => 'Şeffaflık'], 'description' => [
            'ar' => 'خطط واضحة وتقديرات صادقة وتحديثات منتظمة.',
            'en' => 'Clear plans, honest estimates and regular updates.',
            'tr' => 'Net planlar, dürüst tahminler ve düzenli güncellemeler.',
        ]],
        ['icon' => 'heart', 'title' => ['ar' => 'شراكة طويلة الأمد', 'en' => 'Long-term partnership', 'tr' => 'Uzun vadeli ortaklık'], 'description' => [
            'ar' => 'نبقى مع عملائنا بعد الإطلاق، مع نمو احتياجاتهم.',
            'en' => 'We stay with our clients after launch, as their needs grow.',
            'tr' => 'Lansmandan sonra da müşterilerimizin yanındayız, ihtiyaçları büyüdükçe.',
        ]],
    ];

    /** Only figures that are true today: the company's services, the site's languages and its products. */
    public const FIGURES = [
        ['figure' => '6', 'title' => ['ar' => 'خدمات أساسية', 'en' => 'Core services', 'tr' => 'Temel hizmet']],
        ['figure' => '3', 'title' => ['ar' => 'لغات مدعومة', 'en' => 'Languages supported', 'tr' => 'Desteklenen dil']],
        ['figure' => '2', 'title' => ['ar' => 'منتجات قيد التطوير', 'en' => 'Products in development', 'tr' => 'Geliştirilen ürün']],
    ];

    public const PARTNERSHIP_MODELS = [
        ['icon' => 'code', 'title' => ['ar' => 'شركاء التقنية', 'en' => 'Technology partners', 'tr' => 'Teknoloji ortakları'], 'description' => [
            'ar' => 'مزوّدو البرمجيات والمنصات الذين يمكننا دمج منتجاتهم وتنفيذها لعملائنا.',
            'en' => 'Software and platform providers whose products we can integrate and implement for clients.',
            'tr' => 'Ürünlerini müşteriler için entegre edip uygulayabileceğimiz yazılım ve platform sağlayıcıları.',
        ]],
        ['icon' => 'message-square', 'title' => ['ar' => 'شركاء الإحالة', 'en' => 'Referral partners', 'tr' => 'Yönlendirme ortakları'], 'description' => [
            'ar' => 'الوكالات والمستشارون الذين يعرّفوننا بعملاء يحتاجون إلى تطوير البرمجيات.',
            'en' => 'Agencies and consultants who introduce clients that need software development.',
            'tr' => 'Yazılım geliştirmeye ihtiyaç duyan müşterileri bizimle tanıştıran ajanslar ve danışmanlar.',
        ]],
        ['icon' => 'users', 'title' => ['ar' => 'شركاء التنفيذ', 'en' => 'Delivery partners', 'tr' => 'Uygulama ortakları'], 'description' => [
            'ar' => 'فرق متخصصة تعمل إلى جانبنا في المشاريع الأكبر.',
            'en' => 'Specialist teams who work alongside us on larger projects.',
            'tr' => 'Büyük projelerde yanımızda çalışan uzman ekipler.',
        ]],
    ];
}
