#!/usr/bin/php
<?php
/**
 * Moodle Users Total
 * Munin plugin to count total users
 *
 * It's required to define a container entry for this plugin in your
 * /etc/munin/plugin-conf.d/munin-node (or a separate and dedicated file).
 *
 * @example Example entry for configuration:
 * [moodle*]
 * env.type mysql
 * env.db moodle
 * env.user mysql_user
 * env.pass mysql_pass
 * env.host localhost
 * env.port 3306
 * env.table_prefix mdl_
 *
 * @author Arnaud Trouvé <ak4t0sh@free.fr>
 * @version 1.0 2014
 *
 */

$dbh = null;
$db = getenv('db');
$type = getenv('type');
$host = getenv('host');
$user = getenv('user');
$pass = getenv('pass');
$table_prefix = getenv('table_prefix');
$port = getenv('port');
if (!$port)
    $port = 3306;

if (count($argv) === 2 && $argv[1] === 'config') {
    echo "graph_title Moodle Total Users\n";
    echo "graph_args --base 1000 --lower-limit 0\n";
    echo "graph_vlabel users\n";
    echo "graph_category Moodle\n";
    echo "graph_scale no\n";
    echo "graph_info Displays the sum of users, as well as active, suspended and deleted accounts, in your Moodle site\n";
    echo "graph_total total\n";

    echo "users_active.label active\n";
    echo "users_suspended.label suspended\n";
    echo "users_deleted.label deleted\n";
    echo "users_active.min 0\n";
    echo "users_active.draw AREA\n";
    echo "users_suspended.min 0\n";
    echo "users_suspended.draw STACK\n";
    echo "users_deleted.min 0\n";
    echo "users_deleted.draw STACK\n";
    exit(0);
}

try {
    $dsn = $type . ':host=' . $host . ';port=' . $port . ';dbname=' . $db;
    $dbh = new PDO($dsn, $user, $pass);
} catch (Exception $e) {
    echo "Connection failed\n";
    exit(1);
}

//Active users (not deleted or suspended)
$nbusers = 0;
if (($stmt = $dbh->query("SELECT COUNT(id) FROM {$table_prefix}user WHERE deleted=0 AND suspended=0")) != false) {
    $nbusers = $stmt->fetchColumn();
    echo "users_active.value $nbusers\n";
}

//Active users (not deleted or suspended)
$nbusers = 0;
if (($stmt = $dbh->query("SELECT COUNT(id) FROM {$table_prefix}user WHERE suspended=1 and deleted=0")) != false) {
    $nbusers = $stmt->fetchColumn();
    echo "users_suspended.value $nbusers\n";
}

//Active users (not deleted or suspended)
$nbusers = 0;
if (($stmt = $dbh->query("SELECT COUNT(id) FROM {$table_prefix}user WHERE deleted=1")) != false) {
    $nbusers = $stmt->fetchColumn();
    echo "users_deleted.value $nbusers\n";
}