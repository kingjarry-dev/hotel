<?php $p=basename($_SERVER['PHP_SELF']);
function nav($f,$t,$i){global $p;return '<a href="'.BASE.$f.'"'.($p===basename($f)?' class="on"':'').'><span>'.$i.'</span>'.$t.'</a>';}?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hotel</title><link rel="stylesheet" href="<?=BASE?>assets/css/style.css"></head><body>
<aside><h1><?=e(HOTEL_NOMBRE)?><small>Panel de recepción</small></h1><nav>
<?=nav('index.php','Habitaciones','🔑')?><?=nav('modulos/productos.php','Productos','🛍️')?><?=nav('modulos/huespedes.php','Huéspedes','👥')?>
<?php if(esAdmin()):?><?=nav('modulos/cuartos.php','Gestionar cuartos','🛏️')?><?=nav('modulos/ingresos.php','Ingresos','💰')?><?php endif;?>
</nav><div class="who"><?=e($_SESSION['u']['usuario'])?> · <?=e($_SESSION['u']['rol'])?><a href="<?=BASE?>logout.php">Cerrar sesión</a></div></aside><main>
