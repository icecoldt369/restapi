<?php
require_once('Database.php');
require_once('Model.php');

// Open database connection
$db = new Database();

// Retrieve inputs
$method = $_SERVER['REQUEST_METHOD'];
$base_url = '/v1/';
$resource = str_replace($base_url, '', $request_uri);
$resource = explode('/', $resource);



try {
    switch ($method) {
    case 'GET':
        if ($resource[1] == 'teams') {
            // retrieve information on all the teams (this does not include information on the players of each team, but should include a link to the collection resource for all players of each team), sorted by team names
            $teams = $team->readAll();
            foreach ($teams as &$team) {
                $team['players'] = "/teams/{$team['id']}/players";
            }
            [$data, $status] = [$teams, 200];
            } elseif (count($resource) == 3 && $resource[0] == 'teams' && $resource[2] == 'players') {
            // retrieve information on all players of a specific team
            $players = $player->readAllByTeam($resource[1]);
            [$data, $status] = [$players, 200];
              } elseif (count($resource) == 4 && $resource[0] == 'teams' && $resource[2] == 'players') {
            // retrieve information on an existing player of a team
            $p = new Player($db);
            $p->team_id = $resource[1];
            $p->id = $resource[3];
            try {
                $p->read();
                [$data, $status] = [$p, 200];
            } catch (Exception $e) {
                [$data, $status] = ["Error: " . $e->getMessage(), $e->getCode()];
            }
        } else {
            throw new Exception("Invalid resource: " . $resource[1], 400);
        }
        break;
        case 'POST':
            if (count($resource) == 1 && $resource[0] == 'teams') {
                [$data, $status] = createTeam($db, $data);
            } elseif (count($resource) == 3 && $resource[0] == 'teams' && $resource[2] == 'players') {
                [$data, $status] = createPlayer($db, $resource[1], $data);
            } else {
                throw new Exception("Bad request " . implode('/', $resource), 400);
            }
            break;
        case 'PUT':
            if (count($resource) == 2 && $resource[0] == 'teams') {
                [$data, $status] = updateTeam($db, $resource[1], $data);
            } elseif (count($resource) == 4 && $resource[0] == 'teams' && $resource[2] == 'players') {
                [$data, $status] = updatePlayer($db, $resource[1], $resource[3], $data);
            } else {
                throw new Exception("Bad request " . implode('/', $resource), 400);
            }
            break;
        case 'DELETE':
    if (count($resource) == 2 && $resource[0] == 'teams') {
        $team_id = $resource[1];
        $team = new Team($db);
        $team->id = $team_id;
        if ($team->delete()) {
            [$data, $status] = ['Team deleted successfully.', 204];
        } else {
            [$data, $status] = ['Error deleting team.', 400];
        }
    } elseif (count($resource) == 4 && $resource[0] == 'teams' && $resource[2] == 'players') {
        $player_id = $resource[3];
        $player = new Player($db);
        $player->id = $player_id;
        if ($player->delete()) {
            [$data, $status] = ['Player deleted successfully.', 204];
        } else {
            [$data, $status] = ['Error deleting player.', 400];
        }
    } else {
        throw new Exception("Bad request " . implode('/', $resource), 400);
    }
    break;
        default:
            throw new Exception('Method not supported', 405);
    }
    } catch (Exception $e) {
    [$data, $status] = ["Error: " . $e->getMessage(), $e->getCode()];
}

?>

