<?php

// Define the required paths.
$oyunCsvPath = __DIR__ . "/csv/oyun-data.csv";
$donemCsvPath = __DIR__ . "/csv/donem-data.csv";
$donemYazarEserMinifiedPath = __DIR__ . "/json/donem_yazar_eser.min.json";
$donemYazarEserNormalPath = __DIR__ . "/json/donem_yazar_eser.json";

$donemYazarMinifiedPath = __DIR__ . "/json/donem_yazar.min.json";
$donemYazarNormalPath = __DIR__ . "/json/donem_yazar.json";

$donemInfoMinifiedPath = __DIR__ . "/json/donem_info.min.json";
$donemInfoNormalPath = __DIR__ . "/json/donem_info.json";

$GLOBALS['appConfigPath'] = __DIR__ . "/../LYS-Edebiyat-AppConfig.json";

$donemlerTabloAdi = 'donemler';
$yazarlarTabloAdi = 'yazarlar';
$eserlerTabloAdi = 'eserler';
$donemInfoTabloAdi = 'donemler_info';

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
    $ends_line_level = null;
    $json_length = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = null;
        $post = "";
        if ($ends_line_level !== null) {
            $new_line_level = $ends_line_level;
            $ends_line_level = null;
        }
        if ($in_escape) {
            $in_escape = false;
        } else {
            if ($char === '"') {
                $in_quotes = !$in_quotes;
            } else {
                if (!$in_quotes) {
                    switch ($char) {
                        case '}':
                        case ']':
                            $level--;
                            $ends_line_level = null;
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
                            $new_line_level = null;
                            break;
                    }
                } else {
                    if ($char === '\\') {
                        $in_escape = true;
                    }
                }
            }
        }
        if ($new_line_level !== null) {
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
    foreach ($parts as $part) {
        if (!is_dir($filePath .= "/$part")) {
            mkdir($filePath);
        }
    }
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
 * @param null $donemInfoPath
 * @param null $databasePath
 * @return string
 */
function update_build_config_file($donemYazarEserPath = null,
                                  $donemYazarPath = null, $donemInfoPath = null, $databasePath = null) {
    $appConfig = json_decode("{}", true);
    if(file_exists($GLOBALS['appConfigPath'])) {
        $appConfig = file_get_contents($GLOBALS['appConfigPath']);
        $appConfig = json_decode($appConfig, true);
    } else {
        file_force_contents($GLOBALS['appConfigPath'], "{}");
    }

    // Update the build numbers and URLs if the data is updated.
    if ($donemYazarEserPath) {
        $donemYazarEserPath = create_url_from_path($donemYazarEserPath);
        if (!isset($appConfig['AppConfig']['BookDatabaseBuildNo'])) {
            $appConfig['AppConfig']['BookDatabaseBuildNo'] = 0;
        }
        $appConfig['AppConfig']['BookDatabaseBuildNo'] += 1;
        $appConfig['AppConfig']['BookDatabaseUrl'] = $donemYazarEserPath;
    }

    if ($donemYazarPath) {
        $donemYazarPath = create_url_from_path($donemYazarPath);
        if (!isset($appConfig['AppConfig']['AuthorDatabaseBuildNo'])) {
            $appConfig['AppConfig']['AuthorDatabaseBuildNo'] = 0;
        }
        $appConfig['AppConfig']['AuthorDatabaseBuildNo'] += 1;
        $appConfig['AppConfig']['AuthorDatabaseUrl'] = $donemYazarPath;
    }

    if ($donemInfoPath) {
        $donemInfoPath = create_url_from_path($donemInfoPath);
        if (!isset($appConfig['AppConfig']['DonemInfoDatabaseBuildNo'])) {
            $appConfig['AppConfig']['DonemInfoDatabaseBuildNo'] = 0;
        }
        $appConfig['AppConfig']['DonemInfoDatabaseBuildNo'] += 1;
        $appConfig['AppConfig']['DonemInfoDatabaseUrl'] = $donemInfoPath;
    }

    if ($databasePath) {
        $donemInfoPath = create_url_from_path($databasePath);
        if (!isset($appConfig['AppConfig']['DatabaseBuildNo'])) {
            $appConfig['AppConfig']['DatabaseBuildNo'] = 0;
        }
        $appConfig['AppConfig']['DatabaseBuildNo'] += 1;
        $appConfig['AppConfig']['DatabaseURL'] = $databasePath;
    }

    // If any part of the config is updated, commit it to the file.
    if ($donemYazarEserPath OR $donemYazarPath OR $donemInfoPath) {
        $appConfig = json_encode($appConfig, JSON_UNESCAPED_SLASHES);
        file_force_contents($GLOBALS['appConfigPath'], $appConfig);

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

function output_message($status) {
    if ($status) {
        echo "<br>
        Dosyaların oluşturulması tamamlandı, aşağıda çıktı olarak yerleştirdiğim AppConfig 
        objesini görebilirsin.
        <br><br>";
    } else {
        echo "<br>
        Sen beni çalıştırdın ama bişey değişmedi, tüm dosyalar aynı gibi görünüyo, yine de AppConfig çıktısını bırakıyorum alta, belki işine yarar.
        <br><br>";
    }
}

/**
 * Simple print and die function, useful while debugging.
 * @param $data
 */
function pd($data) {
    pp($data);
    die();
}

function pp($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function construct_donem_yazar_eser($csv) {
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

    return $donemYazarEser;
}


function get_app_config() {
    return pretty_print_JSON(file_get_contents($GLOBALS['appConfigPath']));
}