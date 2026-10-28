<?php require '../config/config.php';req();
$q=trim($_GET['q']??'');
$s=$pdo->prepare("SELECT COALESCE(NULLIF(dni,''),huesped) k,MAX(huesped) huesped,MAX(dni) dni,MAX(telefono) telefono,COUNT(*) visitas,MAX(entrada) ultima,COALESCE(SUM(total_hab+total_prod),0) gastado FROM estadias WHERE (?='' OR huesped LIKE ? OR dni LIKE ? OR telefono LIKE ?) GROUP BY k ORDER BY ultima DESC LIMIT 200");
$s->execute([$q,"%$q%","%$q%","%$q%"]);$gs=$s->fetchAll();
include '../includes/header.php';?><h2>Huéspedes</h2>
<form class="srch" method="get"><input name="q" value="<?=e($q)?>" placeholder="Buscar por nombre, DNI o teléfono"><button class="btn">Buscar</button></form>
<div class="panel"><table><tr><th>Huésped</th><th>DNI</th><th>Teléfono</th><th class="n">Visitas</th><th>Última visita</th><?php if(esAdmin()):?><th class="n">Total gastado</th><?php endif;?></tr>
<?php foreach($gs as $g):?><tr><td><?=e($g['huesped'])?></td><td><?=e($g['dni'])?></td><td><?=e($g['telefono'])?></td><td class="n"><?=$g['visitas']?><?=$g['visitas']>1?' ★':''?></td><td><?=date('d/m/Y',strtotime($g['ultima']))?></td><?php if(esAdmin()):?><td class="n"><?=money($g['gastado'])?></td><?php endif;?></tr><?php endforeach;?></table>
<?php if(!$gs):?><p class="empty"><?=$q?'No se encontró ningún huésped con esa búsqueda.':'Aquí aparecerán los huéspedes cuando registres entradas.'?></p><?php endif;?>
<p class="mut">★ = huésped que ha vuelto más de una vez. Se agrupan por DNI; si no tiene DNI, por nombre.</p></div>
<?php include '../includes/footer.php';?>
