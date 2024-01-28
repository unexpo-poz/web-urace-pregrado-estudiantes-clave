<?php
include_once('inc/config.php'); 

imprima_enc();
if ($inscHabilitada){
	imprima_form();
}
else {
	print <<<x001
		<font style="font-family:arial; font-size:14px; color:red;">
		Disculpe, el sistema est&aacute; en mantenimiento.
		</font>
x001
;
}
imprima_final();

function imprima_enc(){
	global $tProceso, $lapso;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php echo $tProceso . $lapso; echo 'en insc form'; ?></title>
<script languaje="Javascript">
<!--
function validar(f) {
	if (f.cedula_v.value == "") {
		alert("Por favor, escriba su cédula antes de pulsar el botón Entrar");
		return false;
	} 
	else {
		f.cedula.value = f.cedula_v.value;
		f.cedula_v.value = "";
		window.open("","clave","left=0,top=0,width=790,height=580,scrollbars=1,resizable=1,status=1");
		return true;
	}

}
//-->
  </script>          
</head>


<body onload="javascript:document.chequeo.cedula_v.focus();">

<table id="table1" style="border-collapse: collapse;" border="0" cellpadding="0" cellspacing="1" width="750">

  <tbody>
  <tr>
    <td width="750">
          <p align="center" style="font-family:arial; font-weight:bold; font-size:20px;">
<?php			echo $tProceso . $lapso;
?>		  </p>
    </td>
  </tr>

  <tr>

       <td width="750" align="center">
<?php
}
function imprima_form(){
?>

	   <font face="Arial" size="2"><br>Por
favor escriba su n&uacute;mero de c&eacute;dula y luego pulse el bot&oacute;n "Entrar" para
          poder acceder a la hoja de registro de claves</font></td>
   </tr>
  <tr>
      <td width="777" align="center">
      <form method="post" name="chequeo" onsubmit="return validar(this)" 
            action="clave.php" target="clave" >
          <p><font size="2" face="Arial">&nbsp; C&eacute;dula:&nbsp;</font>
        <input name="cedula_v" size="20" tabindex="1" type="text" style="border:1px solid #0066FF;
	height:26px;
	background-color:#FFFFFF;
	width:70px; border-radius: 4px;"><font size="2">&nbsp; &nbsp;
		<input value="Entrar" name="b_enviar" tabindex="2" type="submit"> 
		<input value="x" name="cedula" type="hidden"> </p>

      </form>

<?php //imprima_form
}

function imprima_final(){
?>
	  </td>
    </tr>
    <tr>

      <td bgcolor="#C2DFFE" height="137" width="778"><font face="Arial" size="2"> <b>NOTAS:</b></font>
      <ul>

         <li><font face="Arial" size="2"> Recuerde que la Clave que va a registrar es solo para uso personal por la seguridad de sus datos acad&eacute;micos. </font></li>
		 <li><font face="Arial" size="2">Se recomienda que usted registre una clave que pueda recordar. </font></li>
		 <li><font face="Arial" size="2">Requisito indispensable: C&eacute;dula de identidad ORIGINAL o carnet estudiantil ORIGINAL. No se aceptan fotocopias. </font></li>

       </ul>
      </td>
    </tr>
  </tbody>
</table>
</body>
</html>
<?php
} //imprima_final	 
?>
