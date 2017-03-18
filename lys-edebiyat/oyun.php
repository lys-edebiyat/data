<?php
require "helpers.php";


// Get the CSV data from the file.
$csv = @file_get_contents($oyunCsvPath);

// Error checking.
if (!$csv) {
    echo "<br>Beni çalıştırdın ama bana CSV datası vermen lazım, onu bulamıyorum şimdi bakınca. 
        Default olarak '$oyunCsvPath' yolundan okuyorum veriyi.
        Buna uygun olucak şekilde CSV'yi yerleştirirsen sorunsuzca okurum gibi.";
    die();
}

$donemYazarEser = construct_donem_yazar_eser($csv);
unset($csv);

// Get the keys since those are unique values.
foreach ($donemYazarEser as $donem => $yazarlar) {
    $donemYazar[$donem] = array_keys($yazarlar);
}

// Append the available keys as a little array to the dataset.
$aktifDonemler = array_keys($donemYazar);
$donemYazarEser['AktifDonemler'] = $aktifDonemler;

// Convert the arrays to JSON data.
$donemYazar = json_encode($donemYazar, JSON_UNESCAPED_UNICODE);
$donemYazarEser = json_encode($donemYazarEser, JSON_UNESCAPED_UNICODE);

// Check if the file contents are really updated.
$donemYazarEserUpdated = null;
$donemYazarUpdated = null;

$fileContents = @file_get_contents($donemYazarEserMinifiedPath);
if ($fileContents !== $donemYazarEser) {
    $donemYazarEserUpdated = $donemYazarEserMinifiedPath;
    create_JSON_files($donemYazarEserNormalPath, $donemYazarEserMinifiedPath, $donemYazarEser);
}

$fileContents = @file_get_contents($donemYazarMinifiedPath);
if ($fileContents !== $donemYazar) {
    $donemYazarUpdated = $donemYazarMinifiedPath;
    create_JSON_files($donemYazarNormalPath, $donemYazarMinifiedPath, $donemYazar);
}

// Update the build config JSON appropriately.
$appConfig = update_build_config_file($donemYazarEserUpdated, $donemYazarUpdated);
output_message($donemYazarUpdated OR $donemYazarEserUpdated);
pd($appConfig);
