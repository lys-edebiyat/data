<?php
require '../helpers.php';
$oldOyunCsvPath = __DIR__ . '/oldOyunData.csv';
$oldDonemCsvPath = __DIR__ . '/oldDonemData.csv';

if (filesExist($oldOyunCsvPath, $oldDonemCsvPath) &&
    fileSizesMatch($oldOyunCsvPath, $oyunCsvPath, $oldDonemCsvPath, $donemCsvPath) &&
    fileHashesMatch($oldOyunCsvPath, $oyunCsvPath, $oldDonemCsvPath, $donemCsvPath)
) {
    pp("Data değişmemiş, bir şey değişmeyecek.");
    pd(get_app_config());
} else {

    // Get the csv dataset.
    $donemYazarEser = getEraAuthorBookList($oyunCsvPath, $oldOyunCsvPath);
    $donemInfo = getEraInfo($donemCsvPath, $oldDonemCsvPath);


    // Construct era list insertion query.
    list($eraQuery, $donemler) = constructEraQuery($donemYazarEser, $donemlerTabloAdi);

    // Construct author list insertion query.
    list($yazarQuery, $yazarIndex, $eserler) = constructAuthorQuery($donemYazarEser, $donemler, $yazarlarTabloAdi);

    // Construct book list insertion query.
    $eserQuery = constructBookQuery($eserler, $yazarIndex, $eserlerTabloAdi);

    // Construct era info insertion query.
    $eraInfoQuery = constructEraInfoQuery($donemInfo, $donemler, $donemInfoTabloAdi);

    // Construct database connection.
    $db = new SQLite3('lys-data.db');

    // Insert era data.
    $db->query("delete from $donemlerTabloAdi;");
    $db->query($eraQuery);

    // Insert author data.
    $db->query("delete from $yazarlarTabloAdi;");
    $db->query($yazarQuery);

    // Insert book data.
    $db->query("delete from $eserlerTabloAdi;");
    $db->query($eserQuery);

    // Insert era info data.
    $db->query("delete from $donemInfoTabloAdi;");
    $db->query($eraInfoQuery);

    $dbPath = create_url_from_path(__DIR__ . '/lys-data.db');

    // Update the build config JSON appropriately.
    $appConfig = update_build_config_file(null, null, null, $dbPath);
    $message = "Veritabanı ve tablolar dolduruldu. AppConfig'i de aşağıya bırakıyorum.<br>" .
        "Yaratılan tablolar: $donemlerTabloAdi, $yazarlarTabloAdi, $eserlerTabloAdi.<br>" .
        "Veritabanı yolu: $dbPath";
    pp($message);
    pd($appConfig);
}

function getEraAuthorBookList($csvPath, $csvPathToStore = null) {

    // Get the CSV data from the file.
    $csv = @file_get_contents($csvPath);

    // Error checking.
    if (!$csv) {
        echo "<br>Beni çalıştırdın ama bana CSV datası vermen lazım, onu bulamıyorum şimdi bakınca. 
        Default olarak '$csvPath' yolundan okuyorum veriyi.
        Buna uygun olucak şekilde CSV'yi yerleştirirsen sorunsuzca okurum gibi.";
        die();
    }

    if ($csvPathToStore) {
        file_force_contents($csvPathToStore, $csv);
    }

    // Get extended data.
    $donemYazarEser = construct_donem_yazar_eser($csv);
    unset($csv);

    return $donemYazarEser;
}

function getEraInfo($csvPath, $csvPathToStore = null) {

    $keys = ['bilgi', 'yazarlar', 'link'];

    // Get the CSV data from the file.
    $csv = @file_get_contents($csvPath);

    // Error checking.
    if (!$csv) {
        echo "<br>Beni çalıştırdın ama bana CSV datası vermen lazım, onu bulamıyorum şimdi bakınca. 
        Default olarak '$csvPath' yolundan okuyorum veriyi.
        Buna uygun olucak şekilde CSV'yi yerleştirirsen sorunsuzca okurum gibi.";
        die();
    }

    if ($csvPathToStore) {
        file_force_contents($csvPathToStore, $csv);
    }

    return constructEraInfo($csv, $keys);
}

