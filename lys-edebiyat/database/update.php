<?php
require '../helpers.php';
$oldCsvPath = __DIR__ . '/oldData.csv';

if (file_exists($oldCsvPath) && filesize($oldCsvPath) == filesize($oyunCsvPath) && md5_file($oldCsvPath) == md5_file($oyunCsvPath)) {
    pp("Data değişmemiş, bir şey değişmeyecek.");
    pd(get_app_config());
} else {

    // Get the csv dataset.
    $donemYazarEser = get_csv_data($oyunCsvPath, $oldCsvPath);

    // Get the era data to another array and prepare insertion query.
    $donemler = array_keys($donemYazarEser);
    $eraQuery = "insert into $donemlerTabloAdi (donem) values ";
    foreach ($donemler as $key => $donem) {
        $eraQuery .= "('$donem'),";
        unset($donemler[$key]);
        $donemler[$donem] = $key + 1;
    }
    $eraQuery = rtrim($eraQuery, ',') . ';';

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

    // Construct book query.
    $eserQuery = "insert into $eserlerTabloAdi (yazar_id, eser) values ";
    foreach ($eserler as $eserAdi => $yazarAdi) {
        $yazarId = $yazarIndex[$yazarAdi] + 1;
        $eserQuery .= "($yazarId, '$eserAdi'),";
    }
    $eserQuery = rtrim($eserQuery, ',') . ';';

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

    $dbPath = create_url_from_path(__DIR__ . '/lys-data.db');

    // Update the build config JSON appropriately.
    $appConfig = update_build_config_file(null, null, null, $dbPath);
    $message = "Veritabanı ve tablolar dolduruldu. AppConfig'i de aşağıya bırakıyorum.<br>" .
        "Yaratılan tablolar: $donemlerTabloAdi, $yazarlarTabloAdi, $eserlerTabloAdi.<br>" .
        "Veritabanı yolu: $dbPath";
    pp($message);
    pd($appConfig);
}

function get_csv_data($csvPath, $csvPathToStore = null) {

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