<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('SERVERNAME', 'sql2.njit.edu');
define('USERNAME','ci38');
define('PASSWORD','chhavi12345');
define('DBNAME','ci38');

class dbconn {
    protected static $db;
    private function __construct() {
        try {
            self::$db = new PDO('mysql:host='.SERVERNAME.';dbname='.DBNAME , USERNAME, PASSWORD);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
    }
    public static function getConnection() {
        if(!self::$db)
        {
            new dbconn;
        }
        return self::$db;
    }
}


abstract class collection {

    public static function create() {
        $model = new static::$modelName;
        return $model;
    }

    public static function findAll() {
        $db = dbconn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $class = static::$modelName;
        $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        $result = $stmt->fetchAll();
        return $result;
    }

    public static function findOne($id) {
        $db = dbconn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $class = static::$modelName;
        $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        $result = $stmt->fetchAll();
        return $result;
    }
}

abstract class model{

    public static $colstring;
    public static $valuestring;
    public static $id;

    public function save() {

        if (static::$idOfCol == '')
        {
            $array = get_object_vars($this);
            self::$colstring = implode(', ', $array);
            self::$valuestring = implode(', ', array_fill(0, count($array), '?'));
            $sql = $this->insert();
            $db = dbconn::getConnection();
            $statement = $db->prepare($sql);
            $statement->execute(static::$dataToInsert);
        }
        else
        {
            $sql = $this->update();
            $db = dbconn::getConnection();
            $statement = $db->prepare($sql);
            $statement->execute();
            return $sql;
            echo '<br>';
        }
    }

    public function insert()
    {
        $sql = "INSERT INTO ".static::$tableName." (".self::$colstring.") VALUES (".self::$valuestring.")";
        return $sql;
    }

    public function update() {

        $sql = "UPDATE ".static::$tableName." SET ".static::$columnToUpdate." = '".static::$updateData."' WHERE id=".static::$idOfCol;
        return $sql;
    }

    public function delete() {

        $db = dbconn::getConnection();
        $sql = "DELETE from ".static::$tableName." WHERE id=".static::$idOfCol;
        $statement = $db->prepare($sql);
        $statement->execute();
    }
}


class accounts extends collection {

    protected static $modelName = 'accounts';
}

class todos extends collection {

    protected static $modelName = 'todos';
}

class account extends model {

    public $email = 'email';
    public $fname = 'fname';
    public $lname = 'lname';
    public $phone = 'phone';
    public $birthday = 'birthday';
    public $gender = 'gender';
    public $password = 'password';

    public function insert($email, $fname, $lname, $phone, $birthday, $gender, $password) {
        global $db;
        $sql = 'INSERT INTO accounts (email, fname, lname, phone, birthday, gender, password) VALUES (:email, :fname, :lname, :phone, :birthday, :gender, :password, NOW())';
        $db = dbconn::getConnection();
        try {
            $statement = $db->prepare($sql);
            $statement->bindvalue(':email', $email);
            $statement->bindvalue(':fname',$fname);
            $statement->bindvalue(':lname',$lname);
            $statement->bindvalue(':phone',$phone);
            $statement->bindvalue(':birthday',$birthday);
            $statement->bindvalue(':gender',$gender);
            $statement->bindvalue(':password', $password);
            $statement->execute();
            $statement->closeCursor();
            //Get last ID that was automatically inserted
            $id = $db->lastInsertedID();
            return $id;
            return $sql;
        }
        catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    protected static $dataToInsert = array('b@test.com','blair','waldorf','81388','23-01-2017','female','test');

    public static $tableName = 'accounts';

    public static $columnToUpdate='lname';

    protected static $updateData = 'woodsen';

    public static $idOfCol = '9';
}


class todo extends model {

    public $owneremail = 'owneremail';
    public $ownerid = 'ownerid';
    public $createddate = 'createddate';
    public $duedate = 'duedate';
    public $message = 'message';
    public $isdone = 'isdone';

    protected static $dataToInsert = array('ol@g.com','4','11/1/2017','2/1/2017','ccde','10');

    public static $tableName = 'todos';

    public static $columnToUpdate='message';

    public static $updateData = 'code';

