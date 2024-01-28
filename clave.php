<?php
    //include_once('../consulta_mater/inc/vImage.php');
    include_once('inc/odbcss_c.php');
	include_once ('inc/config.php');
	include_once ('inc/activaerror.php');

	//$ipCliente = $_SERVER['REMOTE_ADDR'];
	//if ($ipCliente !='127.0.0.1'){
	//	die('No tiene acceso a esta p&acute;gina ');
	//}

	$archivoAyuda = $raizDelSitio."instrucciones.php";
    $datos_p = array();
    $mat_pre = array();
	$fvacio = TRUE;
	$clave_anterior = "";
    $lapso = "";
    $inscribe = "";
	$exped = "";
	//$sede = "daceccs";
	//$sede = "DRIVER={Centura SQLBase 3.5 32-bit Driver -NT & Win95};SRVR=Server1;DB=dace;uid=c;pwd=c";
	//DSN=dacebqto;DB=dace;SRVR=server1;UID=C;

	function cedula_valida($ced) {
        global $datos_p;
        global $ODBCSS_IP;
        global $lapso;
        global $inscribe;
		global $exped;
		global $sede;

        $cv = FALSE;
        if ($ced != ""){
            //echo " empece";
            $Cdatos_p = new ODBC_Conn($sede,"c","c");
            $dSQL     = " SELECT ci_e, exp_e, nombres, apellidos,carrera ";
            $dSQL     = $dSQL." FROM DACE002, TBLACA010";
            $dSQL     = $dSQL." WHERE ci_e='$ced' AND DACE002.c_uni_ca=TBLACA010.c_uni_ca " ;
            $Cdatos_p->ExecSQL($dSQL);
			if ($Cdatos_p->filas == 1) {
				$cv = ($ced == $Cdatos_p->result[0][0]); 
				$datos_p = $Cdatos_p->result[0];
			}
		} 
        
		// Si falla la autenticacion del usuario, hacemos un retardo
		// para reducir los ataques por fuerza bruta
		//if (!$cv) {
		//	sleep(5); //retardo de 5 segundos
		//}			
        return $cv;
	}

	function elUsuarioNoTieneClave($sinError) {
			global $exped;
			global $clave_anterior;
			global $sede;
			global $mensaje_error;
			
			$user = new ODBC_Conn("usersdb","scael","c0n_4c4");
			$dSQL = "SELECT userid FROM USUARIOS WHERE userid='$exped' and password is null ";
			$user->ExecSQL($dSQL);
			if ($user->filas == 1){
				$sinError=true;
			}
			else {
				$sinError=false;
				$user = new ODBC_Conn("usersdb","scael","c0n_4c4");
				$dSQL = "SELECT password FROM USUARIOS WHERE userid='$exped' ";
				$user->ExecSQL($dSQL);
				if ($user->filas == 1){
					$datos_clave = $user->result[0];
					$clave_anterior = $datos_clave[0];
				}
				$mensaje_error = 'Usted ya tiene una Clave registrada, contacte al personal de soporte.';
			}
			//print $datos_p[0];
            return $sinError;
		  }

    function volver_a_indice($vacio,$fueraDeRango){
    //regresa a la pagina principal:
    if ($vacio) {
?>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <META HTTP-EQUIV="Refresh" 
            CONTENT="0;URL=<?php echo $raizDelSitio . 'insc_login.php'; ?>">
        </head>
        <body>
        </body>
        </html>
<?php
    }
    else {
?>          <script languaje="Javascript">
          <!--
		function entrar_error() {
			mensaje='Cédula incorrecta. Por favor intente de nuevo';
			alert(mensaje);
            window.close();
        //return true; 
		}	
            //-->
            </script>
        </HEAD>
             <body onload = entrar_error(); > 
			 </body>
        </HTML>
<?php
    }
} 


	function imprime_primera_parte($dp) {
    
	global $archivoAyuda,$raizDelSitio,$vicerec;
	global $exped, $nombres, $clave_anterior;
	
	print "<SCRIPT LANGUAGE=\"Javascript\">\n<!--\n";
    print "chequeo = false;\n";
	
    print "ced=\"".$dp[0]."\";\n";
    print "exp_e=\"".$dp[1]."\";\n";
    print "nombres=\"".$dp[2]."\";\n";
    print "apellidos=\"".preg_replace("/\"/","'",$dp[3])."\";\n";
    print "carrera=\"".$dp[4]."\";\n";
    print "CancelPulsado=false;\n";  
    print "var miTempo;\n";  
    print "// --></SCRIPT> \n";
	$titulo = 'Asignaci&oacute;n de Claves';
		
	$instrucciones =$archivoAyuda;
	
	print <<<P001
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/md5.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/inscripcion.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/popup.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/popup3.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>

<SCRIPT LANGUAGE="JavaScript">
    function validar_claves(f) {
		if ((f.clave1.value == "") || (f.clave2.value == "")) {
			alert('Se requiere escriba la clave');
	        return false;
		}
		else{
			if (f.clave1.value == f.clave2.value){
				f.contra.value = hex_md5(f.clave1.value);
				f.clave1.value = "";
				f.clave2.value = "";
				return true;
			}
			else {
				alert('Debe escribir la misma clave en ambas cajas de texto');
				return false;
			}
		}
    }

	function valida_claveA(f) {
		cl = "$clave_anterior";
		if ((f.claveA.value == "") || (f.clave1.value == "") || (f.clave2.value == "")) {
			alert('Se requiere escriba la clave');
	        return false;
		}
		else{

			if (f.clave1.value == f.clave2.value){
				if (cl == hex_md5(f.claveA.value) ){  
					f.contra.value = hex_md5(f.clave1.value);
					f.clave1.value = "";
					f.clave2.value = "";
					return true;
				}
				else{
					alert('La clave anterior no coincide con la almacenada en la base de datos.');
					return false;
				}	
			}
			else {
				alert('Debe escribir la misma clave en ambas cajas de texto');
				return false;
			}
		}
    }

  </SCRIPT>
	  
<style type="text/css">
<!--
#prueba {
  overflow:hidden;
  color:#00FFFF;
  background:#F7F7F7;
}

