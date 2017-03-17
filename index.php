<?php
$serverBase = $path = 'http://' . $_SERVER['SERVER_NAME'] . __DIR__;
$serverBase = str_replace($_SERVER['DOCUMENT_ROOT'], '', $serverBase);
$pathToGo = $serverBase . '/lys-edebiyat';
$appConfigPath = $serverBase . '/LYS-Edebiyat-AppConfig.json';
echo "Gelmen gereken yer burası değil. Adres çubuğuna '$pathToGo' yazarsan aradığını bulursun bence.";
echo "<br>";
echo "Eğer AppConfig'i arıyosan ona da '$appConfigPath' adresinden ulaşabilirsin.";