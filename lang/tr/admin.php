<?php

// Dashboard interface strings. Website content itself is stored in the database.

return [
    'meta_title' => ':site yönetim paneli',

    'nav' => [
        'label' => 'Panel menüsü',
        'open' => 'Menüyü aç',
        'close' => 'Menüyü kapat',
        'new_items' => 'yeni',
        'overview' => 'Genel bakış',
        'pages' => 'Sayfalar',
        'services' => 'Hizmetler',
        'projects' => 'Projeler',
        'posts' => 'Haberler',
        'highlights' => 'Öne çıkanlar',
        'testimonials' => 'Müşteri yorumları',
        'team' => 'Ekip',
        'milestones' => 'Kilometre taşları',
        'media' => 'Medya kütüphanesi',
        'inquiries' => 'Talepler',
        'navigation' => 'Menüler',
        'settings' => 'Ayarlar',
        'users' => 'Kullanıcılar',
        'audit' => 'Denetim kaydı',
        'groups' => [
            'content' => 'İçerik',
            'library' => 'Kütüphane',
            'inbox' => 'Gelen kutusu',
            'site' => 'Web sitesi',
            'administration' => 'Yönetim',
        ],
    ],

    'topbar' => [
        'view_site' => 'Siteyi görüntüle',
        'account' => 'Hesap menüsü',
        'profile' => 'Profiliniz',
        'sign_out' => 'Çıkış yap',
        'language' => 'Panel dili',
    ],

    'auth' => [
        'sign_in' => 'Giriş yap',
        'sign_in_title' => 'Yönetim paneline giriş yapın',
        'sign_in_intro' => 'Panel hesabınızın e-posta adresini ve parolasını kullanın.',
        'email' => 'E-posta adresi',
        'password' => 'Parola',
        'remember' => 'Bu cihazda oturumum açık kalsın',
        'forgot' => 'Parolanızı mı unuttunuz?',
        'forgot_title' => 'Parolanızı sıfırlayın',
        'forgot_intro' => 'Hesabınızın e-posta adresini girin; yeni bir parola belirlemeniz için size bir bağlantı gönderelim.',
        'send_link' => 'Sıfırlama bağlantısı gönder',
        'link_sent' => 'Bu adresi kullanan etkin bir hesap varsa parola sıfırlama bağlantısı yolda. Birkaç dakika içinde gelen kutunuzu kontrol edin.',
        'back_to_sign_in' => 'Giriş sayfasına dön',
        'reset_title' => 'Yeni bir parola belirleyin',
        'reset_intro' => 'Başka hiçbir yerde kullanmadığınız güçlü bir parola seçin.',
        'new_password' => 'Yeni parola',
        'confirm_password' => 'Yeni parola (tekrar)',
        'password_rules' => 'En az 12 karakter; büyük ve küçük harf ile en az bir rakam içermelidir.',
        'reset_submit' => 'Yeni parolayı kaydet',
        'signed_out' => 'Oturumunuz kapatıldı.',
        'deactivated' => 'Bu hesap devre dışı bırakıldı. Lütfen bir yöneticiyle iletişime geçin.',
    ],

    'mail' => [
        'greeting' => 'Merhaba :name,',
        'expiry' => 'Güvenliğiniz için bu bağlantının süresi :minutes dakika sonra dolar.',
        'reset' => [
            'subject' => ':site yönetim paneli parolanızı sıfırlayın',
            'intro' => ':site yönetim paneli hesabınızın parolasını sıfırlamak için bir talep aldık.',
            'action' => 'Yeni parola belirle',
            'ignore' => 'Bu talebi siz yapmadıysanız bu e-postayı yok sayabilirsiniz; parolanız değişmeyecek.',
        ],
        'invitation' => [
            'subject' => ':site yönetim paneli hesabınız',
            'intro' => 'Bir yönetici sizin için :site yönetim panelinde bir hesap oluşturdu. Kullanmaya başlamak için bir parola belirleyin.',
            'action' => 'Parolanızı belirleyin',
            'ignore' => 'Bu daveti beklemiyorsanız bu e-postayı yok sayabilirsiniz.',
        ],
    ],

    'roles' => [
        'administrator' => [
            'label' => 'Yönetici',
            'description' => 'Kullanıcılar, roller ve site ayarları dahil tam erişim.',
        ],
        'editor' => [
            'label' => 'Editör',
            'description' => 'Tüm site içeriğini, medyayı, menüleri ve talepleri yönetir.',
        ],
        'author' => [
            'label' => 'Yazar',
            'description' => 'Haber yazar ve medya yükler. Haberleri editörler inceleyip yayımlar.',
        ],
        'viewer' => [
            'label' => 'İzleyici',
            'description' => 'Paneldeki site içeriğine salt okunur erişim.',
        ],
    ],

    'overview' => [
        'welcome' => 'Hoş geldiniz, :name',
        'intro' => 'Site içeriğini tüm dillerde tek bir yerden yönetin.',
        'your_role' => 'Rolünüz',
    ],

    'status' => [
        'draft' => 'Taslak',
        'published' => 'Yayında',
        'archived' => 'Arşivlendi',
    ],

    'pages' => [
        'templates' => [
            'default' => 'Standart sayfa',
            'about' => 'Hakkımızda sayfası (değerler, ekip ve kilometre taşlarıyla)',
        ],
        'system' => [
            'home' => 'Ana sayfa',
            'services' => 'Hizmetler sayfası',
            'projects' => 'Projeler sayfası',
            'news' => 'Haberler sayfası',
            'contact' => 'İletişim sayfası',
        ],
    ],

    'form' => [
        'errors_title' => 'Lütfen işaretli alanları düzeltin',
        'errors_count' => '{1} Bir alanın düzeltilmesi gerekiyor.|[2,*] :count alanın düzeltilmesi gerekiyor.',
    ],

    'audit' => [
        'events' => [
            'auth_login' => 'Giriş yapıldı',
            'auth_login_failed' => 'Başarısız giriş denemesi',
            'auth_lockout' => 'Tekrarlanan başarısız denemeler nedeniyle giriş kilitlendi',
            'auth_logout' => 'Çıkış yapıldı',
            'auth_password_reset' => 'Parola sıfırlandı',
            'auth_password_changed' => 'Parola değiştirildi',
            'user_created' => 'Kullanıcı oluşturuldu',
            'user_updated' => 'Kullanıcı güncellendi',
            'user_role_changed' => 'Kullanıcı rolü değiştirildi',
            'user_activated' => 'Kullanıcı etkinleştirildi',
            'user_deactivated' => 'Kullanıcı devre dışı bırakıldı',
            'user_deleted' => 'Kullanıcı silindi',
            'user_invitation_sent' => 'Davet gönderildi',
            'settings_updated' => 'Ayarlar güncellendi',
            'navigation_updated' => 'Menüler güncellendi',
            'content_created' => 'İçerik oluşturuldu',
            'content_updated' => 'İçerik güncellendi',
            'content_published' => 'İçerik yayımlandı',
            'content_unpublished' => 'İçerik yayından kaldırıldı',
            'content_archived' => 'İçerik arşivlendi',
            'content_deleted' => 'İçerik silindi',
            'media_uploaded' => 'Medya yüklendi',
            'media_updated' => 'Medya güncellendi',
            'media_deleted' => 'Medya silindi',
            'inquiry_status_changed' => 'Talep durumu değiştirildi',
            'inquiry_deleted' => 'Talep silindi',
        ],
    ],
];
