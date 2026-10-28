<?php require '../config/config.php';req(true);$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['a'];$n=trim($_POST['numero']??'');$t=trim($_POST['tipo']??'');$pr=(float)($_POST['precio']??0);
  try{
    if($a==='new'&&$n&&$t)$pdo->prepare('INSERT INTO habitaciones(numero,tipo,precio) VALUES(?,?,?)')->execute([$n,$t,$pr]);
    if($a==='upd'&&$n)$pdo->prepare('UPDATE habitaciones SET numero=?,tipo=?,precio=? WHERE id=?')->execute([$n,$t,$pr,(int)$_POST['id']]);
    if($a==='del')$pdo->prepare("DELETE FROM habitaciones WHERE id=? AND estado<>'ocupada'")->execute([(int)$_POST['id']]);
  }catch(PDOException $x){$err='No se pudo guardar: el número ya existe o la habitación tiene historial de estadías.';}
  if(!$err){header('Location: cuartos.php');exit;}
}
$hs=$pdo->query('SELECT * FROM habitaciones ORDER BY LENGTH(numero),numero')->fetchAll();
include '../includes/header.php';?><h2>Gestionar cuartos</h2>
<?php if($err):?><div class="err"><?=e($err)?></div><?php endif;?>
<form method="post" class="panel inline"><input type="hidden" name="a" value="new"><label class="sm">Número<input name="numero" required></label>
<label>Tipo<select name="tipo" id="tp" onchange="pr.value=this.selectedOptions[0].dataset.p||pr.value"><option data-p="40">Simple<option data-p="60">Doble<option data-p="70">Triple<option data-p="80">Matrimonial</select></label>
<label class="sm">Precio / noche<input name="precio" id="pr" type="number" step="0.5" min="0" value="40" required></label><button class="btn">Agregar cuarto</button></form>
<div class="panel"><table><tr><th>Número</th><th>Tipo</th><th class="n">Precio</th><th>Estado</th><th></th></tr>
<?php foreach($hs as $h):?><tr><form method="post"><input type="hidden" name="id" value="<?=$h['id']?>"><td><input name="numero" value="<?=e($h['numero'])?>" class="w"></td><td><input name="tipo" value="<?=e($h['tipo'])?>"></td><td class="n"><input name="precio" type="number" step="0.5" min="0" value="<?=$h['precio']?>" class="w"></td><td><?=e($h['estado'])?></td>
<td class="n"><button class="lnk" name="a" value="upd">Guardar</button><button class="lnk red" name="a" value="del" onclick="return confirm('¿Eliminar este cuarto?')">Eliminar</button></td></form></tr><?php endforeach;?></table></div>
<?php include '../includes/footer.php';?>
