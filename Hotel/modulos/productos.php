<?php require '../config/config.php';req();
if(esAdmin()&&$_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['a'];$n=trim($_POST['nombre']??'');$c=trim($_POST['categoria']??'');$pr=(float)($_POST['precio']??0);
  if($a==='new'&&$n&&$c)$pdo->prepare('INSERT INTO productos(nombre,categoria,precio) VALUES(?,?,?)')->execute([$n,$c,$pr]);
  if($a==='upd'&&$n)$pdo->prepare('UPDATE productos SET nombre=?,categoria=?,precio=? WHERE id=?')->execute([$n,$c,$pr,(int)$_POST['id']]);
  if($a==='del')$pdo->prepare('UPDATE productos SET activo=0 WHERE id=?')->execute([(int)$_POST['id']]);
  header('Location: productos.php');exit;
}
$ps=$pdo->query('SELECT * FROM productos WHERE activo=1 ORDER BY categoria,nombre')->fetchAll();
include '../includes/header.php';?><h2>Productos</h2>
<?php if(esAdmin()):?><form method="post" class="panel inline"><input type="hidden" name="a" value="new"><label>Nombre<input name="nombre" required></label><label>Categoría<input name="categoria" list="cats" required></label><datalist id="cats"><option>Aseo<option>Snacks<option>Bebidas<option>Otros</datalist><label class="sm">Precio<input name="precio" type="number" step="0.1" min="0" required></label><button class="btn">Agregar producto</button></form><?php endif;?>
<div class="panel"><table><tr><th>Producto</th><th>Categoría</th><th class="n">Precio</th><th></th></tr>
<?php foreach($ps as $p):?><tr><?php if(esAdmin()):?><form method="post"><input type="hidden" name="id" value="<?=$p['id']?>"><td><input name="nombre" value="<?=e($p['nombre'])?>"></td><td><input name="categoria" value="<?=e($p['categoria'])?>"></td><td class="n"><input name="precio" type="number" step="0.1" min="0" value="<?=$p['precio']?>" class="w"></td><td class="n"><button class="lnk" name="a" value="upd">Guardar</button><button class="lnk red" name="a" value="del" onclick="return confirm('¿Quitar este producto?')">Quitar</button></td></form>
<?php else:?><td><?=e($p['nombre'])?></td><td><?=e($p['categoria'])?></td><td class="n"><?=money($p['precio'])?></td><td></td><?php endif;?></tr><?php endforeach;?></table></div>
<?php include '../includes/footer.php';?>
