<?php
$host="localhost";
$dbname="base_sueldo";
$username="root";
$password="";
try{
    $pdo=new PDO("mysql:host=$host;dbname=$dbname",$username,$password);
}catch(PDOException $e){
    die("Error: No se puede conectar a la base da datos. ".$e->getMessage());
}
?>