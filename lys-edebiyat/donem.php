<?php
require "helpers.php";

$keys = ['bilgi', 'yazarlar', 'link'];

// Get the CSV data from the file.
$data = @file_get_contents($donemCsvPath);

// Error checking.
if (!$data) {
    echo "<br>Beni çalıştırdın ama bana CSV datası vermen lazım, onu bulamıyorum şimdi bakınca. 
        Default olarak '$donemCsvPath' yolundan okuyorum veriyi.
        Buna uygun olucak şekilde CSV'yi yerleştirirsen sorunsuzca okurum gibi.";
    die();
}
$data = explode(PHP_EOL, $data);

foreach ($data as $key => $item) {
    $item = explode(';', $item);
    $donem = array_shift($item);
    $item = array_combine($keys, $item);
    $item['bilgi'] = str_replace('--LINE_BREAK--', PHP_EOL . PHP_EOL, $item['bilgi']);
    $item['yazarlar'] = explode(',', $item['yazarlar']);
    $data[$donem] = $item;
    unset($data[$key]);
}
$donemInfo = json_encode($data, JSON_UNESCAPED_UNICODE);

// Check if the file contents are really updated.
$donemInfoUpdated = null;

$fileContents = @file_get_contents($donemInfoMinifiedPath);
if ($fileContents !== $donemInfo) {
    $donemInfoUpdated = $donemInfoMinifiedPath;
    create_JSON_files($donemInfoNormalPath, $donemInfoMinifiedPath, $donemInfo);
}

// Update the build config JSON appropriately.
$appConfig = update_build_config_file($appConfigPath, null, null, $donemInfoMinifiedPath);
output_message($donemInfoUpdated);
pd($appConfig);