.titulo {
  text-align: center; 
  font-family:Arial; 
  font-size: 14px; 
  font-weight: normal;
  margin-top:0;
  margin-bottom:0;	
}
.tit14 {
  text-align: center; 
  font-family:Arial; 
  font-size: 14px; 
  font-weight: bold;
}
.instruc {
  font-family:Arial; 
  font-size: 12px; 
  font-weight: normal;
  background-color: #FFFFCC;
}
.datosp {
  text-align: left; 
  font-family:Arial; 
  font-size: 13px; 
  font-weight: normal;
  background-color:#F7F7F7; 
}
.enc_p {
  color:#FFFFFF;
  text-align: center; 
  font-family:Helvetica; 
  font-size: 11px; 
  font-weight: bold;
  background-color:#3366CC;
  height:20px;
}
.inact {
  text-align: center; 
  font-family:Arial; 
  font-size: 12px; 
  font-weight: normal;
  background-color:#F7F7F7; 
}
.act { 
  text-align: center; 
  font-family:Arial; 
  font-size: 12px; 
  font-weight: normal;
  background-color:#99CCFF;
}

DIV.peq {
   font-family: Arial;
   font-size: 9px;
   z-index: 1;
}
select.peq {
   font-family: Arial;
   font-size: 9px;
   z-index: 1;
}

-->
</style>  
</head>

<body  onload="javascript:self.focus();document.forms.claves.clave1.focus();" >

