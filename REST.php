<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('Database.php');
require_once('Model.php');
require_once('model1.php');

$db = new Database();

$method = $_SERVER['REQUEST_METHOD'];
$resource = explode('/', $_REQUEST['resource']);
// Define $id with a default value of null
$id = null;

// Check if the query parameter is set and not empty
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
}

// Now $id has a value assigned to it (either the query parameter or null), and can be used in the Player constructor
$player = new Player($db->getConnection(), $id);
$team = new Team($db->getConnection());
$data = json_decode(file_get_contents('php://input'), TRUE);

switch ($method) {
    case 'GET':
        [$data, $status] = readData($db, $resource);
        break;
    case 'POST':
        [$data, $status] = createData($db, $resource, $data);
        break;
    case 'DELETE':
        [$data, $status] = deleteData($db, $resource);
        break;
    default:
        throw new Exception('Method Not Supported', 405);
}

header("Content-Type: application/json", TRUE, $status);
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


// function createTeam($db, $data)
// {
//     $t = new Team($db);
//     $t->name = $data['name'];
//     $t->sport = $data['sport'];
//     $t->average_age = $data['average_age'];
//     $t->create();
//     return [$t, "Team created successfully.", 201];
// }

// function createPlayer($db, $team_id, $data)
// {
//     $p = new Player($db);
//     $p->team_id = $team_id;
//     $p->surname = $data['surname'];
//     $p->given_names = $data['given_names'];
//     $p->nationality = $data['nationality'];
//     $p->date_of_birth = $data['date_of_birth'];
//     $p->create();
//     return [$p, "Player created successfully.", 201];
// }

function createData($db, $method, $resource, $data) {
    if ($method == 'POST' && count($resource) == 1 && $resource[0] == 'players') {
        $p = new Player($db);
        $p->team_id = $resource[1];
        $p->surname = $data['surname'];
        $p->given_names = $data['given_names'];
        $p->nationality = $data['nationality'];
        $p->date_of_birth = $data['date_of_birth'];
        $p->create();
        return ["Player created successfully.", 201];
    } elseif ($method == 'POST' && count($resource) == 2 && $resource[0] == 'players') {
        if (preg_match('/^\d{10}$/', $resource[1])) {
            return createPlayerWS($db, $resource[1], $data);
        } else {
            throw new Exception('Not a valid player', 400);
        }
    } elseif ($method == 'POST' && count($resource) == 1 && $resource[0] == 'teams') {
        $t = new Team($db);
        $t->name = $data['name'];
        $t->sport = $data['sport'];
        $t->average_age = $data['average_age'];
        $t->create();
        return createTeam($db, $data);
    } elseif ($method == 'POST' && count($resource) == 2 && $resource[0] == 'players' && $resource[1] == 'teams') {
        return createPlayerTeam($db, $data);
    } else {
        throw new Exception('Method Not Supported', 405);
    }
}

function readResource($db, $resource, $id = null) {
    if (count($resource) == 1 && $resource[0] == 'players') {
        $players = new Player($db->conn);
        if ($id) {
            $players->read($id);
            return $players;
        } else {
            return $players->findAll();
        }
    } elseif (count($resource) == 1 && $resource[0] == 'teams') {
        $teams = new Team($db->conn);
        if ($id) {
            $teams->read($id);
            return $teams;
        } else {
            return $teams->findAll();
        }
    } else {
        throw new Exception('Resource Not Found', 404);
    }
}