    public static $idOfCol = '3';
}



echo "<h1><u> This is PHP ActiveRecord Assignment </u></h1>";
echo "<h2> Select all Records from accounts</h2>";
$obj = new Account;
$obj->save();
$obj =  accounts::create();
$result = $obj -> findAll();
echo '<table border="1" cellspacing="1" cellpadding="1">';
echo '<tr><th>id</th><th>email</th><th>fname</th><th>lname</th><th>phone</th><th>birthday</th><th>gender</th><th>password</th></tr>';

foreach($result as $row)
{
    echo '<tr>';
    echo '<td>'.$row->id.'</td>';
    echo '<td>'.$row->email.'</td>';
    echo '<td>'.$row->fname.'</td>';
    echo '<td>'.$row->lname.'</td>';
    echo '<td>'.$row->phone.'</td>';
    echo '<td>'.$row->birthday.'</td>';
    echo '<td>'.$row->gender.'</td>';
    echo '<td>'.$row->password.'</td>';
    echo '</tr>';
}
echo '</table><br><hr>';

echo "<h2> Select one record from accounts</h2>";
echo "<h3> Record selected having id=7 </h3><br>";
$obj =  accounts::create();
$result = $obj -> findOne(7);
echo '<table border="1" cellspacing="1" cellpadding="1">';
echo '<tr><th>id</th><th>email</th><th>fname</th><th>lname</th><th>phone</th><th>birthday</th><th>gender</th><th>password</th></tr>';

foreach($result as $row)
{
    echo '<tr>';
    echo '<td>'.$row->id.'</td>';
    echo '<td>'.$row->email.'</td>';
    echo '<td>'.$row->fname.'</td>';
    echo '<td>'.$row->lname.'</td>';
    echo '<td>'.$row->phone.'</td>';
    echo '<td>'.$row->birthday.'</td>';
    echo '<td>'.$row->gender.'</td>';
    echo '<td>'.$row->password.'</td>';
    echo '</tr>';
}
echo '</table><br><hr>';


echo "<h2> Insert new Record in table accounts</h2>";
//echo "Inserted new Record<br><br>";
$obj = new Account;
$obj->save();
$obj =  accounts::create();
$result = $obj -> findAll();
echo '<table border="1" cellspacing="1" cellpadding="1">';
echo '<tr><th>id</th><th>email</th><th>fname</th><th>lname</th><th>phone</th><th>birthday</th><th>gender</th><th>password</th></tr>';

foreach($result as $row)
{
    echo '<tr>';
    echo '<td>'.$row->id.'</td>';
    echo '<td>'.$row->email.'</td>';
    echo '<td>'.$row->fname.'</td>';
    echo '<td>'.$row->lname.'</td>';
    echo '<td>'.$row->phone.'</td>';
    echo '<td>'.$row->birthday.'</td>';
    echo '<td>'.$row->gender.'</td>';
    echo '<td>'.$row->password.'</td>';
    echo '</tr>';
}
echo '</table><br><hr>';


echo "<h2> Update record in table accounts</h2>";
echo "updated lname = woodsen where id=6<br><br>";
$obj = new Account;
$obj->save();
$obj =  accounts::create();
$result = $obj -> findAll();
echo '<table border="1" cellspacing="1" cellpadding="1">';
echo '<tr><th>id</th><th>email</th><th>fname</th><th>lname</th><th>phone</th><th>birthday</th><th>gender</th><th>password</th></tr>';

foreach($result as $row)
{
    echo '<tr>';
    echo '<td>'.$row->id.'</td>';
    echo '<td>'.$row->email.'</td>';
    echo '<td>'.$row->fname.'</td>';
    echo '<td>'.$row->lname.'</td>';
    echo '<td>'.$row->phone.'</td>';
    echo '<td>'.$row->birthday.'</td>';
    echo '<td>'.$row->gender.'</td>';
    echo '<td>'.$row->password.'</td>';
    echo '</tr>';
}
echo '</table><br><hr>';

echo "<h2> Delete record in table accounts</h2>";
echo "Deleted record where id=9 from accounts<br>";
$obj = new account;
$obj->save();
echo '<br>';
$obj->delete();
$obj =  accounts::create();
$result = $obj -> findAll();
echo '<table border="1" cellspacing="1" cellpadding="1">';
echo '<tr><th>id</th><th>email</th><th>fname</th><th>lname</th><th>phone</th><th>birthday</th><th>gender</th><th>password</th></tr>';

foreach($result as $row)
{
    echo '<tr>';
    echo '<td>'.$row->id.'</td>';
    echo '<td>'.$row->email.'</td>';
    echo '<td>'.$row->fname.'</td>';
    echo '<td>'.$row->lname.'</td>';
    echo '<td>'.$row->phone.'</td>';
    echo '<td>'.$row->birthday.'</td>';
    echo '<td>'.$row->gender.'</td>';
    echo '<td>'.$row->password.'</td>';
    echo '</tr>';
}
echo '</table><br><hr>';