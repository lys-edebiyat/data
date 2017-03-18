<?php
require "../helpers.php";

$oldCsvPath = __DIR__ . '/oldData.csv';

// Construct database connection.
$db = new SQLite3('lys-data.db');

$db->query("DROP TABLE IF EXISTS $donemlerTabloAdi");
$db->query("DROP TABLE IF EXISTS $yazarlarTabloAdi");
$db->query("DROP TABLE IF EXISTS $eserlerTabloAdi");

if (file_exists($oldCsvPath)) {
    unlink($oldCsvPath);
}
$databaseHomePath = create_url_from_path(__DIR__ . '/');
$message = "Tablolar düşürüldü: $donemlerTabloAdi, $yazarlarTabloAdi, $eserlerTabloAdi.<br>" .
    "Veritabanı yolu: " . create_url_from_path(__DIR__ . '/lys-data.db') . '<br>' .
    "<a href=$databaseHomePath>Database sayfasına dön</a>";
pd($message);