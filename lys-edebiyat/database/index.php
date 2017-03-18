<?php
require "../helpers.php";

$updatePath = create_url_from_path(__DIR__ . '/update.php');
$createPath = create_url_from_path(__DIR__ . '/create.php');
$dropPath = create_url_from_path(__DIR__ . '/drop.php');

echo "Database bölümü burası, yapabileceğin işler belli. Aşağıdakilere tıkla.<br>";
echo "<a href=$updatePath>Veritabanını Güncelle</a><br>";
echo "<a href=$dropPath>Tabloları Sil</a><br>";
echo "<a href=$createPath>Tabloları Yarat</a><br>";
echo "Sadece veri değiştiyse güncelle seçeneğini seç. Yok ben tablolarla da oynıcam diyosan önce sil, sonra yarat.";