<?php
  session_name("scael");
  session_start();
  include_once ('Include\\SiCoDoVi.php');
  include_once ('buscar\\odbcss_c.php');

  if (!session_is_registered('SiCoDoVi') || ($SiCoDoVi != session_id()) ||
      !session_is_registered('userid') || !session_is_registered('expediente') || 
	  !session_is_registered('ODBCNucleo') || !session_is_registered('tipoUsu')) {
    $Invalid_Link = true;
  } else {
    $Invalid_Link = false;
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title><? echo $System_Title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="imagetoolbar" content="no">
<?php
    if ($Invalid_Link || !(isset($_POST['id_usuario']))) {
?>
      <meta http-equiv="Refresh" content="5;URL=<? echo $link ?>">
<?php
    }
?>
    <script language="JavaScript" src="Scripts/Scripts.js" type="text/javascript">
    <!--
    //-->
    </script>
    <script language="JavaScript" type="text/javascript">
    <!--
      window.resizeTo(800,575);
      //expand();
      function MM_preloadImages() { //v3.0
        var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
        var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
        if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
      }
      function MM_swapImgRestore() { //v3.0
        var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
      }
      function MM_findObj(n, d) { //v4.01
        var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
        if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
        for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
        if(!x && d.getElementById) x=d.getElementById(n); return x;
      }
      function MM_swapImage() { //v3.0
        var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
        if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
      }
    //-->
    </script>
    <!-- Aplicación de estilos -->
    <link href="Styles/Styles.css" rel="stylesheet" type="text/css">
    <? include_once ('Styles/Styles.php') ?>
    <!-- Fin de Aplicación de estilos -->
  </head>
  <body bgcolor="<? echo $Back_Color; ?>" onLoad="MM_preloadImages('Buttons/short_b5_over.gif','Buttons/mid_b3_over.gif')">
<?php
    Check_Link ($Invalid_Link);
	$UsersDBID = 'ASCAEL';
	$UsersDBPass = 'adc0n4c4'; 
	$conexionUSERS = odbc_connect ($UsersDBName, $UsersDBID, $UsersDBPass);
    if (!$conexionUSERS) die (DB_Error);
    $consultaSQL = "SELECT DBLOGGING, DBPASSWORD FROM NUCLEOS WHERE (ODBC = '$ODBCNucleo')";
    $resultadoUSERS = odbc_do ($conexionUSERS, $consultaSQL);
    if (!$resultadoUSERS) {
      Error_Mailer ($AppName, __FILE__, __LINE__, Do_Error, $consultaSQL);
      odbc_close ($conexionUSERS);
      die ('');
    }
    $DBlogging = odbc_result ($resultadoUSERS, "DBLOGGING");
    $DBpassword = odbc_result ($resultadoUSERS, "DBPASSWORD");
    odbc_free_result ($resultadoUSERS);
?>
    <table width="756" border="0" cellpadding="0" cellspacing="0">
      <tr bgcolor="<? echo $Back_Color; ?>">
        <td width="753" height="88">
          <center>
            <img src="<? echo $Header_Image ?>" width="750" height="88" alt="">
          </center>
        </td>
      </tr>
      <tr>
        <td height="18" align="right">
          <font color="<? echo $Normal_Font; ?>" size="<? echo $Small_Font_Size ?>"
		        face="<? echo $Small_Font ?>">
<?php
            $Date = getdate ();
            echo Date_String ($Date).' '. date('h:i:s A');
?>
		  </font>
        </td>
      </tr>
      <tr>
        <td>
          <br>
<?php

		  function elUsuarioExiste(&$sinError) {
			global $CUDB, $id_usuario;

			$dSQL = "SELECT userid FROM USUARIOS WHERE userid='".$id_usuario."'";
            $CUDB->ExecSQL($dSQL,__LINE__, false);
            $sinError = ($CUDB->filas == 1) && ($CUDB->status =='OK');
			return $sinError;
		  }

		  function restablecerClave(&$sinError) {
			global $CUDB, $id_usuario, $cedula;

			//nueva clave: los 6 ultimos digitos de la cedula
			$nuevaClave = md5(substr($cedula, -6));
			$dSQL = "UPDATE usuarios SET password ='".$nuevaClave . "' WHERE userid='".$id_usuario."'";
            $CUDB->ExecSQL($dSQL,__LINE__,true);
            $sinError = ($CUDB->fmodif == 1) && ($CUDB->status =='OK');
			return $sinError;
		  }

		  function agregarUsuario(&$sinError) {
			global $CUDB, $cedula, $Stipo_usuario, $nombre, $apellido;
			global $id_usuario, $ODBCNucleo;
			//nueva clave: los 6 ultimos digitos de la cedula
			$nuevaClave = md5(substr($cedula, -6));
			if ($Stipo_usuario == 'estudiante'){
				$tipo_usuario = 100;
				$elNombre = $nombre;
			}
			else {
				$tipo_usuario = 510;
				$elNombre = $nombre.' '.$apellido;
			}

			$dSQL  = "INSERT INTO usuarios (userid, password, nombre, ";
			$dSQL .= "checkmail, hidemenu, hidepass, hidemail, hideqa, ";
			$dSQL .= "hideusers, hidelast, lasttime, times, campus, ";
			$dSQL .= "tipo_usuario) values ('".$id_usuario."', '".$nuevaClave;
			$dSQL .= "', '".$elNombre."', 0, 0, 0, 0, 0, 0, 0, 0, 0, ";
			$dSQL .= "'".$ODBCNucleo."', ".$tipo_usuario.")";
            $CUDB->ExecSQL($dSQL,__LINE__,true);
            $sinError = ($CUDB->fmodif == 1) && ($CUDB->status =='OK');
			return $sinError;
		  }
			
		  function imprimirTodoOK() {
			  global $cedula;
			?>
              <font size="<? echo $Small_Font_Size ?>"
			        color="<? echo $Normal_Font; ?>" face="<? echo $Small_Font ?>">
                <strong>Se ha restablecido la clave del usuario a los seis &uacute;ltimos 
				d&iacute;gitos de su c&eacute;dula. EL USUARIO DEBE CAMBIAR ESTA CLAVE TAN 
				PRONTO COMO SEA POSIBLE, INGRESANDO AL SISTEMA DE CONSULTA ACAD&Eacute;MICA.
				</strong>
              </font>

			<?php
		  }
		  function imprimirError() {
			  global $cedula;
			?>
              <font size="<? echo $Small_Font_Size ?>"
			        color="<? echo $Normal_Font; ?>" face="<? echo $Small_Font ?>">
                <strong>No se pudo restablecer la clave del usuario con c&eacute;dula 
				<?php echo ' '.$cedula.'. '; ?>
				Por favor, tome nota de la hora actual y datos del usuario y 
				contacte al personal de soporte.</strong>
              </font>

			<?php
		  }

		  Online_Users (false);
          $consultaSQL = "SELECT USERIP FROM ONLINE WHERE USERIP = '$userid'";
          $exec = odbc_do ($conexionUSERS, $consultaSQL);
          if (!$exec) die (Do_Error);
          if (odbc_result ($exec, 1) != '') {
            // El usuario tiene sesión activa y puede realizar la consulta
            odbc_free_result ($exec);
			$CUDB = new ODBC_Conn($UsersDBName, $UsersDBID, $UsersDBPass, $ODBCC_conBitacora, 'cambiaclave.log', $userid);
			$sinError = true;
			if (elUsuarioExiste($sinError)) {
				restablecerClave($sinError);
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
          } //if (odbc_result)
		  else {
?>
              <font size="<? echo $Small_Font_Size ?>"
			        color="<? echo $Normal_Font; ?>" face="<? echo $Small_Font ?>">
                <strong>Lo siento, pero su sesi&oacute;n ha caducado por tiempo de inactividad, y no podr&aacute; realizar la consulta solicitada.</strong>
                <br>Deber&aacute; salir del sistema y volver a entrar para poder realizar su consulta.
              </font>
<?php
		  }
		   Display_Close_Button ();
           odbc_close ($conexionUSERS);
?>
        </td>
      </tr>
    </table>
  </body>
</html>