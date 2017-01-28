<?php

// Define the required paths.
$csvPath = __DIR__ . "/csv/data.csv";
$donemYazarEserMinifiedPath = __DIR__ . "/json/donem_yazar_eser.min.json";
$donemYazarEserNormalPath = __DIR__ . "/json/donem_yazar_eser.json";
$donemYazarMinifiedPath = __DIR__ . "/json/donem_yazar.min.json";
$donemYazarNormalPath = __DIR__ . "/json/donem_yazar.json";
$appConfigPath = __DIR__ . "/../LYS-Edebiyat-AppConfig.json";

// Get the CSV data from the file.
$csv = @file_get_contents($csvPath);

// Error checking.
if(!$csv) {
    echo "<br>Beni çalıştırdın ama bana CSV datası vermen lazım, onu bulamıyorum şimdi bakınca. 
        Default olarak '$csvPath' yolundan okuyorum veriyi.
        Buna uygun olucak şekilde CSV'yi yerleştirirsen sorunsuzca okurum gibi.";
    die();
}

$csv = explode("\n", $csv);

$donemYazarEser = array();
foreach ($csv as $key => $value) {

    // Parse the CSV line.
    $temp = str_getcsv($value);

    // If this is not an empty line, process it.
    if (count($temp) == 4) {

        // Collect the author, book and title information to variables.
        $donem = $temp[0];
        $yazar = $temp[1];
        $eser = $temp[2];
        $digerYazar = $temp[3];

        $eserInfo = array();

        $eserInfo['eser'] = $eser;
        if ($digerYazar != '') {
            $eserInfo['digerYazar'] = $digerYazar;
        }

        // Push the values to arrays.
        $donemYazarEser[$donem][$yazar][] = $eserInfo;
    }

    // Remove the raw data.
    unset($csv[$key]);
}

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
$donemYazarEserUpdated = NULL;
$donemYazarUpdated = NULL;

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
$appConfig = update_build_config_file($appConfigPath, $donemYazarEserUpdated, $donemYazarUpdated);

if ($donemYazarUpdated AND $donemYazarEserUpdated) {
    echo "<br>
        Dosyaların oluşturulması tamamlandı, aşağıda çıktı olarak yerleştirdiğim AppConfig 
        objesini görebilirsin.
        <br><br>";
} else {
    echo "<br>
        Sen beni çalıştırdın ama bişey değişmedi, tüm dosyalar aynı gibi görünüyo, yine de AppConfig çıktısını bırakıyorum alta, belki işine yarar.
        <br><br>";
}
pd($appConfig);


/**
 * Taken from the StackOverflow answer: http://stackoverflow.com/a/9776726 - Prettifies given JSON string.
 * @param $json
 * @return string
 */
function pretty_print_JSON($json) {
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if ($ends_line_level !== NULL) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}':
                case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{':
                case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ":
                case "\t":
                case "\n":
                case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }
        if ($new_line_level !== NULL) {
            $result .= "\n" . str_repeat("\t", $new_line_level);
        }
        $result .= $char . $post;
    }

    return $result;
}

/**
 * Creates if any of the directories are missing, and writes the file.
 * Taken from the PHP documentation page: http://php.net/manual/tr/function.file-put-contents.php#84180
 * @param $filePath
 * @param $contents
 */
function file_force_contents($filePath, $contents) {
    $parts = explode('/', $filePath);
    $file = array_pop($parts);
    $filePath = '';
    foreach ($parts as $part) if (!is_dir($filePath .= "/$part")) mkdir($filePath);
    file_put_contents("$filePath/$file", $contents);
}

/**
 * Creates the minified and beautified JSON files.
 * @param $normalPath
 * @param $minifiedPath
 * @param $data
 */
function create_JSON_files($normalPath, $minifiedPath, $data) {
    file_force_contents($minifiedPath, $data);
    $prettyData = pretty_print_JSON($data);
    file_force_contents($normalPath, $prettyData);
}

/**
 * Increments the build counts and URLs in the config JSON appropriately.
 * @param $appConfigPath
 * @param null $donemYazarEserPath
 * @param null $donemYazarPath
 * @return string
 */
function update_build_config_file($appConfigPath, $donemYazarEserPath = NULL, $donemYazarPath = NULL) {
    $appConfig = file_get_contents($appConfigPath);
    $appConfig = json_decode($appConfig, true);

    // Update the build numbers and URLs if the data is updated.
    if ($donemYazarEserPath) {
        $donemYazarEserPath = create_url_from_path($donemYazarEserPath);
        $appConfig['AppConfig']['BookDatabaseBuildNo'] += 1;
        $appConfig['AppConfig']['BookDatabaseUrl'] = $donemYazarEserPath;
    }

    if ($donemYazarPath) {
        $donemYazarPath = create_url_from_path($donemYazarPath);
        $appConfig['AppConfig']['AuthorDatabaseBuildNo'] += 1;
        $appConfig['AppConfig']['AuthorDatabaseUrl'] = $donemYazarPath;
    }

    // If any part of the config is updated, commit it to the file.
    if($donemYazarEserPath OR $donemYazarPath) {
        $appConfig = json_encode($appConfig, JSON_UNESCAPED_SLASHES);
        file_force_contents($appConfigPath, $appConfig);
        return pretty_print_JSON($appConfig);
    }

    return pretty_print_JSON(json_encode($appConfig, JSON_UNESCAPED_SLASHES));

}

/**
 * Create an HTTP URL from the given absolute path.
 * @param $path
 * @return mixed|string
 */
function create_url_from_path($path) {
    $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
    $path = 'http://' . $_SERVER['SERVER_NAME'] . $path;
    return $path;
}

/**
 * Simple print and die function, useful while debugging.
 * @param $data
 */
function pd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die();
}