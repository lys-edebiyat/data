<?php
require '../helpers.php';

// Construct database connection.
$db = new SQLite3('lys-data.db');

// Create the tables.
$db->query("CREATE TABLE IF NOT EXISTS $donemlerTabloAdi(
                _id INTEGER PRIMARY KEY,
                donem varchar(255)
            );");

$db->query("CREATE TABLE IF NOT EXISTS $donemInfoTabloAdi (
                donem_id INTEGER,
                info TEXT,
                yazarlar TEXT,
                link TEXT
            );");

$db->query("CREATE TABLE IF NOT EXISTS $yazarlarTabloAdi (
                _id INTEGER PRIMARY KEY,
                donem_id INTEGER,
                yazar varchar(255)
            );");

$db->query("CREATE TABLE IF NOT EXISTS $eserlerTabloAdi (
                _id INTEGER PRIMARY KEY,
                yazar_id INTEGER,
                eser varchar(255)
            );");

// Create the indexes.
$db->query("CREATE INDEX IF NOT EXISTS `donemId` ON `$donemlerTabloAdi` (`_id` )");
$db->query("CREATE INDEX IF NOT EXISTS `donemIndex` ON `$donemlerTabloAdi` (`donem` )");
$db->query("CREATE INDEX IF NOT EXISTS `donemInfo` ON `$donemInfoTabloAdi` (`donem_id` )");
$db->query("CREATE INDEX IF NOT EXISTS `eserId` ON `$eserlerTabloAdi` (`_id` )");
$db->query("CREATE INDEX IF NOT EXISTS `eserIndex` ON `$eserlerTabloAdi` (`eser` )");
$db->query("CREATE INDEX IF NOT EXISTS `eserYazarId` ON `$eserlerTabloAdi` (`yazar_id` )");
$db->query("CREATE INDEX IF NOT EXISTS `yazarDonemId` ON `$yazarlarTabloAdi` (`donem_id` )");
$db->query("CREATE INDEX IF NOT EXISTS `yazarId` ON `$yazarlarTabloAdi` (`_id` )");
$db->query("CREATE INDEX IF NOT EXISTS `yazarIndex` ON `$yazarlarTabloAdi` (`yazar` )");

$databaseHomePath = create_url_from_path(__DIR__ . '/');
$message = "Veritabanı ve tablolar yaratıldı.<br>" .
    "Yaratılan tablolar: $donemlerTabloAdi, $yazarlarTabloAdi, $eserlerTabloAdi, $donemInfoTabloAdi.<br>" .
    "Veritabanı yolu: " . create_url_from_path(__DIR__ . '/lys-data.db') . '<br>' .
    "<a href=$databaseHomePath>Database sayfasına dön</a>";;
pd($message);