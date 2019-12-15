<?php

if (empty($_SERVER["argv"])) {
    msg("Tento skript nemůže běžet v prohlížeči!");
    exit;
}

$ignore_dbs = [
    "information_schema",
    "mysql",
    "performance_schema"
];

system("clear");

start:


if (file_exists("dbconn.txt")) {
    $content = file_get_contents("dbconn.txt");
    if (empty($content)) {
        unlink("dbconn.txt");
        msg("Údaje jsou neplatné!");
        msg("Zadejte je znova!");
        goto start;
    }
    $ex = explode("|", $content);
    if (empty($ex[1])) {
        unlink("dbconn.txt");
        msg("Údaje jsou neplatné!");
        msg("Zadejte je znova!");
        goto start;
    }
    $dt = explode(";", base64_decode($ex[1]));
    if (empty($dt[0]) || empty($dt[1])) {
        unlink("dbconn.txt");
        msg("Údaje jsou neplatné!");
        msg("Zadejte je znova!");
        goto start;
    }
    $username = $dt[0];
    $password = $dt[1];
} else {
    $username = readline('Zadej jméno uživatele: ');
    system("clear");
    $password = readline("Zadej heslo pro $username: ");
    system("clear");
    $save = readline("Chcete uložit tyto údaje? (Y/N): ");

    switch(strtolower($save)) {
        case "y":
        case "yes":
        case "ano":
        case "a":
            $file = fopen("dbconn.txt", "w");
            fwrite($file, "%SAVED-DATA%|" . base64_encode($username . ";" . $password) . "|%/SAVED-DATA%");
            fclose($file);
        break;

        case "n": 
        case "no": 
        case "ne": 
        case "n": 
        break;
    }
}

msg("Vyber kterou databázi chceš exportovat:");
$conn = new mysqli("localhost:3306", $username, $password);
if (isset($conn->connect_error)) {
    system("clear");
    msg("MYSQL Error: " . $conn->connect_error);
    if (file_exists("dbconn.txt")) {
        unlink("dbconn.txt");
        msg("Konfigurační soubor nalezen =>");
        msg("Konfigurační soubor byl smazán!");
        msg("Spusťte skript znova a zadejte platné údaje.");
    }
    exit;
}


$rv = $conn->query("SHOW DATABASES;");

while($db = mysqli_fetch_row($rv)) {
    if (in_array($db[0], $ignore_dbs)) continue;
    $dbs[] = $db[0];
}

$i = 1;

$database_list = $dbs;

foreach ($dbs as $database) {
    if ($database === "mysql" || $database === "information_schema" || $database === "performance_schema") continue; 
    msg("(" . $i++ . ") $database");
}


msg("");
msg("Zadej čislo od 1 do" . ($i - 1));
msg("Lze zadat pouze čislo, nebo 1,2,3.. nebo zadej all pro všechny databáze.");

while(true) {
    $dbnum = readline("Zadej databázi(e) pro export: ");

    if ($dbnum === "all") {
        break;
    }

    $databases = explode(",", $dbnum);

    if (isset($databases[1])) {
        $continue = true;
        foreach (array_count_values($databases) as $count) {
            if ($count > 1) {
                msg("Nemůžeš vybrat více stejných databází!");
                $continue = false;
                break;
            }
        } 
        foreach ($databases as $database) {
            if (!checkinput($database, $i)) {
                $continue = false;
            }
        }
        if($continue === true) {
            break;
        }
    } else {
        if (checkinput($dbnum, $i)) {
            break;
        }
    }
}

if ($dbnum === "all") {
    $start = microtime(true);
    $return = shell_exec("mysqldump --all_databases -u $username --password=$password");
    $filename = "database_export_all.sql";
    if (file_exists("database_export_all.sql")) {
        $ch = readline("Soubor database_export_all.sql již existuje, chcete ho přepsat? (Y/N): ");
        switch(strtolower($ch)){
            case "y":
            case "yes":
            case "ano":
            case "a":
                unlink("database_export_all.sql");
                $filename = "database_export_all.sql";
            break;

            case "n": 
            case "no": 
            case "ne": 
            case "n": 
                $filename = "database_export_all(" . randomstring(5) . ").sql";
            break;
        }
    }

    $file = fopen($filename, "w");
    fwrite($file, $return);
    fclose($file);
    msg("Dokončeno za " . (microtime(true) - $start) . "ms!");
    exit;
    
} else {
    if (isset($databases[1])) {
        $start = microtime(true);
        foreach ($databases as $database) {
            createSQL($database);
        }
        msg("Dokončeno za " . (microtime(true) - $start) . "ms!");
        exit;
    } else {
        $start = microtime(true);
        createSQL($dbnum);
        msg("Dokončeno za " . (microtime(true) - $start) . "ms!");
        exit;
    }
}


function checkinput($n) {
    global $i;
    if (empty($n)) {
        msg("Hodnota je prázdná!");
    } else if (!is_numeric($n)) {
        msg("Zadaná hodnota není číslo!");
    } else if ($n > $i || $n < 1) {
        msg("$n není číslo od 1 do " . ($i - 1));
        msg("Zadej číslo 1-" . ($i - 1) . "!");
    } else {
        return true;
    }
}

function msg($text) {
    echo $text . PHP_EOL;
}

function createSQL($database) {
    global $username;
    global $password;
    global $database_list;
    $database = $database_list[$database - 1];
    $return = shell_exec("mysqldump $database -u $username --password=$password");
    $filename = "database_export_$database.sql";
    if (file_exists("database_export_$database.sql")) {
        $ch = readline("Soubor database_export_$database.sql již existuje, chcete ho přepsat? (Y/N): ");
        switch(strtolower($ch)) {
            case "y":
            case "yes":
            case "ano":
            case "a":
                unlink("database_export_$database.sql");
                $filename = "database_export_$database.sql";
            break;
    
            case "n": 
            case "no": 
            case "ne": 
            case "n": 
                $filename = "database_export_$database(" . randomstring(5) . ").sql";
            break;
        }
    }
    $file = fopen($filename, "w");
    fwrite($file, $return);
    fclose($file);
}

function randomstring($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}