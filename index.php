<?php
$pathToGo = $path = 'http://' . $_SERVER['SERVER_NAME'] . __DIR__;
$pathToGo = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pathToGo);
$pathToGo .= '/lys-edebiyat';
echo "Gelmen gereken yer burası değil. Adres çubuğuna '$pathToGo' yazarsan aradığını bulursun bence.";