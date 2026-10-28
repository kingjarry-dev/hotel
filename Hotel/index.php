<?php require 'config/config.php';req();
function dias($a,$b){return (int)round((strtotime($b)-strtotime($a))/86400);}
function fd($d){return date('d/m/Y',strtotime($d));}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['a']??'';$h=(int)($_POST['h']??0);
  $m=in_array($_POST['metodo']??'',METODOS)?$_POST['metodo']:'Efectivo';
  $pay=fn($eid,$monto,$tipo)=>$pdo->prepare('INSERT INTO pagos(estadia_id,monto,metodo,tipo) VALUES(?,?,?,?)')->execute([$eid,$monto,$m,$tipo]);
  $act=$pdo->prepare("SELECT * FROM estadias WHERE habitacion_id=? AND estado='activa'");$act->execute([$h]);$est=$act->fetch();
  if($a==='in'&&!$est){
    $r=$pdo->prepare('SELECT * FROM habitaciones WHERE id=?');$r->execute([$h]);$r=$r->fetch();$g=trim($_POST['huesped']??'');
    if($r&&$g&&$r['estado']==='libre'){
      $s=strtotime($_POST['salida']??'');$n=$s?max(1,dias('today',date('Y-m-d',$s))):1;
      $pdo->prepare('INSERT INTO estadias(habitacion_id,huesped,dni,telefono,noches,salida_prevista,precio_noche) VALUES(?,?,?,?,?,?,?)')->execute([$h,$g,trim($_POST['dni']??''),trim($_POST['telefono']??''),$n,date('Y-m-d',strtotime("+$n day")),$r['precio']]);
      $eid=$pdo->lastInsertId();$ad=round((float)($_POST['adelanto']??0),2);if($ad>0)$pay($eid,$ad,'adelanto');
      $pdo->prepare("UPDATE habitaciones SET estado='ocupada' WHERE id=?")->execute([$h]);
    }
  }elseif($a==='estado'&&!$est){
    $ne=$_POST['estado']??'';if(in_array($ne,['libre','limpieza','mantenimiento']))$pdo->prepare("UPDATE habitaciones SET estado=? WHERE id=? AND estado<>'ocupada'")->execute([$ne,$h]);
  }elseif($est&&$a==='add'){
    $p=$pdo->prepare('SELECT * FROM productos WHERE id=? AND activo=1');$p->execute([(int)$_POST['pid']]);$p=$p->fetch();
    if($p)$pdo->prepare('INSERT INTO consumos(estadia_id,producto,precio,cantidad) VALUES(?,?,?,?)')->execute([$est['id'],$p['nombre'],$p['precio'],max(1,(int)$_POST['cant'])]);
  }elseif($est&&$a==='del'){
    $pdo->prepare('DELETE FROM consumos WHERE id=? AND estadia_id=?')->execute([(int)$_POST['cid'],$est['id']]);
  }elseif($est&&$a==='pay'){
    $ad=round((float)($_POST['monto']??0),2);if($ad>0)$pay($est['id'],$ad,'adelanto');
  }elseif($est&&$a==='ext'){
    $s=strtotime($_POST['salida']??'');$ent=date('Y-m-d',strtotime($est['entrada']));
    if($s&&dias($ent,date('Y-m-d',$s))>=1){$n=dias($ent,date('Y-m-d',$s));$pdo->prepare('UPDATE estadias SET noches=?,salida_prevista=? WHERE id=?')->execute([$n,date('Y-m-d',$s),$est['id']]);}
  }elseif($est&&$a==='out'){
    $c=$pdo->prepare('SELECT COALESCE(SUM(precio*cantidad),0) FROM consumos WHERE estadia_id=?');$c->execute([$est['id']]);$c=$c->fetchColumn();
    $pg=$pdo->prepare('SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estadia_id=?');$pg->execute([$est['id']]);$pg=$pg->fetchColumn();
    $th=$est['noches']*$est['precio_noche'];$saldo=round($th+$c-$pg,2);
    if($saldo!=0)$pay($est['id'],$saldo,'saldo');
    $pdo->prepare("UPDATE estadias SET estado='cerrada',salida=NOW(),total_hab=?,total_prod=? WHERE id=?")->execute([$th,$c,$est['id']]);
    $pdo->prepare("UPDATE habitaciones SET estado='limpieza' WHERE id=?")->execute([$h]);
    header('Location: modulos/boleta.php?id='.$est['id'].'&auto=1');exit;
  }
  header('Location: index.php?h='.$h);exit;
}
$rooms=$pdo->query("SELECT h.*,e.id eid,e.huesped,e.noches,e.entrada,e.salida_prevista,e.dni,e.telefono,e.precio_noche pn,(SELECT COALESCE(SUM(precio*cantidad),0) FROM consumos WHERE estadia_id=e.id) cons,(SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estadia_id=e.id) pag FROM habitaciones h LEFT JOIN estadias e ON e.habitacion_id=h.id AND e.estado='activa' ORDER BY LENGTH(h.numero),h.numero")->fetchAll();
$hoyd=date('Y-m-d');$libres=0;$pend=0;$alertas=0;$fuera=0;
foreach($rooms as $r){if($r['eid']){$pend+=$r['noches']*$r['pn']+$r['cons']-$r['pag'];if($r['salida_prevista']<=$hoyd)$alertas++;}elseif($r['estado']==='libre')$libres++;else $fuera++;}
$hoy=esAdmin()?$pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE DATE(fecha)=CURDATE()")->fetchColumn():null;
$sel=null;foreach($rooms as $r)if($r['id']==($_GET['h']??0))$sel=$r;
$prods=$pdo->query('SELECT * FROM productos WHERE activo=1 ORDER BY categoria,nombre')->fetchAll();
$G=[];foreach($pdo->query("SELECT dni,MAX(huesped) h,MAX(telefono) t FROM estadias WHERE dni IS NOT NULL AND dni<>'' GROUP BY dni") as $g)$G[$g['dni']]=['h'=>$g['h'],'t'=>$g['t']];
include 'includes/header.php';?>
<div class="stats"><div><b><?=$libres?></b>Disponibles</div><div><b><?=count($rooms)-$libres-$fuera?></b>Ocupadas</div><div><b><?=$fuera?></b>Limpieza / mantenim.</div><div><b><?=$alertas?></b>Salen hoy o vencidas</div><div><b><?=money($pend)?></b>Por cobrar</div>
<?php if($hoy!==null):?><div class="hi"><b><?=money($hoy)?></b>Cobrado hoy</div><?php endif;?></div>
<div class="split"><div class="keys">
<?php foreach($rooms as $r):?><a class="key <?=$r['eid']?'busy':['libre'=>'free','limpieza'=>'clean','mantenimiento'=>'mant'][$r['estado']]?><?=$sel&&$sel['id']==$r['id']?' sel':''?>" href="?h=<?=$r['id']?>"><i></i><b><?=e($r['numero'])?></b><small><?=e($r['tipo'])?> · <?=money($r['precio'])?></small><em><?=$r['eid']?e($r['huesped']):['libre'=>'Libre','limpieza'=>'Por limpiar','mantenimiento'=>'Mantenimiento'][$r['estado']]?></em>
<?php if($r['eid']&&$r['salida_prevista']<$hoyd):?><span class="due late">Vencida</span><?php elseif($r['eid']&&$r['salida_prevista']==$hoyd):?><span class="due warn">Sale hoy</span><?php elseif($r['eid']):?><span class="due">Sale <?=date('d/m',strtotime($r['salida_prevista']))?></span><?php endif;?></a><?php endforeach;?>
<?php if(!$rooms):?><p class="empty">No hay habitaciones. El administrador puede crearlas en “Gestionar cuartos”.</p><?php endif;?></div>
<section class="panel"><?php if(!$sel):?><p class="empty">Elige una habitación para registrar una entrada o ver su cuenta.</p>
<?php elseif(!$sel['eid']&&$sel['estado']!=='libre'):?><h2>Habitación <?=e($sel['numero'])?></h2><p class="mut"><?=$sel['estado']==='limpieza'?'Está por limpiar.':'Está en mantenimiento.'?></p>
<form method="post" class="inline"><input type="hidden" name="a" value="estado"><input type="hidden" name="h" value="<?=$sel['id']?>"><button class="btn" name="estado" value="libre"><?=$sel['estado']==='limpieza'?'Ya está limpia, dejar disponible':'Terminó el mantenimiento, dejar disponible'?></button><?php if($sel['estado']==='limpieza'):?><button class="btn alt" name="estado" value="mantenimiento">Enviar a mantenimiento</button><?php endif;?></form>
<?php elseif(!$sel['eid']):?><h2>Habitación <?=e($sel['numero'])?></h2><p class="mut"><?=e($sel['tipo'])?> · <?=money($sel['precio'])?> por noche</p>
<form method="post"><input type="hidden" name="a" value="in"><input type="hidden" name="h" value="<?=$sel['id']?>"><label>DNI o documento (opcional)<input name="dni" id="dni" maxlength="20" autocomplete="off"></label><label>Nombre del huésped<input name="huesped" id="hn" required></label><label>Teléfono (opcional)<input name="telefono" id="tl" maxlength="20"></label><label>Fecha de salida<input name="salida" type="date" min="<?=date('Y-m-d',strtotime('+1 day'))?>" value="<?=date('Y-m-d',strtotime('+1 day'))?>" required></label>
<div class="inline"><label>Adelanto (S/)<input name="adelanto" type="number" step="0.5" min="0" value="0"></label><label>Método<select name="metodo"><?php foreach(METODOS as $m):?><option><?=$m?></option><?php endforeach;?></select></label></div><button class="btn">Registrar entrada</button></form>
<form method="post" class="inline"><input type="hidden" name="a" value="estado"><input type="hidden" name="h" value="<?=$sel['id']?>"><button class="lnk" name="estado" value="limpieza">Marcar por limpiar</button><button class="lnk" name="estado" value="mantenimiento">Poner en mantenimiento</button></form>
<script>const G=<?=json_encode($G,JSON_HEX_TAG|JSON_HEX_AMP)?>;document.getElementById('dni').addEventListener('input',e=>{const g=G[e.target.value.trim()];if(g){document.getElementById('hn').value=g.h;document.getElementById('tl').value=g.t||''}});</script>
<?php else:$cs=$pdo->prepare('SELECT * FROM consumos WHERE estadia_id=?');$cs->execute([$sel['eid']]);$pgs=$pdo->prepare('SELECT * FROM pagos WHERE estadia_id=? ORDER BY fecha');$pgs->execute([$sel['eid']]);
$total=$sel['noches']*$sel['pn']+$sel['cons'];$saldo=$total-$sel['pag'];$late=$sel['salida_prevista']<$hoyd;?>
<h2>Habitación <?=e($sel['numero'])?> · <?=e($sel['huesped'])?></h2><p class="mut">Entró el <?=fd($sel['entrada'])?> · sale el <?=fd($sel['salida_prevista'])?> · <?=$sel['noches']?> noche(s) × <?=money($sel['pn'])?></p>
<p class="mut"><?=$sel['dni']?'DNI: '.e($sel['dni']).' · ':''?><?=$sel['telefono']?'Tel: '.e($sel['telefono']).' · ':''?><a href="modulos/boleta.php?id=<?=$sel['eid']?>" target="_blank">Ver cuenta imprimible</a></p>
<?php if($late):?><div class="err">Debía salir el <?=fd($sel['salida_prevista'])?> (<?=dias($sel['salida_prevista'],$hoyd)?> día(s) de más). Extiende la estadía para cobrar esos días, o haz el check-out.</div><?php endif;?>
<table><?php foreach($cs as $c):?><tr><td><?=$c['cantidad']?> × <?=e($c['producto'])?></td><td class="n"><?=money($c['precio']*$c['cantidad'])?></td><td class="n"><form method="post"><input type="hidden" name="a" value="del"><input type="hidden" name="h" value="<?=$sel['id']?>"><input type="hidden" name="cid" value="<?=$c['id']?>"><button class="lnk">Quitar</button></form></td></tr><?php endforeach;?></table>
<form method="post" class="inline"><input type="hidden" name="a" value="add"><input type="hidden" name="h" value="<?=$sel['id']?>"><label>Producto<select name="pid"><?php foreach($prods as $p):?><option value="<?=$p['id']?>"><?=e($p['nombre'])?> (<?=money($p['precio'])?>)</option><?php endforeach;?></select></label><label class="sm">Cant.<input name="cant" type="number" min="1" value="1"></label><button class="btn alt">Cargar</button></form>
<div class="tot"><span>Total de la cuenta</span><b><?=money($total)?></b></div>
<?php foreach($pgs as $p):?><div class="pg"><span>Pagado (<?=e($p['metodo'])?>)</span><span>− <?=money($p['monto'])?></span></div><?php endforeach;?>
<div class="tot"><span><?=$saldo<0?'A devolver':'Saldo a cobrar'?></span><b><?=money(abs($saldo))?></b></div>
<form method="post" class="inline"><input type="hidden" name="a" value="pay"><input type="hidden" name="h" value="<?=$sel['id']?>"><label>Registrar adelanto (S/)<input name="monto" type="number" step="0.5" min="0.5" required></label><label class="sm">Método<select name="metodo"><?php foreach(METODOS as $m):?><option><?=$m?></option><?php endforeach;?></select></label><button class="btn alt">Agregar pago</button></form>
<form method="post" class="inline"><input type="hidden" name="a" value="ext"><input type="hidden" name="h" value="<?=$sel['id']?>"><label>Nueva fecha de salida<input name="salida" type="date" value="<?=$late?date('Y-m-d',strtotime('+1 day')):$sel['salida_prevista']?>" required></label><button class="btn alt">Cambiar salida</button></form>
<form method="post" class="inline" onsubmit="return confirm('¿Cobrar y liberar la habitación?')"><input type="hidden" name="a" value="out"><input type="hidden" name="h" value="<?=$sel['id']?>"><label class="sm">Método<select name="metodo"><?php foreach(METODOS as $m):?><option><?=$m?></option><?php endforeach;?></select></label><button class="btn">Cobrar y hacer check-out</button></form>
<?php endif;?></section></div>
<?php include 'includes/footer.php';?>
