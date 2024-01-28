<?php
   // include_once('../clave/inc/vImage.php');
   include_once('inc/odbcss_c.php');
	include_once ('inc/config.php');
	include_once ('inc/activaerror.php');
	//	$sede = "dacebqto";
	$datos_p = array();
	//$sede = "daceccs";
	//$sede = "DRIVER={Centura SQLBase 3.5 32-bit Driver -NT & Win95};SRVR=Server1;DB=dace;uid=c;pwd=c";
	//$sede1 = "DRIVER={Centura SQLBase 3.5 32-bit Driver -NT & Win95};SRVR=Server1;DB=usersdb;uid=scael;pwd=c0n_4c4";

		function elUsuarioExiste($sinError) {
			global $exp;
			global $datos_p;
			global $sede;
			
			$user = new ODBC_Conn("usersdb","scael","c0n_4c4");
			$dSQL = "SELECT userid FROM USUARIOS WHERE userid='$exp' ";
			$user->ExecSQL($dSQL);
			if ($user->filas == 1){
				$cv = ($exp == $user->result[0][0]); 
				$datos_p = $user->result[0];
				$sinError=true;
			}
			else 
				$sinError=false;
			//print $datos_p[0];
           	return $sinError;
		  }	


		function elUsuarioNoTieneClave(&$sinError) {
			global $exp;
			global $datos_p;
			global $sede;
			global $mensaje_error;
			
			$user = new ODBC_Conn("usersdb","scael","c0n_4c4");
			$dSQL = "SELECT userid FROM USUARIOS WHERE userid='$exp' and password is null ";
			$user->ExecSQL($dSQL);
			if ($user->filas == 1){
				$sinError=true;
			}
			else {
				$sinError=false;
				$mensaje_error = 'Usted ya tiene una Clave registrada, contacte al personal de soporte';
			}
			//print $datos_p[0];
            return $sinError;
		  }

		function restablecerClave(&$sinError) {
			//global $CUDB, $id_usuario, $cedula;
			global $sede;
			global $datos_p;
			global $contra;
			global $exp;
			global $Clave;

			$Clave = new ODBC_Conn("USERSDB","ASCAEL","adc0n4c4");
						
			$dSQL = "UPDATE usuarios SET password ='$contra' WHERE userid='$exp'";
			$Clave->ExecSQL($dSQL, __LINE__,true); 
			$sinError = ($Clave->fmodif == 1) && ($Clave->status =='OK');
			return $sinError;
		
            //$CUDB->ExecSQL($dSQL,__LINE__,true);
            //$sinError = ($CUDB->fmodif == 1) && ($CUDB->status =='OK');
			//return $sinError;
		  }

		  function agregarUsuario(&$sinError) {
			//global $CUDB, $cedula, $Stipo_usuario, $nombre, $apellido;
			//global $id_usuario, $ODBCNucleo;
			global $sede;
			global $datos_p;
			global $contra;
			global $exp;
			global $Clave;
			global $Nucleo;
			global $nombres;
			
			//nueva clave: los 6 ultimos digitos de la cedula
			//$nuevaClave = md5(substr($cedula, -6));
			// tipo_usuario = 'estudiante')

			$tipo_usuario = 100;
			//$elNombre = $datos_p[3];
			$Clave = new ODBC_Conn("USERSDB","ASCAEL","adc0n4c4");
			$dSQL  = "INSERT INTO usuarios (userid, password, nombre, ";
			$dSQL .= "checkmail, hidemenu, hidepass, hidemail, hideqa, ";
			$dSQL .= "hideusers, hidelast, lasttime, times, campus, ";
			$dSQL .= "tipo_usuario) values ('".$exp."', '".$contra;
			$dSQL .= "', '".$nombres."', 0, 0, 0, 0, 0, 0, 0, 0, 0, ";
			$dSQL .= "'".$Nucleo."', ".$tipo_usuario.")";
            $Clave->ExecSQL($dSQL,__LINE__,true);
            $sinError = ($Clave->fmodif == 1) && ($Clave->status =='OK');
			return $sinError;
		  }
			
		  function imprimirTodoOK() {
			  global $datos_p, $exp, $nombres, $mensaje_error;
			?>
              <font size="3"  face="Arial" >
                <p><center><strong> <?php echo ' '.$nombres.', '; ?> la Clave ha sido registrada
				</strong>
				
				</font>
			  <font size="2" face="Arial" >
				<p> <?php print 'Por favor cierre esta ventana'; ?>
			  </font>
			  
			<?php
			
		  }
		  function imprimirError() {
			  global $datos_p, $exp, $nombres,$mensaje_error;
			?>
              <font size="3" color="#330000" face="Arial" >
                <center><strong><?php echo ' '.$nombres.', '.$mensaje_error; ?>
					</strong>
					
				<p> <?php print 'Por favor cierre esta ventana'; ?>
				</center>
              </font>

			<?php
		  }

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<title><?php echo $tProceso . $lapso; ?></title>

<?php
	 if (!empty($_POST['exp']) && (!empty($_POST['contra'])))  {
		$exp = $_POST['exp'];
		$contra = $_POST['contra'];
		$nombres = $_POST['nombres'];
		
		//echo 'ahora a guardar';
		//echo $exp;
		$sinError = true;
		$mensaje_error = 'No se pudo registrar la clave. Por favor, tome nota de la hora actual, los datos del usuario y contacte al personal de soporte.';
		if (elUsuarioExiste($sinError)) {
				//if (elUsuarioNoTieneClave($sinError)) {
					restablecerClave($sinError);
				//}
		}
		else {
			agregarUsuario($sinError);
		}
		if ($sinError){
			imprimirTodoOK();
		}
		else {
				imprimirError();
		}
	}
?>
</html>