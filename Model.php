<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('Database.php');

class Player {
    private $conn;
    private static $table = 'players';
    private $parts = ['surname', 'given_names', 'nationality', 'date_of_birth'];
    public $surname, $given_names, $nationality, $date_of_birth;
    public $_links ;

    public function __construct ($conn , $sid ){
        $this -> conn = $conn ;
        $this -> id = $sid ;
    }

public function set ($source) {
if (is_object ($source) )
$source = (array) $source ;
foreach ($source as $key => $value )
if (in_array ( $key , $this -> parts ) )
$this -> $key = $value ;
else
throw new Exception ( " $key not an attribute of,→address " ,400) ;
}
public function setLinks ($sid , $aType ) {
$this -> _links =
[( object ) [ ' href ' => " / players / $sid / teams / $aType " ,
' method ' => ' GET ' , ' rel ' => ' self '] ,
( object ) [ ' href ' => " / players / $sid / teams / $aType " ,
' method ' => ' PATCH ' , ' rel ' => ' edit '] ,
( object ) [ ' href ' => " / players / $sid / teams / $aType " ,
' method ' => ' DELETE ' , ' rel ' => ' delete ' ]];
}
public function store () {
$query = 'INSERT INTO' . self :: $table .
'(team_id, surname, given_names, nationality, date_of_birth) VALUES (? ,? ,? ,? ,?, ?) ';
$stmt = $this -> conn -> prepare ($query) ;
$stmt -> execute (array( $this -> id , $this -> surname ,
$this -> given_names , $this -> nationality,
$this -> date_of_birth , $this -> team_id ));
$this -> id = $this->conn->lastInsertId();
return $this->id ;
}
public function read ($aType) {
    //create query
$query = ' SELECT * FROM ' . self :: $table .
' WHERE id =: id ';
$stmt = $this->conn->prepare ($query);
$stmt -> execute (array ($this->id));
$row = $stmt -> fetch () ;
foreach ( $row as $key => $value )
$this -> $key = $value ;
$this -> setLinks ( $this -> team_id, $aType ) ;
}
public function validate () {
foreach ( $this -> parts as $key )
if ( is_null ( $this -> $key ) )
return FALSE ;
return TRUE ;
}
public function __toString () {
return json_encode( $this,
    JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}



public function delete() { 
}
public static function readAll($db, $regexp) {
    $sql = "SELECT * FROM players";
    if ($regexp) {
        $sql .= " WHERE name LIKE '%$regexp%'";
    }
    $result = $db->getConnection()->query($sql);

    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $m = new Player($db);
            $m->set($row);
            $m->setLinks($m->slot);
            $data[] = $m;
        }
        return [$data, 200];
    } else {
        return ['No [players] found.', 404];
    }
}
}

class Team {
    private $conn;
    private static $table = 'teams';
    public $name, $sport, $average_age;

    public function __construct ($conn, $id = null) {
        $this -> conn = $conn ;
        $this -> id = $id ;
    }

    public function set($source) {
if (is_object($source))
$source = (array)$source;
foreach ($source as $key=>$value)
if (in_array($key,$this->parts))
$this->$key = $value;
else
throw new Exception("$key not an attribute of Teams",400);
}
public function validate() {
foreach ($this->parts as $key)
if (is_null($this->$key))
return FALSE;
return TRUE;
}
public function __toString() {
return json_encode($this,
JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
public function setLinks($id) {
    $this->_links = array(
        'self' => array(
            'href' => '/teams/' . $id,
            'method' => 'GET'
        ),
        'delete' => array(
            'href' => '/teams/' . $id,
            'method' => 'DELETE'
        )
    );
}

public function store () {
$query = ' INSERT INTO ' . self :: $table .
'(id , name, sport, average_age) VALUES (? ,? ,? ,?)';
$stmt = $this -> conn -> prepare ($query) ;
$stmt -> execute (array($this -> id , $this -> name ,
$this -> sport , $this -> average_age)) ;
return $this -> id ;
}

public static function generateID($db) {
$maxIdArr = $db -> conn -> query (" SELECT max (id) from
,players") -> fetch ( PDO :: FETCH_NUM ) ;
$newId = min ($maxIdArr [0]+1 ,2019000001) ;
return $newId ;
}

public function read () {
$query = ' SELECT * FROM ' . self :: $table .
' WHERE id =? ';
$stmt = $this -> conn -> prepare ( $query ) ;
$stmt -> execute (array ($this -> id)) ;
$row = $stmt -> fetch () ;
foreach ($row as $key => $value)
$this -> $key = $value ;
$this -> setLinks () ;
}
}

?>