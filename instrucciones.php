<?php
include_once ('inc/config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<title><?php echo 'Instrucciones '.$tProceso . $lapso; ?></title>
<style type="text/css">
<!--
.instruc {
  font-family:Arial; 
  font-size: 13px; 
  font-weight: normal;
  background-color: #FFFFCC;
}
.act { 
  text-align: center; 
  font-family:Arial; 
  font-size: 12px; 
  font-weight: normal;
  background-color:#99CCFF;
}
-->
</style>  
</head>


<?php
	$titulo = 'Asignaci&oacute;n de Claves';
	
    print <<<P001
<body onload="javascript:self.focus();">
<table border="0" width="680">
	<tr><td class="act" style="font-size:14px; font-weight:bold;">
	INSTRUCCIONES</td></tr>
	<tr><td class="instruc">
        <ul>
            <li style="list-style-type: square;">
            UTILIZA el control de selecci&oacute;n en la columna "SECCI&Oacute;N"
			y elige la secci&oacute;n de las asignaturas que 
            deseas {$msgInscribir}.</li>
           <li style="list-style-type: square;">
		   Las asignaturas seleccionadas o inscritas se indican con
            <span id="F" class="act"> FONDO AZUL CLARO</span>.</li>
P001;
	if ($_GET['tp'] == '2') { //inclusionesy retiros
		print <<<P001i
           <li style="list-style-type: square;">
		   Las asignaturas seleccionadas para retirar o ya retiradas indican con
            <span id="F" class="act" style="background-color:#FF6666; color:white;"> FONDO ROJO</span>.</li>
            <li style="list-style-type: square;">
            Tu selecci&oacute;n ser&aacute; procesada solamente despu&eacute;s de 
			pulsar el bot&oacute;n "Inscribirme/Imprimir" y escribir de 
			nuevo tu clave.</li>
            <li style="list-style-type: square;">
            PUEDES VOLVER A INGRESAR las veces que lo requieras dentro del 
			periodo de inclusiones y/o retiros para retirar o incluir nuevas 
			asignaturas y para volver a imprimir tu planilla.</li>
            <li style="list-style-type: square;">
            NO PUEDES volver a incluir una asignatura que hayas retirado 
			anteriormente.</li>

P001i;
	}
	print <<<P001
             <li style="list-style-type: square;">
            La inscripci&oacute;n de las asignaturas seleccionadas est&aacute; sujeta
            a los reglamentos vigentes.</li>
        </ul>
        </td>
    </tr>
	<tr><td align="center">
	<input type="button" value="Cerrar" onclick="javascript:self.close();">
	</table>
P001;
?>
</body>
</html>