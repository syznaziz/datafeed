<?php

return [
    'databases' => [
        'mysql' => [
            'pdo_type' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'kaufland',
            'username' => 'root',
            'password' => '',
        ],
        'sqlite' => [
            'pdo_type' => 'sqlite',
            'dbname' => 'path/to/your/sqlite.db',
        ],
        // Add more databases as needed
    ],
    'selected_database' => 'mysql',
    'data_source' => [
        'type' => 'xml',
        'path' => 'feed.xml',
        'delimiter' => ',', // CSV delimiter
        // Add more configuration options based on the data source type
    ],
    'logFile' => 'error.log',
];

?>
