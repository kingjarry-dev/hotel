<?php require '../config/config.php';req(true);
$w=['Hoy'=>"DATE(fecha)=CURDATE()",'Este mes'=>"YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())",'Total'=>"1=1"];$tot=[];$met=[];
foreach($w as $k=>$c){$tot[$k]=$pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE $c")->fetchColumn();
  foreach(METODOS as $m){$s=$pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE metodo=? AND $c");$s->execute([$m]);$met[$m][$k]=$s->fetchColumn();}}
$top=$pdo->query("SELECT c.producto,SUM(c.cantidad) q,SUM(c.precio*c.cantidad) t FROM consumos c JOIN estadias e ON e.id=c.estadia_id WHERE e.estado='cerrada' GROUP BY c.producto ORDER BY t DESC LIMIT 10")->fetchAll();
$last=$pdo->query("SELECT p.*,e.huesped,h.numero FROM pagos p JOIN estadias e ON e.id=p.estadia_id JOIN habitaciones h ON h.id=e.habitacion_id ORDER BY p.fecha DESC LIMIT 50")->fetchAll();
include '../includes/header.php';?><h2>Ingresos</h2>
<div class="stats"><?php foreach($tot as $k=>$v):?><div><b><?=money($v)?></b>Cobrado · <?=$k?></div><?php endforeach;?></div>
<div class="panel"><h3>Por método de pago</h3><table><tr><th>Método</th><?php foreach($w as $k=>$_):?><th class="n"><?=$k?></th><?php endforeach;?></tr>
<?php foreach($met as $m=>$v):?><tr><td><?=$m?></td><?php foreach($v as $x):?><td class="n"><?=money($x)?></td><?php endforeach;?></tr><?php endforeach;?></table></div>
<div class="split2"><div class="panel"><h3>Productos más vendidos</h3><table><?php foreach($top as $t):?><tr><td><?=e($t['producto'])?></td><td class="n"><?=$t['q']?> u.</td><td class="n"><?=money($t['t'])?></td></tr><?php endforeach;?></table><?php if(!$top):?><p class="empty">Aún no hay ventas de productos.</p><?php endif;?></div>
<div class="panel"><h3>Últimos cobros</h3><table><tr><th>Fecha</th><th>Cuarto</th><th>Huésped</th><th>Método</th><th class="n">Monto</th><th></th></tr><?php foreach($last as $l):?><tr><td><?=date('d/m H:i',strtotime($l['fecha']))?></td><td><?=e($l['numero'])?></td><td><?=e($l['huesped'])?></td><td><?=e($l['metodo'])?><?=$l['tipo']==='adelanto'?' (adelanto)':''?></td><td class="n"><?=money($l['monto'])?></td><td><a href="boleta.php?id=<?=$l['estadia_id']?>" target="_blank">Boleta</a></td></tr><?php endforeach;?></table><?php if(!$last):?><p class="empty">Los cobros aparecen aquí al registrar un adelanto o hacer check-out.</p><?php endif;?></div></div>
<?php include '../includes/footer.php';?>