<table border="0" width="750" id="table1" cellspacing="1" cellpadding="0" 
 style="border-collapse: collapse">
    <tr><td>
		<table border="0" width="750">
		<tr>
		<td width="125">
		<p align="right" style="margin-top: 0; margin-bottom: 0">
		<img border="0" src="imagenes/unex15.gif" 
		     width="75" height="75"></p></td>
		<td width="500">
		<p class="titulo">

		Universidad Nacional Experimental Polit&eacute;cnica</p>
		<p class="titulo">
		$vicerec</font></p>
		<p class="titulo">
		</font></td>
		<td width="125">&nbsp;</td>
		</tr><tr><td colspan="3" style="background-color:#99CCFF;">
		<font style="font-size:2px;"> &nbsp;</font></td></tr>
	    </table></td>
    </tr>
    <tr>
        <td width="750" class="tit14"> 
         $titulo</td>

    </tr>
    <tr>
    <td width="570"><br>
        <div class="tit14">Datos del Estudiante</div>
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="570">
            <tbody>
                <tr>
                    <td style="width: 250px;" class="datosp">
                        Apellidos:</td>
                    <td style="width: 250px;" class="datosp">
                        Nombres:</td>
                    <td style="width: 110px;" class="datosp">
                        C&eacute;dula:</td>
                    <td style="width: 114px;" class="datosp">
                        Expediente:</td>
                </tr>

                <tr>
                    <td style="width: 250px;"  class="datosp">
P001
;
        print $dp[3];
        print <<<P002
                    </td>
                    <td style="width: 250px;" class="datosp">
P002
;
        print $dp[2];
        print <<<P003
                    </td>
                    <td style="width: 110px;" class="datosp">
P003
;
        print $dp[0];
        print <<<P004
                    </td>
                    <td style="width: 114px;" class="datosp">
P004
;       print $dp[1];
        print <<<P005
                    </td>
                <tr>
                    <td colspan="4" class="datosp">
P005
;
        print "Especialidad: $dp[4]</td>\n";
        print <<<P003
                </tr>
				<tr>
				  <td colspan="4" class="peq">&nbsp;</td>
				</tr>
								
            </tbody>
        </table>
    </td>
    </tr>
    <tr>
