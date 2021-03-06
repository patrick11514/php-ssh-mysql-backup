<?php

if (PHP_SAPI != 'cli') {
    msg("Tento skript nemůže běžet v prohlížeči!");
    exit;
}

/**
 * CONFIG
 */

$cfg = [
    "folder" => "./" //Musí končit vždy '/'
];

 /**
 * ----------------------
 */

$commands = [
    "help",
    "export",
    "delconf",
    "saveconf"
];

$helps = [
    "Zobrazí nápovědu",
    "Exportuje databázi(e)",
    "Smaže uložený config",
    "Uloží údaje do configu"
];

$colors = [
    "white"         => "39",
    "black"         => "30",
    "red"           => "31",
    "green"         => "32",
    "yellow"        => "33",
    "blue"          => "34",
    "magenta"       => "35",
    "cyan"          => "36",
    "light gray"    => "37",
    "dark gray"     => "90",
    "light red"     => "91",
    "light green"   => "92",
    "light yellow"  => "93",
    "light blue"    => "94",
    "light magenta" => "95",
    "light cyan"    => "96",
];

$background = [
    "white"         => "49",
    "black"         => "40",
    "red"           => "41",
    "green"         => "42",
    "yellow"        => "53",
    "blue"          => "44",
    "magenta"       => "45",
    "cyan"          => "46",
    "light gray"    => "47",
    "dark gray"     => "100",
    "light red"     => "101",
    "light green"   => "102",
    "light yellow"  => "103",
    "light blue"    => "104",
    "light magenta" => "105",
    "light cyan"    => "106",
];

if (isset($_SERVER["argv"][1])) {

    /**
     * Mazání configu
     */
    if (if_arg(1) == "delconf") {
        unlink("dbconn.txt");
        msg("Config smazán!");

    /**
     * Export Configu
     */
    } else if (if_arg(1) == "export") {
        $usage = PHP_EOL . "php export.php export useconfig <host> <port> <user> <password> <database(s)>"
        . PHP_EOL .
        "php export.php export false localhost 3306 root password123456 xenforo,authme"
        . PHP_EOL .
        "php export.php export true all";
        /**
        * Argument true/false
        */
        if (empty($_SERVER["argv"][2])) {
            msg(color("green") . "Použití: $usage");
            exit;
        }
        /**
        * Argument=true
        */
        if (if_arg(2) == "true") {
            if (empty($_SERVER["argv"][3])) {
                msg(color("green") . "Použití: $usage");
                exit;
            }
            /**
            * Export
            */
            $credentials = get_credentials_from_file();
            check_credentials($credentials["host"], $credentials["port"], $credentials["username"], $credentials["password"]);
            export_by_name($_SERVER["argv"][3]);
        /**
        * Argument=false
        */
        } else if (if_arg(2) == "false"){
            if (empty($_SERVER["argv"][3]) || empty($_SERVER["argv"][4]) || empty($_SERVER["argv"][5]) || empty($_SERVER["argv"][6]) || empty($_SERVER["argv"][7])) {
                msg(color("green") . "Použití: $usage");
                exit;
            }
            $credentials = [
                "username" => $_SERVER["argv"][5],
                "password" => $_SERVER["argv"][6],
                "host"     => $_SERVER["argv"][3],
                "port" => $_SERVER["argv"][4]
            ];
            check_credentials($credentials["host"], $credentials["port"], $credentials["username"], $credentials["password"]);
            export_by_name($_SERVER["argv"][7]);
        } else {
            msg(color("green") . "Použití: $usage");
            exit;
        }
    } else if (if_arg(1) == "help") {
        foreach ($commands as $id => $command) {
            $maxcount = 12;
            msg(correct($command, $maxcount) . " - " . $helps[$id]);
        }
    } else if (if_arg(1) == "saveconf") {
        $usage = PHP_EOL . "php export.php saveconf <username> <password> <host> <port>"; 
        if (empty($_SERVER["argv"][2]) || empty($_SERVER["argv"][3]) || empty($_SERVER["argv"][4] || $_SERVER["argv"][5])) {
            msg(color("red") . "Použití: " . $usage);
            exit;
        }
        check_credentials($_SERVER["argv"][4], $_SERVER["argv"][5], $_SERVER["argv"][2], $_SERVER["argv"][3], false);
        save_credentials_to_file($_SERVER["argv"][2], $_SERVER["argv"][3], $_SERVER["argv"][4], $_SERVER["argv"][5]);
        msg("Config uložen!");
    } else {
        msg(color("red") . "Příkaz nenalezen!");
        foreach ($commands as $id => $command) {
            $maxcount = 12;
            msg(correct($command, $maxcount) . " - " . $helps[$id]);
        }
        exit;
    }
    exit;
}

