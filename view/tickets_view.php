<?php

global $mdb, $fullAddr;

if (User::isLoggedIn() == false) {
    $app->redirect('/');
    return;
}

$message = array();
$info = User::getUserInfo();
$ticket = $mdb->findDoc('tickets', ['_id' => new MongoId($id), 'parentID' => null]);
if ($ticket == null or sizeof($ticket) == 0) {
    $message = array('status' => 'error', 'message' => 'Ticket does not exist.');
} elseif ($ticket['status'] == 0) {
    $message = array('status' => 'error', 'message' => 'Ticket has been closed, you cannot post, only view it');
} elseif ($ticket['characterID'] != User::getUserID() && @$info['moderator'] != true) {
    $app->notFound();
}

if ($_POST) {
    $reply = Util::getPost('reply');
    $status = Util::getPost('status');

    if (@$info['moderator'] == true && $status !== null) {
        $mdb->getCollection('tickets')->update(['_id' => new MongoID($id)], ['$set' => ['status' => $status]]);
        if ($status == 0) {
            $app->redirect('/account/tickets/');
        } else {
            $app->redirect('.');
        }
        exit();
    }

    if ($reply !== null && $ticket['status'] != 0) {
        $charID = User::getUserId();
        $name = $info['username'];
        $moderator = @$info['moderator'] == true;
        $mdb->insert('tickets', ['parentID' => $id, 'content' => $reply, 'characterID' => $charID, 'dttm' => time(), 'moderator' => $moderator]);
        $mdb->getCollection('tickets')->update(['_id' => new MongoID($id)], ['$set' => ['dttmUpdate' => time()]]);
        $mdb->getCollection('tickets')->update(['_id' => new MongoID($id)], ['$inc' => ['replies' => 1]]);

        if (@$info['moderator'] == true) {
            Util::sendsendEveMail($ticket['characterID'], 'zKillboard Ticket Response', "You have received a response to a ticket you submitted. To view the response, please click <a href=\"$fullAddr/account/tickets/view/$id/\">here</a>.");
        }

        $app->redirect('.');
        exit();
    } else {
        $message = array('status' => 'error', 'message' => 'No...');
    }
}

$replies = $mdb->find('tickets', ['parentID' => $id], ['dttm' => 1]);

Info::addInfo($ticket);
Info::addInfo($replies);
array_unshift($replies, $ticket);

$app->render('tickets_view.html', array('page' => $id, 'message' => $message, 'ticket' => $ticket, 'replies' => $replies, 'user' => $info));
