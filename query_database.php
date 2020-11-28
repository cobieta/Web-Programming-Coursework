<?php
require_once 'MDB2.php';

// Connect to the database.
include "wedding-mysql-connect.php";  
 
$host='localhost';
$dbName='coa123wdb';
$dsn = "mysql://$username:$password@$host/$dbName"; 
$db =& MDB2::connect($dsn); 

if(PEAR::isError($db)){ 
    die($db->getMessage());
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);

// Query the database using the SQL query recieved.
$sql=$_REQUEST['sql'];
$res =& $db->query($sql);

if(PEAR::isError($res)){
    die($res->getMessage());
}

// Send the results back to wedding.php in JSON.
$value = json_encode($res->fetchAll());
echo $value;
?>