function readData($db, $resource)
{
    if (count($resource) == 1 && $resource[0] == 'teams') {
        $t = new Team($db);
        $teams = $t->readAll();
        foreach ($teams as &$team) {
            $team['players'] = "/teams/{$team['id']}/players";
        }
        return [$teams, 200];
    } elseif (count($resource) == 2 && $resource[0] == 'teams') {
        $t = new Team($db);
        $t->id = $resource[1];
        try {
            $t->read();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        $t->players = "/teams/{$t->id}/players";
        return [$t, 200];
    } elseif (count($resource) == 3 && $resource[0] == 'teams' && $resource[2] == 'players') {
        $p = new Player($db);
        $p->team_id = $resource[1];
        $players = $p->readAll();
        return [$players, 200];
    } elseif (count($resource) == 4 && $resource[0] == 'teams' && $resource[2] == 'players') {
        $p = new Player($db);
        $p->team_id = $resource[1];
        $p->id = $resource[3];
        try {
            $p->read();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        return [$p, 200];
    } else {
        throw new Exception("Bad request ".implode('/',$resource),400);
    }
}

function deleteResource($db, $resource, $id = null) {
    if (count($resource) == 1 && $resource[0] == 'players') {
        $student = new Player($db->conn);
        if ($id) {
            $student->read($id);
            $student->delete();
            return null;
        } else {
            throw new Exception('Cannot delete all players', 400);
        }
    } elseif (count($resource) == 1 && $resource[0] == 'teams') {
        $teams = new Team($db->conn);
        if ($id) {
            $teams->read($id);
            $teams->delete();
            return null;
        } else {
            throw new Exception('Cannot delete all teams', 400);
        }
    } else {
        throw new Exception('Resource Not Found', 404);
    }
}

function deleteData($db, $resource, $id = null) 
{
    if (count($resource) == 2 && $resource[0] == 'teams') {
        $team = new Team($db);
        $team->id = $resource[1];
        try {
            $team->read();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        try {
            $team->delete();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        return ["Team deleted successfully.", 200];
    } else if (count($resource) == 3 && $resource[0] == 'teams' && $resource[2] == 'players') {
        $player = new Player($db);
        $player->id = $resource[1];
        $player->team_id = $resource[1];
        try {
            $player->read();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        try {
            $player->delete();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        return ["Player deleted successfully.", 200];
    } else {
        throw new Exception("Bad request ".implode('/',$resource),400);
    }
}

function createPlayer($db, &$data) {
    $db->conn->beginTransaction();
    $data['id'] = Player::generateId($db);
    $players = new Player($db->conn, $data['id']);
    
    if (array_key_exists('nationality', $data)) {
        $players->set('nationality', $data['nationality']);
    }
    if (array_key_exists('date_of_birth', $data)) {
        $players->set('date_of_birth', $data['date_of_birth']);
    }
    
    $players->set($data);
    
    if ($players->validate()) {
        $player->store();
        $db->conn->commit();
        $players->setLinks();
        return [$player, 201];
    } else {
        $db->conn->rollback();
        throw new Exception("Player data incomplete", 400);
    }
}


function createTeam($db, &$data, $aType , $sid = NULL) {
    if (array_key_exists('id', $data)) {
        $sid = $data [ 'id' ];
    }
    
    if ( array_key_exists ($aType , $data))
    $aData = $data [$aType];
    else
    $aData = $data;
    $aTeam = new Team($db -> conn , $sid );
    $aTeam -> set ($aData);
    $player->set($data);
    
    if ($aTeam->validate()) {
        $aTeamId = $aTeam->store();
    } else {
        throw new Exception("Team $aType data incomplete", 400);
        if ( array_key_exists ( $aType , $data ))
        unset ( $data [ $aType ]);
        return $aTeamId; 
    }
}

function updateData($db, $resource, $data)
{
    if (count($resource) == 2 && $resource[0] == 'teams') {
        $team = new Team($db);
        $team->id = $resource[1];
        try {
            $team->read();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        try {
            $team->update($data);
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        return ["Team updated successfully.", 200];
    } else if (count($resource) == 3 && $resource[0] == 'teams' && $resource[2] == 'players') {
        $player = new Player($db);
        $player->id = $resource[1];
        $player->team_id = $resource[1];
        try {
            $player->read();
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        try {
            $player->update($data);
        } catch (Exception $e) {
            header('HTTP/1.1 '.$e->getCode().' '.$e->getMessage());
            exit;
        }
        return ["Player updated successfully.", 200];
    } else {
        throw new Exception("Bad request ".implode('/',$resource),400);
    }
}

set_exception_handler(function ($e) {
    $code = $e->getCode() ?: 400;
    header("Content-Type: application/json", NULL, $code);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
});
?>