<?php require 'config/config.php';
if(!$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn()){
  $i=$pdo->prepare('INSERT INTO usuarios(usuario,clave,rol) VALUES(?,?,?)');
  $i->execute(['admin',password_hash('admin123',PASSWORD_DEFAULT),'admin']);
  $i->execute(['recepcion',password_hash('recepcion123',PASSWORD_DEFAULT),'recepcion']);
}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $s=$pdo->prepare('SELECT * FROM usuarios WHERE usuario=?');$s->execute([trim($_POST['usuario'])]);$u=$s->fetch();
  if($u&&password_verify($_POST['clave'],$u['clave'])){$_SESSION['u']=$u;header('Location: index.php');exit;}
  $err='Usuario o contraseña incorrectos.';
}
?><!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ingresar</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login"><form method="post" class="logbox"><h1><?=e(HOTEL_NOMBRE)?></h1><p>Ingresa para abrir tu turno.</p>
<?php if($err):?><div class="err"><?=e($err)?></div><?php endif;?>
<label>Usuario<input name="usuario" required autofocus></label><label>Contraseña<input name="clave" type="password" required></label>
<button class="btn">Entrar</button></form></body></html>
