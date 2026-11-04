<?php require '../config/config.php';req();
function mov($pdo,$pid,$cant,$motivo){$pdo->prepare('INSERT INTO movimientos_stock(producto_id,cantidad,motivo,usuario) VALUES(?,?,?,?)')->execute([$pid,$cant,$motivo,$_SESSION['u']['usuario']]);}
if(esAdmin()&&$_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['a']??'';$n=trim($_POST['nombre']??'');$c=trim($_POST['categoria']??'');$pr=max(0,(float)($_POST['precio']??0));$min=max(0,(int)($_POST['minimo']??5));
  if($a==='new'&&$n&&$c){
    $st=max(0,(int)($_POST['stock']??0));
    $pdo->prepare('INSERT INTO productos(nombre,categoria,precio,stock,stock_minimo) VALUES(?,?,?,?,?)')->execute([$n,$c,$pr,$st,$min]);
    if($st>0)mov($pdo,$pdo->lastInsertId(),$st,'compra');
  }
  if($a==='upd'&&$n){
    $id=(int)$_POST['id'];
    $pdo->prepare('UPDATE productos SET nombre=?,categoria=?,precio=?,stock_minimo=? WHERE id=?')->execute([$n,$c,$pr,$min,$id]);
    // Solo se ajusta el stock si el administrador cambió el número; así no pisa ventas hechas mientras tenía la página abierta.
    $delta=(int)($_POST['stock']??0)-(int)($_POST['stock_old']??0);
    if($delta!==0){$pdo->prepare('UPDATE productos SET stock=GREATEST(0,stock+?) WHERE id=?')->execute([$delta,$id]);mov($pdo,$id,$delta,'ajuste');}
  }
  if($a==='rep'){
    $id=(int)$_POST['pid'];$q=(int)($_POST['cant']??0);
    if($q>0){$pdo->prepare('UPDATE productos SET stock=stock+? WHERE id=? AND activo=1')->execute([$q,$id]);mov($pdo,$id,$q,'compra');}
  }
  if($a==='del')$pdo->prepare('UPDATE productos SET activo=0 WHERE id=?')->execute([(int)$_POST['id']]);
  header('Location: productos.php');exit;
}
$ps=$pdo->query("SELECT p.*,COALESCE((SELECT -SUM(m.cantidad) FROM movimientos_stock m WHERE m.producto_id=p.id AND m.motivo IN('venta','devolucion')),0) vendidos FROM productos p WHERE p.activo=1 ORDER BY p.categoria,p.nombre")->fetchAll();
$ag=0;$bajo=0;$unid=0;$reponer=[];
foreach($ps as $p){$unid+=$p['stock'];if($p['stock']<=0){$ag++;$reponer[]=$p;}elseif($p['stock']<=$p['stock_minimo']){$bajo++;$reponer[]=$p;}}
$hist=esAdmin()?$pdo->query("SELECT m.*,p.nombre FROM movimientos_stock m JOIN productos p ON p.id=m.producto_id ORDER BY m.fecha DESC,m.id DESC LIMIT 40")->fetchAll():[];
$lbl=['compra'=>'Reposición','venta'=>'Venta','ajuste'=>'Ajuste','devolucion'=>'Devuelto (quitado de una cuenta)'];
include '../includes/header.php';?><h2>Productos</h2>
<div class="stats"><div><b><?=count($ps)?></b>Productos</div><div><b><?=$unid?></b>Unidades en stock</div><div><b><?=$bajo?></b>Con poco stock</div><div><b><?=$ag?></b>Agotados</div></div>
<?php if($reponer):?><div class="panel alert"><h3>Por reponer</h3><?php foreach($reponer as $p):?><div class="pg"><span><?=e($p['nombre'])?></span><span><?=$p['stock']<=0?'Agotado':'Quedan '.(int)$p['stock']?></span></div><?php endforeach;?></div><?php endif;?>
<?php if(esAdmin()):?>
<form method="post" class="panel inline"><input type="hidden" name="a" value="rep"><label>Reponer stock (llegó mercadería)<select name="pid"><?php foreach($ps as $p):?><option value="<?=$p['id']?>"><?=e($p['nombre'])?> (hay <?=(int)$p['stock']?>)</option><?php endforeach;?></select></label><label class="sm">Cantidad<input name="cant" type="number" min="1" value="10" required></label><button class="btn">Sumar al stock</button></form>
<form method="post" class="panel inline"><input type="hidden" name="a" value="new"><label>Nombre<input name="nombre" required></label><label>Categoría<input name="categoria" list="cats" required></label><datalist id="cats"><option>Aseo<option>Snacks<option>Bebidas<option>Otros</datalist><label class="sm">Precio<input name="precio" type="number" step="0.1" min="0" required></label><label class="sm">Stock inicial<input name="stock" type="number" min="0" value="0"></label><label class="sm">Avisar con<input name="minimo" type="number" min="0" value="5"></label><button class="btn">Agregar producto</button></form>
<?php endif;?>
<div class="panel"><table><tr><th>Producto</th><th>Categoría</th><th class="n">Precio</th><th class="n">Stock</th><?php if(esAdmin()):?><th class="n">Avisar con</th><?php endif;?><th class="n">Vendidos</th><th></th></tr>
<?php foreach($ps as $p):$cls=$p['stock']<=0?'out':($p['stock']<=$p['stock_minimo']?'low':'');?><tr><?php if(esAdmin()):?><form method="post"><input type="hidden" name="id" value="<?=$p['id']?>"><input type="hidden" name="stock_old" value="<?=(int)$p['stock']?>"><td><input name="nombre" value="<?=e($p['nombre'])?>"></td><td><input name="categoria" value="<?=e($p['categoria'])?>"></td><td class="n"><input name="precio" type="number" step="0.1" min="0" value="<?=$p['precio']?>" class="w"></td><td class="n"><input name="stock" type="number" min="0" value="<?=(int)$p['stock']?>" class="w2"> <span class="bd <?=$cls?>"><?=$cls==='out'?'Agotado':($cls==='low'?'Bajo':'OK')?></span></td><td class="n"><input name="minimo" type="number" min="0" value="<?=(int)$p['stock_minimo']?>" class="w2"></td><td class="n"><?=(int)$p['vendidos']?></td><td class="n"><button class="lnk" name="a" value="upd">Guardar</button><button class="lnk red" name="a" value="del" onclick="return confirm('¿Quitar este producto?')">Quitar</button></td></form>
<?php else:?><td><?=e($p['nombre'])?></td><td><?=e($p['categoria'])?></td><td class="n"><?=money($p['precio'])?></td><td class="n"><?=(int)$p['stock']?> <span class="bd <?=$cls?>"><?=$cls==='out'?'Agotado':($cls==='low'?'Bajo':'OK')?></span></td><td class="n"><?=(int)$p['vendidos']?></td><td></td><?php endif;?></tr><?php endforeach;?></table>
<?php if(!$ps):?><p class="empty">Aún no hay productos. El administrador puede agregar el primero arriba.</p><?php endif;?>
<?php if(esAdmin()):?><p class="mut">Para corregir el stock, cambia el número de la columna Stock y pulsa Guardar. Queda anotado como “Ajuste”.</p><?php endif;?></div>
<?php if(esAdmin()):?><div class="panel"><h3>Movimientos de stock</h3><table><tr><th>Fecha</th><th>Producto</th><th>Motivo</th><th class="n">Cantidad</th><th>Usuario</th></tr>
<?php foreach($hist as $m):?><tr><td><?=date('d/m H:i',strtotime($m['fecha']))?></td><td><?=e($m['nombre'])?></td><td><?=e($lbl[$m['motivo']])?></td><td class="n"><?=$m['cantidad']>0?'+':''?><?=(int)$m['cantidad']?></td><td><?=e($m['usuario'])?></td></tr><?php endforeach;?></table>
<?php if(!$hist):?><p class="empty">Aquí aparecerán las ventas, reposiciones y ajustes de stock.</p><?php endif;?></div><?php endif;?>
<?php include '../includes/footer.php';?>
