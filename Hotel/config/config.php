<?php
session_start();
// Ruta base del proyecto (funciona en localhost/hotel y en hotel.test)
$root=str_replace('\\','/',realpath($_SERVER['DOCUMENT_ROOT']));$proj=str_replace('\\','/',dirname(__DIR__));
define('BASE',rtrim(substr($proj,strlen($root)),'/').'/');
$pdo=new PDO('mysql:host=127.0.0.1;port='.(getenv('HOTEL_DB_PORT')?:3306).';dbname=hotel;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function money($n){return 'S/ '.number_format((float)$n,2);}
function esAdmin(){return ($_SESSION['u']['rol']??'')==='admin';}
function req($admin=false){
  if(empty($_SESSION['u'])){header('Location: '.BASE.'login.php');exit;}
  if($admin&&!esAdmin()){header('Location: '.BASE.'index.php');exit;}
}
const METODOS=['Efectivo','Yape','Plin','Tarjeta'];
const HOTEL_NOMBRE='Hotel'; // Cambia aquí el nombre que sale en el menú, el login y la boleta
