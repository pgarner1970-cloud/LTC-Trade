<?php
return [
  'dsn' => 'mysql:host=sdb-81.hosting.stackcp.net;dbname=tyres4sale-35303339872b;charset=utf8mb4',
  'user' => 'tyres4sale-35303339872b',
  'pass' => '5madFish!',
  'options' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ],
];
