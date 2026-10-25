<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The default error messages used by the validator class.
    |
    */

    'accepted' => ':attribute alanı kabul edilmelidir.',
    'accepted_if' => ':other alanı :value olduğunda :attribute alanı kabul edilmelidir.',
    'active_url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'after' => ':attribute alanı :date tarihinden sonra bir tarih olmalıdır.',
    'after_or_equal' => ':attribute alanı :date veya sonrası bir tarih olmalıdır.',
    'alpha' => ':attribute alanı yalnızca harf içerebilir.',
    'alpha_dash' => ':attribute alanı yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
    'alpha_num' => ':attribute alanı yalnızca harf ve rakam içerebilir.',
    'any_of' => ':attribute alanı geçersiz.',
    'array' => ':attribute alanı bir dizi olmalıdır.',
    'array_keys' => ':attribute alanı yalnızca şu anahtarları içerebilir: :values.',
    'ascii' => ':attribute alanı yalnızca tek baytlık alfanümerik karakterler ve semboller içerebilir.',
    'base64' => ':attribute alanı geçerli bir Base64 metni olmalıdır.',
    'before' => ':attribute alanı :date tarihinden önce bir tarih olmalıdır.',
    'before_or_equal' => ':attribute alanı :date veya öncesi bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute alanı :min ile :max arasında öğe içermelidir.',
        'file' => ':attribute alanı :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute alanı :min ile :max arasında olmalıdır.',
        'string' => ':attribute alanı :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'can' => ':attribute alanı yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute alanının onayı eşleşmiyor.',
    'contains' => ':attribute alanında gerekli bir değer eksik.',
    'current_password' => 'Parola hatalı.',
    'date' => ':attribute alanı geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute alanı :date ile aynı tarih olmalıdır.',
    'date_format' => ':attribute alanı :format biçimine uymalıdır.',
    'decimal' => ':attribute alanı :decimal ondalık basamak içermelidir.',
    'declined' => ':attribute alanı reddedilmelidir.',
    'declined_if' => ':other alanı :value olduğunda :attribute alanı reddedilmelidir.',
    'different' => ':attribute alanı ile :other farklı olmalıdır.',
    'digits' => ':attribute alanı :digits basamaklı olmalıdır.',
    'digits_between' => ':attribute alanı :min ile :max basamak arasında olmalıdır.',
    'dimensions' => ':attribute alanının görsel boyutları geçersiz.',
    'distinct' => ':attribute alanında yinelenen bir değer var.',
    'doesnt_contain' => ':attribute alanı şunların hiçbirini içermemelidir: :values.',
    'doesnt_end_with' => ':attribute alanı şunlardan biriyle bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute alanı şunlardan biriyle başlamamalıdır: :values.',
    'email' => ':attribute alanı geçerli bir e-posta adresi olmalıdır.',
    'encoding' => ':attribute alanı :encoding ile kodlanmış olmalıdır.',
    'ends_with' => ':attribute alanı şunlardan biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'extensions' => ':attribute alanı şu uzantılardan birine sahip olmalıdır: :values.',
    'file' => ':attribute alanı bir dosya olmalıdır.',
    'filled' => ':attribute alanının bir değeri olmalıdır.',
    'gt' => [
        'array' => ':attribute alanı :value öğeden fazla içermelidir.',
        'file' => ':attribute alanı :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden büyük olmalıdır.',
        'string' => ':attribute alanı :value karakterden uzun olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute alanı en az :value öğe içermelidir.',
        'file' => ':attribute alanı :value kilobayt veya daha büyük olmalıdır.',
        'numeric' => ':attribute alanı :value veya daha büyük olmalıdır.',
        'string' => ':attribute alanı en az :value karakter olmalıdır.',
    ],
    'hex_color' => ':attribute alanı geçerli bir onaltılık renk kodu olmalıdır.',
    'image' => ':attribute alanı bir görsel olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute alanı :other içinde bulunmalıdır.',
    'in_array_keys' => ':attribute alanı şu anahtarlardan en az birini içermelidir: :values.',
    'integer' => ':attribute alanı bir tam sayı olmalıdır.',
    'ip' => ':attribute alanı geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute alanı geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute alanı geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute alanı geçerli bir JSON metni olmalıdır.',
    'list' => ':attribute alanı bir liste olmalıdır.',
    'lowercase' => ':attribute alanı küçük harflerden oluşmalıdır.',
    'lt' => [
        'array' => ':attribute alanı :value öğeden az içermelidir.',
        'file' => ':attribute alanı :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden küçük olmalıdır.',
        'string' => ':attribute alanı :value karakterden kısa olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute alanı en fazla :value öğe içerebilir.',
        'file' => ':attribute alanı :value kilobayt veya daha küçük olmalıdır.',
        'numeric' => ':attribute alanı :value veya daha küçük olmalıdır.',
        'string' => ':attribute alanı en fazla :value karakter olabilir.',
    ],
    'mac_address' => ':attribute alanı geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute alanı en fazla :max öğe içerebilir.',
        'file' => ':attribute alanı en fazla :max kilobayt olabilir.',
        'numeric' => ':attribute alanı en fazla :max olabilir.',
        'string' => ':attribute alanı en fazla :max karakter olabilir.',
    ],
    'max_digits' => ':attribute alanı en fazla :max basamak içerebilir.',
    'mimes' => ':attribute alanı şu türde bir dosya olmalıdır: :values.',
    'mimetypes' => ':attribute alanı şu türde bir dosya olmalıdır: :values.',
    'min' => [
        'array' => ':attribute alanı en az :min öğe içermelidir.',
        'file' => ':attribute alanı en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute alanı en az :min olmalıdır.',
        'string' => ':attribute alanı en az :min karakter olmalıdır.',
    ],
    'min_digits' => ':attribute alanı en az :min basamak içermelidir.',
    'missing' => ':attribute alanı bulunmamalıdır.',
    'missing_if' => ':other alanı :value olduğunda :attribute alanı bulunmamalıdır.',
    'missing_unless' => ':other alanı :value olmadıkça :attribute alanı bulunmamalıdır.',
    'missing_with' => ':values mevcut olduğunda :attribute alanı bulunmamalıdır.',
    'missing_with_all' => ':values mevcut olduğunda :attribute alanı bulunmamalıdır.',
    'multiple_of' => ':attribute alanı :value değerinin katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute alanının biçimi geçersiz.',
    'numeric' => ':attribute alanı bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute alanı en az bir harf içermelidir.',
        'mixed' => ':attribute alanı en az bir büyük ve bir küçük harf içermelidir.',
        'numbers' => ':attribute alanı en az bir rakam içermelidir.',
        'symbols' => ':attribute alanı en az bir sembol içermelidir.',
        'uncompromised' => 'Girilen :attribute bir veri sızıntısında yer almış. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute alanı mevcut olmalıdır.',
    'present_if' => ':other alanı :value olduğunda :attribute alanı mevcut olmalıdır.',
    'present_unless' => ':other alanı :value olmadıkça :attribute alanı mevcut olmalıdır.',
    'present_with' => ':values mevcut olduğunda :attribute alanı da mevcut olmalıdır.',
    'present_with_all' => ':values mevcut olduğunda :attribute alanı da mevcut olmalıdır.',
    'prohibited' => ':attribute alanının kullanılması yasaktır.',
    'prohibited_if' => ':other alanı :value olduğunda :attribute alanının kullanılması yasaktır.',
    'prohibited_if_accepted' => ':other kabul edildiğinde :attribute alanının kullanılması yasaktır.',
    'prohibited_if_declined' => ':other reddedildiğinde :attribute alanının kullanılması yasaktır.',
    'prohibited_unless' => ':other alanı :values içinde olmadıkça :attribute alanının kullanılması yasaktır.',
    'prohibits' => ':attribute alanı, :other alanının bulunmasını engeller.',
    'regex' => ':attribute alanının biçimi geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_array_keys' => ':attribute alanı şunlar için değer içermelidir: :values.',
    'required_if' => ':other alanı :value olduğunda :attribute alanı zorunludur.',
    'required_if_accepted' => ':other kabul edildiğinde :attribute alanı zorunludur.',
    'required_if_declined' => ':other reddedildiğinde :attribute alanı zorunludur.',
    'required_unless' => ':other alanı :values içinde olmadıkça :attribute alanı zorunludur.',
    'required_with' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_with_all' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_without' => ':values mevcut olmadığında :attribute alanı zorunludur.',
    'required_without_all' => ':values alanlarının hiçbiri mevcut olmadığında :attribute alanı zorunludur.',
    'same' => ':attribute alanı :other ile eşleşmelidir.',
    'size' => [
        'array' => ':attribute alanı :size öğe içermelidir.',
        'file' => ':attribute alanı :size kilobayt olmalıdır.',
        'numeric' => ':attribute alanı :size olmalıdır.',
        'string' => ':attribute alanı :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute alanı şunlardan biriyle başlamalıdır: :values.',
    'string' => ':attribute alanı bir metin olmalıdır.',
    'timezone' => ':attribute alanı geçerli bir saat dilimi olmalıdır.',
    'unique' => 'Bu :attribute zaten kullanılıyor.',
    'uploaded' => ':attribute yüklenemedi.',
    'uppercase' => ':attribute alanı büyük harflerden oluşmalıdır.',
    'url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'ulid' => ':attribute alanı geçerli bir ULID olmalıdır.',
    'uuid' => ':attribute alanı geçerli bir UUID olmalıdır.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Readable names for request fields. Translatable content fields
    | ("translations.ar.title") are named by the form requests themselves.
    |
    | Written as form labels (capitalized): messages begin with the field
    | name, and Laravel's automatic capitalization is not Turkish-aware
    | (it would turn "i" into "I" instead of "İ").
    |
    */

    'attributes' => [
        'name' => 'Ad',
        'email' => 'E-posta',
        'password' => 'Parola',
        'password_confirmation' => 'Parola onayı',
        'current_password' => 'Mevcut parola',
        'locale' => 'Dil',
        'token' => 'Doğrulama kodu',
        'remember' => 'Beni hatırla',
    ],

];