system("clear");

if (version_compare(PHP_VERSION, "7.0", "<")) {
    msg("Aktualizujte svoji verzi php na 7.0 nebo vyšší");
    exit;
}

$extensions = [
    "mysqli"
];

$errors = [];

foreach ($extensions as $extension) {
    if (!extension_loaded($extension)) {
        $errors[] = $extension;
    }
}

if (!empty($errors)) {
    msg("Dotázejte se svého hostitele webu pro instalaci těchto rozšíření:");
    foreach ($errors as $error) {
        msg("- $error");
    }
    exit;
}

$version = "0.1.9";
$github_ver = file_get_contents("https://raw.githubusercontent.com/patrick11514/ssh-mysql-backup/master/latest");

if (version_compare($version, $github_ver, "<")) {
    msg(color("red") . background("white") . "Máš zastaralou verzi, updatuj ji zde: https://github.com/patrick11514/ssh-mysql-backup/releases");
    exit;
}

$ignore_dbs = [
    "information_schema",
    "mysql",
    "performance_schema"
];



start:


if (file_exists("dbconn.txt")) {
    $credentials = get_credentials_from_file();
    if (!$credentials) {
        goto start;
    }
    $username = $credentials["username"];
    $password = $credentials["password"];
    $host     = $credentials["host"]    ;
    $port = $credentials["port"];
} else {
    check_host:
    $host_check = readline("Chcete použit jiného hosta, než localhost? (A/N): ");
    switch(strtolower($host_check)){
        case "y":
        case "yes":
        case "ano":
        case "a":
            $host = readline("Zadejte ip/host: ");
        break;

        case "n": 
        case "no": 
        case "ne": 
        case "n": 
            $host = "localhost";
        break;
        default:
            msg(color("red") . "Platné možnosti: y,yes,ano,a|n,no,ne");
            goto check_host;
        break;
    }
    system("clear");
    check_port:
    $port_check = readline("Chcete použít jiný port, než 3306? (A/N): ");
    switch(strtolower($port_check)){
        case "y":
        case "yes":
        case "ano":
        case "a":
            $port = readline("Zadejte port: ");
        break;

        case "n": 
        case "no": 
        case "ne": 
        case "n": 
            $port = null;
        break;
        default:
            msg(color("red") . "Platné možnosti: y,yes,ano,a|n,no,ne");
            goto check_port;
        break;
    }
    system("clear");
    $username = readline('Zadej jméno uživatele: ');
    system("clear");
    $temp_port = isset($port) ? ":" . $port : null;
    $host_str = $username . "@" . $host . $temp_port;
    $password = readline("Zadej heslo pro {$host_str}: ");
    system("clear");
    check_save:
    $save = readline("Chcete uložit tyto údaje? (A/N): ");

    switch(strtolower($save)) {
        case "y":
        case "yes":
        case "ano":
        case "a":
            save_credentials_to_file($username, $password, $host, $port);
        break;

        case "n": 
        case "no": 
        case "ne": 
        case "n": 
        break;
        default:
            msg(color("red") . "Platné možnosti: y,yes,ano,a|n,no,ne");
            goto check_save;
        break;
    }
}

