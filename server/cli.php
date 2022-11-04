<?php

// command line client

    chdir(dirname(__FILE__));

    require_once "utils/error.php";
    require_once "utils/response.php";
    require_once "utils/hooks.php";
    require_once "utils/guidv4.php";
    require_once "utils/loader.php";
    require_once "utils/checkint.php";
    require_once "utils/email.php";
    require_once "utils/is_executable.php";
    require_once "utils/db_ext.php";
    require_once "utils/parse_uri.php";

    require_once "backends/backend.php";

    require_once "api/api.php";

    function usage() {
        global $argv;

        echo "usage: {$argv[0]}
            [--init-db]
            [--admin-password=<password>]
            [--reindex]
            [--clear-cache]
            [--cleanup]
            [--check-mail=<your email address>]
            [--run-demo-server]
            [--autoconfigure-domophone=<domophone_id> [--first-time]]
            [--cron=<minutely|hourly|daily|monthly>]
            [--install-crontabs]
            [--uninstall-crontabs]
        \n";

        exit(0);
    }

    $script_result = null;
    $script_process_id = -1;
    $script_filename = __FILE__;

    function startup() {
        global $db, $params, $script_process_id;

        $script_process_id = $db->insert('insert into core_running_processes (pid, start, process, params, expire) values (:pid, :start, :process, :params, :expire)', [
            "pid" => getmypid(),
            "start" => $db->now(),
            "process" => "cli.php",
            "params" => implode(' ', $params),
            "expire" => time() + 24 * 60 * 60,
        ]);
    }

    function shutdown() {
        global $script_process_id, $db, $script_result;

        if ($db) {
            $db->modify("update core_running_processes set done = :done, result = :result where running_process_id = :running_process_id", [
                "done" => $db->now(),
                "result" => $script_result,
                "running_process_id" => $script_process_id,
            ]);
        }
    }

    function check_if_pid_exists() {
        global $db;

        $pids = $db->get("select running_process_id, pid from core_running_processes where done is null or done = ''", false, [
            "running_process_id" => "id",
            "pid" => "pid",
        ]);

        foreach ($pids as $process) {
            if (!file_exists( "/proc/{$process['pid']}")) {
                $db->modify("update core_running_processes set done = :done, result = :result where running_process_id = :running_process_id", [
                    "done" => $db->now(),
                    "result" => "unknown",
                    "running_process_id" => $process['id'],
                ]);
            }
        }
    }

    register_shutdown_function('shutdown');

    $args = [];

    for ($i = 1; $i < count($argv); $i++) {
        $a = explode("=", $argv[$i]);
        $args[$a[0]] = @$a[1];
    }

    if (count($args) == 1 && array_key_exists("--run-demo-server", $args) && !isset($args["--run-demo-server"])) {
        $db = null;
        if (is_executable_pathenv(PHP_BINARY)) {
            echo "open in your browser:\n\n";
            echo "http://localhost:8000/client/index.html\n\n";
            chdir(dirname(__FILE__) . "/..");
            passthru(PHP_BINARY . " -S 0.0.0.0:8000");
        } else {
            echo "no php interpreter found in path\n";
        }
        exit(0);
    }

    try {
        mb_internal_encoding("UTF-8");
    } catch (Exception $e) {
        die("mbstring extension is not available\n");
    }

    if (!function_exists("curl_init")) {
        die("curl extension is not installed\n");
    }

    $required_backends = [
        "authentication",
        "authorization",
        "accounting",
        "users",
    ];

    try {
        if (PHP_VERSION_ID < 50600) {
            echo "minimal supported php version is 5.6\n";
            exit(1);
        }
    } catch (Exception $e) {
        echo "can't determine php version\n";
        exit(1);
    }

    try {
        $config = @json_decode(file_get_contents("config/config.json"), true);
    } catch (Exception $e) {
        echo "can't load config file\n";
        exit(1);
    }

    if (!$config) {
        echo "config is empty\n";
        exit(1);
    }

    if (@!$config["backends"]) {
        echo "no backends defined\n";
        exit(1);
    }

    try {
        $db = new PDO_EXT(@$config["db"]["dsn"], @$config["db"]["username"], @$config["db"]["password"], @$config["db"]["options"]);
    } catch (Exception $e) {
        echo "can't open database " . $config["db"]["dsn"] . "\n";
        exit(1);
    }

    $params = $argv;
    array_shift($params);

    try {
        $redis = new Redis();
        $redis->connect($config["redis"]["host"], $config["redis"]["port"]);
        if (@$config["redis"]["password"]) {
            $redis->auth($config["redis"]["password"]);
        }
        $redis->setex("iAmOk", 1, "1");
    } catch (Exception $e) {
        echo "can't connect to redis server\n";
        exit(1);
    }

    $version = 0;

    try {
        $query = $db->query("select var_value from core_vars where var_name = 'dbVersion'", PDO::FETCH_ASSOC);
        if ($query) {
            $version = (int)($query->fetch()["var_value"]);
        }
    } catch (Exception $e) {
        $version = 0;
    }

    $backends = [];
    foreach ($required_backends as $backend) {
        if (loadBackend($backend) === false) {
            die("can't load required backend [$backend]\n");
        }
    }

    if (count($args) == 1 && array_key_exists("--init-db", $args) && !isset($args["--init-db"])) {
        echo "dbVersion: $version\n";

        require_once "sql/install.php";
        require_once "utils/clear_cache.php";
        require_once "utils/reindex.php";
        init_db();
        startup();
        $n = clearCache(true);
        echo "$n cache entries cleared\n\n";
        reindex();
        echo "\n";
        exit(0);
    }

    startup();

    if (count($args) == 1 && array_key_exists("--cleanup", $args) && !isset($args["--cleanup"])) {
        require_once "utils/cleanup.php";
        cleanup();
        exit(0);
    }

    if (count($args) == 1 && array_key_exists("--reindex", $args) && !isset($args["--reindex"])) {
        require_once "utils/reindex.php";
        require_once "utils/clear_cache.php";
        reindex();
        $n = clearCache(true);
        echo "$n cache entries cleared\n";
        exit(0);
    }

    if (count($args) == 1 && array_key_exists("--clear-cache", $args) && !isset($args["--clear-cache"])) {
        require_once "utils/clear_cache.php";
        $n = clearCache(true);
        echo "$n cache entries cleared\n";
        exit(0);
    }

    if (count($args) == 1 && array_key_exists("--admin-password", $args) && isset($args["--admin-password"])) {
        try {
            $db->exec("insert into core_users (uid, login, password) values (0, 'admin', 'admin')");
        } catch (Exception $e) {
            //
        }
        $sth = $db->prepare("update core_users set password = :password, login = 'admin', enabled = 1 where uid = 0");
        $sth->execute([ ":password" => password_hash($args["--admin-password"], PASSWORD_DEFAULT) ]);
        echo "admin account updated\n";
        exit(0);
    }

    if (count($args) == 1 && array_key_exists("--check-mail", $args) && isset($args["--check-mail"])) {
        $r = email($config, $args["--check-mail"], "test email", "test email");
        if ($r === true) {
            echo "email sended\n";
            exit(0);
        }
        if ($r === false) {
            echo "no email config found\n";
            exit(0);
        }
        exit(0);
    }

    if (count($args) == 1 && array_key_exists("--cron", $args)) {
        $parts = [ "minutely", "hourly", "daily", "monthly" ];
        $part = false;

        foreach ($parts as $p) {
            if (in_array($p, $args)) {
                $part = $p;
            }
        }

        if ($part) {
            foreach ($config["backends"] as $backend_name => $cfg) {
                $backend = loadBackend($backend_name);
                if ($backend) {
                    if (!$backend->cron($part)) {
                        echo "$backend_name [$part] fail\n";
                    }
                }
            }
        }

        if ($part == "minutely") {
            check_if_pid_exists();
            $db->modify("delete from core_running_processes where coalesce(expire, 0) < :expire", [
                "expire" => time(),
            ]);
        }

        exit(0);
    }

    if ((count($args) == 1 || count($args) == 2) && array_key_exists("--autoconfigure-domophone", $args) && isset($args["--autoconfigure-domophone"])) {
        $domophone_id = $args["--autoconfigure-domophone"];

        $first_time = false;

        if (count($args) == 2) {
            if (array_key_exists("--first-time", $args)) {
                $first_time = true;
            } else {
                usage();
            }
        }

        if (checkInt($domophone_id)) {
            require_once "utils/autoconfigure_domophone.php";
            autoconfigure_domophone($domophone_id, $first_time);
            exit(0);
        }
    }

    if (count($args) == 1 && array_key_exists("--install-crontabs", $args) && !isset($args["--install-crontabs"])) {
        require_once "utils/install_crontabs.php";
        $n = installCrontabs();
        echo "$n crontabs lines added\n";
        exit(0);
    }

    if (count($args) == 1 && array_key_exists("--uninstall-crontabs", $args) && !isset($args["--install-crontabs"])) {
        require_once "utils/install_crontabs.php";
        $n = unInstallCrontabs();
        echo "$n crontabs lines removed\n";
        exit(0);
    }

usage();