<?php

global $mdb, $redis;

//if ($redis->get("zkb:killlistrow:" . $killID) != "true") return;

$map = array(
        'corporation' => array('column' => 'corporation', 'mixed' => true),
        'character' => array('column' => 'character', 'mixed' => true),
        'alliance' => array('column' => 'alliance', 'mixed' => true),
        'faction' => array('column' => 'faction', 'mixed' => true),
        'system' => array('column' => 'solarSystem', 'mixed' => true),
        'region' => array('column' => 'region', 'mixed' => true),
        'group' => array('column' => 'group', 'mixed' => true),
        'ship' => array('column' => 'shipType', 'mixed' => true),
        'location' => array('column' => 'item', 'mixed' => true),
        );
$vics = ['characterID' => 'character', 'corporationID' => 'corporation', 'allianceID' => 'alliance', 'shipTypeID' => 'ship', 'groupID' => 'group', 'factionID' => 'faction'];

$kills = Kills::getKills(['killID' => $killID], true, true, true);
if (isset($entityID) && $entityID > 0) {
    $type = @$map[$entityType]['column'] . "ID";
    $kills = Kills::mergeKillArrays($kills, array(), 100, $type, $entityID);
}

foreach ($kills as $id => $kill) {
    $vic = [];
    foreach ($vics as $key => $uri) {
        if (isset($kill['victim'][$key])) $vic[] = $kill['victim'][$key];
    }
    $kill['vics'] = implode(',', $vic);
    if ($whInfo = Info::getWormholeSystemInfo(@$kill['system']['solarSystemID'])) {
        $kill['whClass'] = $whInfo['class'];
    }
    $kills[$id] = $kill;
}

$app->render('components/kill_list_row.html', ['killList' => $kills, 'currentDate' => date('M d, Y')]);