function constructEraInfo($csv, $keys){
    $csv = explode(PHP_EOL, $csv);

    foreach ($csv as $key => $item) {
        $item = str_replace('\r', '', $item);
        $item = explode(';', $item);
        $donem = array_shift($item);
        $item = array_combine($keys, $item);
        $item['bilgi'] = str_replace('--LINE_BREAK--', PHP_EOL . PHP_EOL, $item['bilgi']);
        $item['yazarlar'] = str_replace(',', PHP_EOL, $item['yazarlar']);
        $csv[$donem] = $item;
        unset($csv[$key]);
    }
    return $csv;
}

function constructEraInfoQuery($donemInfo, $donemler, $donemInfoTabloAdi){

    // Get the era data to another array and prepare insertion query.
    $eraInfoQuery = "insert into $donemInfoTabloAdi(donem_id, info, yazarlar, link) values ";
    foreach ($donemInfo as $donemAdi => $data) {
        $info = SQLite3::escapeString($data['bilgi']);
        $authors = SQLite3::escapeString($data['yazarlar']);
        $link = SQLite3::escapeString($data['link']);
        $donemId = $donemler[$donemAdi];

        $eraInfoQuery .= "($donemId, '$info', '$authors', '$link'),";
    }
    $eraInfoQuery = rtrim($eraInfoQuery, ',') . ';';
    return $eraInfoQuery;
}

function constructEraQuery($donemYazarEser, $donemlerTabloAdi) {

    // Get the era data to another array and prepare insertion query.
    $donemler = array_keys($donemYazarEser);
    $eraQuery = "insert into $donemlerTabloAdi (donem) values ";
    foreach ($donemler as $key => $donem) {
        $eraQuery .= "('$donem'),";
        unset($donemler[$key]);
        $donemler[$donem] = $key + 1;
    }
    $eraQuery = rtrim($eraQuery, ',') . ';';

    return [$eraQuery, $donemler];
}

function constructAuthorQuery($donemYazarEser, $donemler, $yazarlarTabloAdi) {

    // Construct authors dataset.
    $yazarDonem = array();
    $yazarIndex = array();
    $eserler = array();
    foreach ($donemYazarEser as $donemAdi => $donemYazarlari) {
        foreach ($donemYazarlari as $yazarAdi => $yazarEserleri) {
            $yazarDonem[$yazarAdi] = $donemler[$donemAdi];
            $yazarIndex[] = $yazarAdi;

            foreach ($yazarEserleri as $eser) {
                if (!isset($eser['digerYazar'])) {
                    $eserler[$eser['eser']] = $yazarAdi;
                }
            }
        }
    }
    $yazarIndex = array_flip($yazarIndex);

    // Construct author query.
    $yazarQuery = "insert into $yazarlarTabloAdi (donem_id, yazar) values ";
    foreach ($yazarDonem as $yazarAdi => $donemId) {
        $yazarQuery .= "($donemId, '$yazarAdi'),";
    }
    $yazarQuery = rtrim($yazarQuery, ',') . ';';

    return [$yazarQuery, $yazarIndex, $eserler];
}

function constructBookQuery($eserler, $yazarIndex, $eserlerTabloAdi) {

    // Construct book query.
    $eserQuery = "insert into $eserlerTabloAdi (yazar_id, eser) values ";
    foreach ($eserler as $eserAdi => $yazarAdi) {
        $yazarId = $yazarIndex[$yazarAdi] + 1;
        $eserQuery .= "($yazarId, '$eserAdi'),";
    }
    $eserQuery = rtrim($eserQuery, ',') . ';';

    return $eserQuery;
}

function filesExist($file1, $file2) {
    return file_exists($file1) && file_exists($file2);
}

function fileSizesMatch($file1, $file1Match, $file2, $file2Match) {
    return filesize($file1) == filesize($file1Match) && filesize($file2) == filesize($file2Match);
}

function fileHashesMatch($file1, $file1Match, $file2, $file2Match) {
    return md5_file($file1) == md5_file($file1Match) && md5_file($file2) == md5_file($file2Match);
}