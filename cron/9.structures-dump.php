<?php

require_once "../init.php";

if ($redis->get("zkb:structure_api") == "true") exit();

$structures = $mdb->find("structures", ['hasMatch' => true]);
$output = [];
foreach ($structures as $row) {
    $output[$row['structure_id']] = ['structure_id' => $row['structure_id'], 'name' => $row['name'], 'solar_system_id' => $row['solar_system_id'], 'position' => $row['position']];
}
file_put_contents("./public/api/structures.json", json_encode($output));

$redis->setex("zkb:structure_api", 4800, "true");