P003
; 
    }


    function imprime_ultima_parte($dp) {
		global $exped, $nombres;
	print <<<U001
     <tr width="500" >
		<td width="500" align="center">
			<font face="Arial" size="2"><br>
			Por favor escriba la clave, esta puede ser hasta 20 d&iacute;gitos alfanum&eacute;ricos</font>
        <table bgcolor="#EFEFEF" align="center" border="0" cellpadding="8" 
            cellspacing="0" width="500">
          <tbody>
          <form action="aceptar.php" method="post" width="500" align="center" name="claves"
				onSubmit="return validar_claves(this)" >
			<p><tr>
				<td  width="230" align="center"><p><font size="2" face="Arial">&nbsp;Clave:&nbsp;</font>
				<INPUT TYPE="password" NAME="clave1" size="21" maxlength="20" tabindex="1"></td>

				<td width="270" align="center"><font size="2" face="Arial">&nbsp;Repetir Clave:&nbsp;</font>
				<INPUT TYPE="password" NAME="clave2" size="21" maxlength="20" tabindex="2"></td>
			</tr>
			<tr width="500" >
				<td colspan="2"> 
				<table align="center" border="0" width="500">
				<tbody>
					<tr>
						<td valign="top"><p align="center">
							<input type="reset" value="Borrar" name="B1">
						</td>
						<td valign="top"><p align="center">
							<input type="button" value="Salir" name="B1"
							onclick="javascript:self.close();"> 
						</td>
						<td valign="top"><p align="center">
							<input type="submit" value="Aceptar">
						</td>
							<input value="x" name="contra" type="hidden"> 
							<input value="$exped" name="exp" type="hidden">
							<input value="$nombres" name="nombres" type="hidden">
					</tr>
				</tbody>
				</table>
				</td>
			</tr>
          </form>  
          </tbody>
        </table>
        </td>
     </tr>
   <tr width="570" >
        <td >
        <table align="center" border="0" cellpadding="0" 
            cellspacing="0" width="400">
          </table>
        </td>
    </tr>
 </table>
</body>
</html>
U001
;
    }

	function imprime_cambio_clave() {
		global $exped, $nombres, $clave_anterior;
	print <<<U001
     <tr width="500" >
		<td width="500" align="center">
			<font face="Arial" size="2"><br>
			Por favor escriba la clave, esta puede ser hasta 20 d&iacute;gitos alfanum&eacute;ricos</font>
        <table bgcolor="#EFEFEF" align="center" border="0" cellpadding="8" 
            cellspacing="0" width="300">
          <tbody>
          <form action="aceptar.php" method="post" width="200" align="center" name="claves"
			onSubmit="return valida_claveA(this)"
				 >
			<p><tr>
				<td  width="100" align="center"><p><font size="2" face="Arial">&nbsp;
				Nueva Clave:&nbsp;</font>
				<INPUT TYPE="password" NAME="clave1" size="21" maxlength="20" tabindex="1"></td>

				<td width="100" align="center"><font size="2" face="Arial">&nbsp;
				Repetir Clave:&nbsp;</font>
				<INPUT TYPE="password" NAME="clave2" size="21" maxlength="20" tabindex="2"></td>
				
				<td  width="100" align="center"><p><font size="2" face="Arial">&nbsp;
				Clave Anterior:&nbsp;</font>
				<INPUT TYPE="password" NAME="claveA" size="21" maxlength="20" tabindex="3"></td>
			</tr>
			<tr width="300" >
				<td colspan="3"> 
				<table align="center" border="0" width="200">
				<tbody>
					<tr>
						<td valign="top"><p align="center">
							<input type="reset" value="Borrar" name="C1">
						</td>
						<td valign="top"><p align="center">
							<input type="button" value="Salir" name="C1"
							onclick="javascript:self.close();"> 
						</td>
						<td valign="top"><p align="center">
							<input type="submit" value="Cambiar Clave" >
						</td>
							<input value="x" name="contra" type="hidden"> 
							<input value="$exped" name="exp" type="hidden">
							<input value="$nombres" name="nombres" type="hidden">
					</tr>
				</tbody>
				</table>
				</td>
			</tr>
          </form>  
          </tbody>
        </table>
        </td>
     </tr>
   <tr width="570" >
        <td >
		  <table align="center" border="0" cellpadding="0" 
            cellspacing="0" width="400">
          </table>
        </td>
    </tr>
 </table>
</body>
</html>
U001
;
    }

    // Programa principal
    //leer las variables enviadas
	if(isset($_POST['cedula'])) {
        $cedula=$_POST['cedula'];
        // limpiemos la cedula y coloquemos los ceros faltantes
        $cedula = ltrim(preg_replace("/[^0-9]/","",$cedula),'0');
        $cedula = substr("00000000".$cedula, -8);
        $fvacio = false; 
		//echo $cedula.'ppp';
		
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<TITLE><?php echo $tProceso . $lapso; ?></TITLE>
<?php
		
        if(!$fvacio && cedula_valida($cedula)) {
            // ya tenemos en $datos_p los datos personales
                $exped    = $datos_p[1];
                $apellidos= $datos_p[3];
                $nombres  = $datos_p[2];
				$c_carr   = $datos_p[4];
				//echo $cedula.'xxx';
				
				//imprime_primera_parte($datos_p);
				$tieneClave = true;
                if (elUsuarioNoTieneClave($tieneClave)){
					imprime_primera_parte($datos_p);
					imprime_ultima_parte($datos_p);
				}
				else{
					imprime_primera_parte($datos_p);
					imprime_cambio_clave();
				}

		}
        else 
			volver_a_indice(false,false); //cedula  incorrecta
	}
    else volver_a_indice(true,false); //formulario vacio
?>
