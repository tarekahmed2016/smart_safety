<?php

return [

    'cookie' => [
        'name' => 'visitor_id',
        'session_name' => 'visitor_sid',
        'minutes' => 525600,
        'session_minutes' => 30,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    'path_max_length' => 80,

    'cache_ttl_seconds' => 300,

    'excluded_path_prefixes' => [
        'dashboard',
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password',
        'verify-email',
        'confirm-password',
        'email',
        'password',
        'api',
        'up',
        'storage',
        'livewire',
        'sanctum',
        'broadcasting',
        'build',
        'vendor',
        '_debugbar',
        'telescope',
        'horizon',
        'pulse',
    ],

    'excluded_extensions' => [
        'css', 'js', 'map', 'mjs', 'cjs',
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'bmp', 'avif',
        'woff', 'woff2', 'ttf', 'eot', 'otf',
        'mp4', 'webm', 'mp3', 'wav', 'ogg',
        'pdf', 'zip', 'json', 'xml', 'txt', 'csv',
    ],

    'bot_user_agents' => [
        'bot', 'spider', 'crawler', 'crawl', 'slurp',
        'googlebot', 'bingbot', 'yandex', 'baiduspider', 'duckduckbot',
        'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot',
        'whatsapp', 'telegrambot', 'applebot', 'pingdom', 'uptimerobot',
        'semrush', 'ahrefs', 'mj12bot', 'dotbot', 'petalbot',
        'curl/', 'wget/', 'python-requests', 'python-urllib', 'php/',
        'httpclient', 'libwww', 'go-http-client', 'okhttp',
        'headlesschrome', 'phantomjs', 'scrapy',
    ],

];