msg("Vyber kterou databázi chceš exportovat:");
$port_str = isset($port) ? ":" . $port : null;
$host_port = $host . $port_str;
$conn = new mysqli($host_port, $username, $password);
if (isset($conn->connect_error)) {
    system("clear");
    msg("");
    msg(color("red") . "MYSQL Error: " . $conn->connect_error);
    msg("");
    if (file_exists("dbconn.txt")) {
        unlink("dbconn.txt");
        msg(color("red") . "Konfigurační soubor nalezen =>");
        msg(color("red") . "Konfigurační soubor byl smazán!");
        msg("");
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
msg(color("white") . background("red") . "Zadej čislo od 1 do " . ($i - 1));
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
                msg(color("white") . background("red") . "Nemůžeš vybrat více stejných databází!");
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

$backups_f = [];

if ($dbnum === "all") {
    $start = microtime(true);
    $nullport = isset($port) ? " --port=" . $port : null;
    $return = shell_exec("mysqldump --all_databases --user=$username --password=$password --host=$host" . $nullport);
    $return = watermark($return, (microtime(true) - $start), null);
    msg("Exportovano za " . (microtime(true) - $start) . "ms!");
    $filename = "database_export_all.sql";
    if (file_exists($cfg["folder"] . "database_export_all.sql")) {
        exist_export_all:
        $ch = readline("Soubor database_export_all.sql již existuje, chcete ho přepsat? (A/N): ");
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
                $filename = "database_export_all (" . randomstring(5) . ").sql";
            break;
            default:
                msg(color("red") . "Platné možnosti: y,yes,ano,a|n,no,ne");
                goto exist_export_all;
            break;
        }
    }

    $file = fopen($cfg["folder"] . $filename, "w");
    fwrite($file, $return);
    fclose($file);
    msg(color("green") . background("black") . "Záloha uložena do " . __DIR__ . "/$filename");
    exit;

} else {
    if (isset($databases[1])) {
        foreach ($databases as $database) {
            createSQL($database);
        }
    } else {
        createSQL($dbnum);
    }
    msg(color("green") . background("black") . "Záloha(y) uložena(y) do:");
    foreach ($backups_f as $backup_file) {
        msg("- " . __DIR__ . "/$backup_file");
    }

    exit;
}

function checkinput($n) {
    global $i;
    if (empty($n)) {
        msg(color("white") . background("red") . "Hodnota je prázdná!");
    } else if (!is_numeric($n)) {
        msg(color("white") . background("red") . "Zadaná hodnota není číslo!");
    } else if ($n > $i || $n < 1) {
        msg(color("white") . background("red") . "$n není číslo od 1 do " . ($i - 1));
        msg("Zadej číslo 1-" . ($i - 1) . "!");
    } else {
        return true;
    }
}

function msg($text) {
    $text = preg_replace("/[`](.*?)[`]/", color("green") . "$1" . color("white"), $text);
    echo $text . color("white") . background("white") . PHP_EOL;
}

function createSQL($database) {
    $start = microtime(true);

    global $username;
    global $password;
    global $host;
    global $port;
    global $database_list;
    global $backups_f;
    global $cfg;

    $database = $database_list[$database - 1];
    $nullport = isset($port) ? " --port=" . $port : null;
    $return = shell_exec("mysqldump $database --user=$username --password=$password --host=$host" . $nullport);
    $return = watermark($return, (microtime(true) - $start), $database);
    msg("Databáze $database exportována za " . (microtime(true) - $start) . "ms!");
    $filename = "database_export_$database.sql";
    if (file_exists($cfg["folder"] . "database_export_$database.sql")) {
        exist_single_db:
        $ch = readline("Soubor database_export_$database.sql již existuje, chcete ho přepsat? (A/N): ");
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
                $filename = "database_export_$database (" . randomstring(5) . ").sql";
            break;
            default:
                msg(color("red") . "Platné možnosti: y,yes,ano,a|n,no,ne");
                goto exist_single_db;
            break;
        }
    }
    $backups_f[] = $filename;
    $file = fopen($cfg["folder"] . $filename, "w");
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

function watermark($array, $time, $dbname) {
    if ($dbname === null) {
        $db_str = "";
    } else {
        $db_str = "CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET `utf8mb`;USE `{$dbname}`;";
    }
    $explode = explode("\n", $array);
    $return[0] = "--";
    $return[1] = "-- Exported at " . date("d.m.Y H:i:s");
    $return[2] = "-- Exported in {$time}ms";
    $return[3] = "--";
    $return[4] = "-- by using https://github.com/patrick11514/ssh-mysql-backup";
    $return[5] = "-- ------------------------------------------------------";
    $return[6] = "--";
    $return[7] = $db_str;
    foreach ($explode as $id => $value) {
        $newid = $id + 8;
        $return[$newid] = $value;
    }
    return implode("\n", $return);
}

function if_arg($number) {
    return $_SERVER["argv"][$number];
}

function correct($string, $spaces) {
    $length = strlen($string);
    $for = $spaces - $length;
    $return = $string;
    for ($i = 0; $i < $for; $i++) {
        $return .= " ";
    }
    return $return;
}

function color($color) {
    global $colors;
    if (empty($colors[$color])) {
        msg($colors["red"] . "Color not found!");
        return "\e[" . $colors["white"] . "m";
    }
    return "\e[" . $colors[$color] . "m";
}

function background($bg) {
    global $background;
    if (empty($background[$bg])) {
        msg($background["red"] . "Color not found!");
        return "\e[" . $background["white"] . "m";
    }
    return "\e[" . $background[$bg] . "m";
}

function check_credentials($host, $port, $user, $password, $showhelp = true) {
    $nullport = isset($port) && $port != 3306 ? ":" . $port : null;
    $mysqli = @new mysqli($host . $nullport, $user, $password);
    if (isset($mysqli->connect_error)) {
        msg(color("red") . background("white") . "MYSQLI Error: " . $mysqli->connect_error);
        if ($showhelp === true ){
            msg(color("green") . "Prosím spusťte `php export.php delconf` a poté `php export.php saveconf <username> <password> <host> <port>` pro úpravu uložených údajů.");
        }
        exit;
    }
    return true;
}

function save_credentials_to_file($username, $password, $host, $port) {
    $file = fopen("dbconn.txt", "w");
    fwrite($file, "%SAVED-DATA%|" . base64_encode($username . ";" . $password . ";" . $host. ";".  $port) . "|%/SAVED-DATA%");
    fclose($file);
}

function get_credentials_from_file() {
    $content = file_get_contents("dbconn.txt");
    if (empty($content)) {
        unlink("dbconn.txt");
        msg(color("white") . background("red") . "Údaje jsou neplatné!");
        msg(color("white") . background("red") . "Zadejte je znova!");
        return false;
    }
    $ex = explode("|", $content);
    if (empty($ex[1])) {
        unlink("dbconn.txt");
        msg(color("white") . background("red") . "Údaje jsou neplatné!");
        msg(color("white") . background("red") . "Zadejte je znova!");
        return false;
    }
    $dt = explode(";", base64_decode($ex[1]));
    if (empty($dt[0]) || empty($dt[1]) || empty($dt[2]) || count($dt) < 4) {
        unlink("dbconn.txt");
        msg(color("white") . background("red") . "Údaje jsou neplatné!");
        msg(color("white") . background("red") . "Zadejte je znova!");
        return false;
    }

    $port = isset($dt[3]) && $dt[3] !== "" ? $dt[3] : null;

    return [
        "username" => $dt[0],
        "password" => $dt[1],
        "host"     => $dt[2],
        "port" => $port
    ];
}

function export_by_name($string) {
    global $credentials;
    global $cfg;
    if ($string === "all") {
        $start = microtime(true);
        $nullport = isset($credentials["port"]) ? " --port=" . $credentials["port"] : null;
        $return = shell_exec("mysqldump --all_databases --user={$credentials["username"]} --password={$credentials["password"]} --host={$credentials["host"]}" . $nullport);
        if (file_exists($cfg["folder"] . "database_export_all.sql")) {
            $filename = "database_export_all (" . randomstring(5) . ").sql";
        } else {
            $filename = "database_export_all.sql";
        }
        $file = fopen($cfg["folder"] . $filename, "w");
        fwrite($file, watermark($return, (microtime(true) - $start), null));
        fclose($file);
    } else {

        $dbs = explode(",", $string);
        $mysqli = @new mysqli($credentials["host"], $credentials["username"], $credentials["password"]);
        foreach ($dbs as $db) {
            $mysqli->query("USE {$db};");
            if (!empty($mysqli->error)) {
                msg(color("red") . "Database $db not found!");
                exit;
            }
        }
        foreach ($dbs as $db) {
            $start = microtime(true);
            $nullport = isset($credentials["port"]) ? " --port=" . $credentials["port"] : null;
            $return = shell_exec("mysqldump $db --user={$credentials["username"]} --password={$credentials["password"]} --host={$credentials["host"]}" . $nullport);
            if (file_exists($cfg["folder"] . "database_export_$db.sql")) {
                $filename = "database_export_$db (" . randomstring(5) . ").sql";
            } else {
                $filename = "database_export_$db.sql";
            }
            $file = fopen($cfg["folder"] . $filename, "w");
            fwrite($file, watermark($return, (microtime(true) - $start), $db));
            fclose($file);
        }
    }
}