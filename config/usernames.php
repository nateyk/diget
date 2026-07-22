<?php

return [
    'min_length' => 6,
    'max_length' => 50,
    'cooldown_days' => 30,

    // Route, brand, support, and security identities that must never be claimed.
    'reserved' => [
        'admin', 'administrator', 'anonymous', 'api', 'app', 'auth', 'backup', 'billing',
        'blog', 'callback', 'cart', 'categories', 'category', 'checkout',
        'contact', 'creator', 'dashboard', 'demo', 'diget', 'directory', 'domain',
        'discover', 'download', 'downloads', 'edit', 'editor', 'email',
        'ecommerce', 'favorite', 'feedback', 'files', 'finance', 'follow',
        'followers', 'following', 'forum', 'forums', 'gadget', 'gadgets',
        'games', 'group', 'groups', 'guest', 'help', 'home', 'homepage',
        'hosting', 'hostname', 'httpd', 'https', 'image', 'images', 'index',
        'indice', 'info', 'information', 'intranet', 'invite', 'iphone',
        'item', 'items', 'javascript', 'knowledgebase', 'legal', 'lists',
        'login', 'logout', 'manager', 'moderator', 'nobody', 'nopass',
        'oauth', 'orders', 'owner', 'password', 'payment', 'portfolio',
        'premium', 'privacy', 'qwerty',
        'profile', 'register', 'reset', 'reviews', 'root', 'sales',
        'security', 'service', 'settings', 'storefront', 'superuser',
        'support', 'sysadmin', 'system', 'tech', 'terms', 'test', 'user',
        'verify', 'webhook', 'webmaster', 'welcome', 'withdrawals', 'www',
        'websites', 'workshop', 'yourdomain', 'yourname', 'yoursite', 'yourusername',
    ],
];
