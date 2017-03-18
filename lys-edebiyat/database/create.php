<?php
require '../helpers.php';

// Construct database connection.
$db = new SQLite3('lys-data.db');

$db->query("CREATE TABLE IF NOT EXISTS $donemlerTabloAdi(
                _id INTEGER PRIMARY KEY,
                donem varchar(255)
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

$databaseHomePath = create_url_from_path(__DIR__ . '/');
$message = "Veritabanı ve tablolar yaratıldı.<br>".
    "Yaratılan tablolar: $donemlerTabloAdi, $yazarlarTabloAdi, $eserlerTabloAdi.<br>".
    "Veritabanı yolu: " . create_url_from_path(__DIR__ . '/lys-data.db').'<br>'.
    "<a href=$databaseHomePath>Database sayfasına dön</a>";;
pd($message);