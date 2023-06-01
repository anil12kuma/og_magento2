<?php
return [
    'backend' => [
        'frontName' => 'admin'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => 'af07659d95adbe0fc02a816233aab05d'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'og-magento-website.cqktiyplfa4o.ap-south-1.rds.amazonaws.com',
                'dbname' => 'wildcountrygear',
                'username' => 'admin',
                'password' => '2ftiJyxG5hLhE85b',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '2',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '4',
            'max_concurrency' => '6',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000'
        ]
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'backend' => '\\Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '127.0.0.1',
                    'database' => '0',
                    'port' => '6379'
                ]
            ],
            'page_cache' => [
                'backend' => '\\Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '127.0.0.1',
                    'port' => '6379',
                    'database' => '1',
                    'compress_data' => '0'
                ]
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1,
        'checkout' => 1,
        'amasty_shopby' => 1,
        'optimization' => 1
    ],
    'downloadable_domains' => [
        '162.240.215.116/~wildcoun'
    ],
    'install' => [
        'date' => 'Thu, 21 Jul 2022 17:26:43 +0000'
    ],
    'dev' => [
        'debug' => [
            'debug_logging' => 1
        ]
    ],
    'http_cache_hosts' => [
        [
            'host' => '127.0.0.1',
            'port' => '80'
        ]
    ],
    'cron_consumers_runner' => [
        'cron_run' => true,
        'max_messages' => 0,
        'consumers' => [
            'exportProcessor'
        ]
    ]
];
