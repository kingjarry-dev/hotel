<?php require '../config/config.php';req();
$s=$pdo->prepare('SELECT e.*,h.numero,h.tipo FROM estadias e JOIN habitaciones h ON h.id=e.habitacion_id WHERE e.id=?');$s->execute([(int)($_GET['id']??0)]);$x=$s->fetch();
if(!$x){http_response_code(404);exit('Boleta no encontrada');}
$cs=$pdo->prepare('SELECT * FROM consumos WHERE estadia_id=?');$cs->execute([$x['id']]);$cs=$cs->fetchAll();
$pg=$pdo->prepare('SELECT * FROM pagos WHERE estadia_id=? ORDER BY fecha');$pg->execute([$x['id']]);$pg=$pg->fetchAll();
$th=$x['noches']*$x['precio_noche'];$tc=0;foreach($cs as $c)$tc+=$c['precio']*$c['cantidad'];
$total=$th+$tc;$pagado=0;foreach($pg as $p)$pagado+=$p['monto'];$saldo=$total-$pagado;$cerr=$x['estado']==='cerrada';
?><!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Boleta <?=str_pad($x['id'],6,'0',STR_PAD_LEFT)?></title>
<style>body{font:14px/1.5 "Courier New",monospace;background:#e9eef0;margin:0;padding:16px;color:#000}.t{width:300px;margin:0 auto;background:#fff;padding:16px}h1,h2{text-align:center;margin:0}h1{font-size:20px}h2{font-size:14px;margin:6px 0}
hr{border:0;border-top:1px dashed #000;margin:8px 0}.r{display:flex;justify-content:space-between;gap:8px}.c{text-align:center}.big{font-size:16px;font-weight:bold}.small{font-size:11px}
.no{text-align:center;margin-bottom:12px}.no button,.no a{font:inherit;padding:8px 14px;margin:0 4px;border:1px solid #333;background:#fff;cursor:pointer;text-decoration:none;color:#000;border-radius:6px}
@media print{body{background:#fff;padding:0}.no{display:none}.t{width:100%;padding:0}}</style></head><body>
<div class="no"><button onclick="print()">Imprimir</button><a href="../index.php?h=<?=$x['habitacion_id']?>">Volver al sistema</a></div>
<div class="t"><h1><?=e(HOTEL_NOMBRE)?></h1><h2><?=$cerr?'BOLETA':'CUENTA (aún no cancelada)'?> N° <?=str_pad($x['id'],6,'0',STR_PAD_LEFT)?></h2>
<div class="c small">Emitida: <?=date('d/m/Y H:i')?></div><hr>
<div>Huésped: <?=e($x['huesped'])?></div><?php if($x['dni']):?><div>DNI: <?=e($x['dni'])?></div><?php endif;?><?php if($x['telefono']):?><div>Tel: <?=e($x['telefono'])?></div><?php endif;?>
<div>Habitación: <?=e($x['numero'])?> (<?=e($x['tipo'])?>)</div><div>Entrada: <?=date('d/m/Y H:i',strtotime($x['entrada']))?></div><div>Salida: <?=$cerr?date('d/m/Y H:i',strtotime($x['salida'])):'prevista '.date('d/m/Y',strtotime($x['salida_prevista']))?></div><hr>
<div class="r"><span>Alojamiento <?=$x['noches']?> x <?=number_format($x['precio_noche'],2)?></span><span><?=number_format($th,2)?></span></div>
<?php foreach($cs as $c):?><div class="r"><span><?=$c['cantidad']?> x <?=e($c['producto'])?></span><span><?=number_format($c['precio']*$c['cantidad'],2)?></span></div><?php endforeach;?><hr>
<div class="r big"><span>TOTAL</span><span>S/ <?=number_format($total,2)?></span></div><hr>
<?php foreach($pg as $p):?><div class="r"><span>Pago <?=e($p['metodo'])?><?=$p['tipo']==='adelanto'?' (adelanto)':''?></span><span><?=number_format($p['monto'],2)?></span></div><?php endforeach;?>
<div class="r"><span><?=$saldo<0?'A devolver':'Saldo pendiente'?></span><span>S/ <?=number_format(abs($saldo),2)?></span></div><hr>
<div class="c">¡Gracias por su visita!</div><div class="c small">Comprobante interno, no es boleta electrónica.</div></div>
<script>if(location.search.indexOf('auto=1')>-1)window.print();</script></body></html>
