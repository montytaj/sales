<?php

return [

    // Uncomment the languages that your site supports - or add new ones.
    // These are sorted by the native name, which is the order you might show them in a language selector.
    // Regional languages are sorted by their base language, so "British English" sorts as "English, British"
    'supportedLocales' => [
        'ar' => ['name' => 'Arabic',  'script' => 'Arab', 'native' => 'العربية', 'dir' => 'rtl', 'regional' => 'ar_SA'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'dir' => 'ltr', 'regional' => 'en_US'],
    ],

    'useAcceptLanguageHeader' => false,

    'hideDefaultLocaleInURL' => false,

    // If you want to display the locales in particular order in the language selector you should write the order here.
    //CAUTION: Please consider using the appropriate locale code otherwise it will not work
    //Example: 'localesOrder' => ['es','en'],
    'localesOrder' => [],

    // If you want to use custom language URL segments like 'at' instead of 'de-AT', you can map them to allow the
    // LanguageNegotiator to assign the desired locales based on HTTP Accept Language Header. For example, if you want
    // to use 'at' instead of 'de-AT', you would map 'de-AT' to 'at' (ie. ['de-AT' => 'at']).
    'localesMapping' => [],

    // Locale suffix for LC_TIME and LC_MONETARY
    // Defaults to most common ".UTF-8". Set to blank on Windows systems, change to ".utf8" on CentOS and similar.
    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    // URLs which should not be processed, e.g. '/nova', '/nova/*', '/nova-api/*' or specific application URLs
    // Defaults to []
    'urlsIgnored' => ['/skipped'],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
