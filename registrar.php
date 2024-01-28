<?php
    include_once('../inc/odbcss_c.php');
	include_once('../inc/config.php');
	include_once ('../inc/activaerror.php');

    $datos_p = array();
    $asignat = array();
    $errstr  = "";
	$sede    = "";

	//$Cmat    = new ODBC_Conn($sede,"usuario2","usuario2",$ODBCC_conBitacora,'insc.log');
    $fecha  = date('Y-m-d', time() - 3600*date('I'));
    $hora   = date('h:i:s', time() - 3600*date('I'));
    $ampm   = date('A', time() - 3600*date('I'));
    $todoOK = true;
    $secc   =  "";
    $statusI = array();
    $inscrito = 0;

    function print_error($f,$sqlerr){
    
    print "<pre>".$f."\n".$sqlerr."</pre>";
    }
    
	function leer_datos_p($exp_e) {
        global $datos_p;
        global $errstr;
        global $E;
		global $sede;
		global $ODBCC_sinBitacora;
    
		if ($exp_e != ""){
            $Cdatos_p = new ODBC_Conn($sede,"c","c",$ODBCC_sinBitacora);
            $dSQL = " SELECT ci_e, exp_e, nombres, apellidos ";
            $dSQL = $dSQL."FROM DACE002 WHERE exp_e='".$exp_e."'";
            $Cdatos_p->ExecSQL($dSQL,__LINE__,false);
            $datos_p = $Cdatos_p->result[0];
            return (true);
            
        }
        else return(false);      
    }
    
    function reportar_error($errstr,$impmsg = true) {
	//global $errstr;
    if($impmsg) {
       print <<<E001
   
    <tr><td><pre> 
            Disculpe, Existen problemas con la conexi&oacute;n al servidor, 
            por favor contacte al personal de URACE e intente m&aacute;s tarde
    </pre></td></tr>
E001
;
    }
    $error_log=date('h:i:s A [d/m/Y]').":\n".$errstr."\n";
//    file_put_contents('errores.log', $error_log, FILE_APPEND);
}
    function consultar_datos($sinCupo) {
        
        global $ODBCSS_IP;
        global $datos_p; 
        global $asignat;
        global $errstr;
        global $lapso;
        global $inscribe;
        global $sede;
		global $Cmat;
		global $inscrito;
        
		$actBitacora = (intval('0'.$inscrito) != 1 || intval('0'.$inscribe)==2 ); 
		//actualiza bitacora si no es solo reporte;
        $todoOK = true;       
        //$Cdep = new ODBC_Conn($sede,"usuario2","usuario2", $ODBCC_conBitacora, $laBitacora);
        $dSQL = "SELECT A.c_asigna, asignatura, unid_credito, seccion, status FROM tblaca008 A, dace006 B ";
        $dSQL = $dSQL."WHERE exp_e='".$datos_p[1]."' AND lapso='$lapso' AND A.c_asigna = B.c_asigna";
        $Cmat->ExecSQL($dSQL,__LINE__); 
        if ($todoOK) {
            $asignat = $Cmat->result;
            if (!$sinCupo && $actBitacora) {
                $dSQL = "UPDATE orden_inscripcion set inscrito='1'";
                $dSQL = $dSQL." WHERE ord_exp='$datos_p[1]'";
                $Cmat->ExecSQL($dSQL, __LINE__); 
				//actualizamos sexo y fecha de nacimiento:
                $dSQL = "UPDATE dace002 set sexo='".$_POST['sexo']."', ";
				$dSQL = $dSQL."f_nac_e='".$_POST['f_nac_e']."'"; 
                $dSQL = $dSQL." WHERE exp_e='$datos_p[1]'";
                $Cmat->ExecSQL($dSQL, __LINE__,$actBitacora); 
            }
         }
        return($todoOK);        
    }

    function reportarInscripcion() {
        
        global $asignat, $datos_p;
        $tot_dep = 0;
		$firma = "";        
        print <<<R001
    <tr><td>&nbsp;</td>
    </tr>
        <tr><td width="750">
        <TABLE align="center" border="1" cellpadding="0" cellspacing="1" width="550">
        <TR><TD>
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="550">
            <tr>
                <td style="width: 60px;" nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="matB">C&Oacute;DIGO</div></td>
                <td style="width: 300px;" bgcolor="#FFFFFF">
                    <div class="matB">ASIGNATURA</div></td>
                <td style="width: 60px;" nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="matB">U.C.</div></td>
                <td style="width: 60px;" nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="matB">SECCI&Oacute;N</div></td>
                <td style="text-align:center; width: 70px;" nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="matB">ESTATUS</div></td>
            </tr>

R001
;
        $total=count($asignat);
        for ($i=0;$i<$total;$i++) {
            $sEstatus = array(2=>'RETIRADA', 7=>'INSCRITA', 9=>'INCLUIDA','C'=>'CENSADA');
			if (intval('0'.$asignat[$i][4],10)>0){
				$firma .= $asignat[$i][0].$asignat[$i][3].$asignat[$i][4]." ";
				print <<<R002
            <tr>
                <td nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="mat">{$asignat[$i][0]}</div></td>
                <td bgcolor="#FFFFFF">
                    <div class="mat">{$asignat[$i][1]}</div></td>
                <td nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="mat">{$asignat[$i][2]}</div></td>
                <td nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="mat">{$asignat[$i][3]}</div></td>
                <td nowrap="nowrap" bgcolor="#FFFFFF">
                    <div class="mat">{$sEstatus[$asignat[$i][4]]}</div></td>
            </tr>

R002;
			}
        }
		$key = substr(md5("320c6711"),0,16);
		srand();
		$td = mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_ECB, '');
		if(!$td) { print 'fallo';}
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$firma3D = mcrypt_generic($td, $firma);
		mcrypt_generic_deinit($td);

		//$firma = strrev($firma.' '.strrev(substr($datos_p[0],-4)));
		//$firmaGZ = str_rot13(bin2hex(gzdeflate($firma, 9)));
		//$firmaL .="[". strlen($firmaGZ) ."]";				
		//$firmaDZ = strrev(gzinflate(pack("H*",str_rot13($firmaGZ))));
		//$firmaDZ = ((pack("H*",$firmaGZ)));		//echo $firmaDZ;
		//$firmaN .="[". strlen($firmaDZ) ."]";
		//$firmaGZp = str_pad($firmaGZ,100, " ", STR_PAD_RIGHT);
		//$firma1 = substr($firmaGZp,0,25);
		//$firma2 = substr($firmaGZp,25,25);
		//$firma3 = substr($firmaGZp,50,25);
		//$firma4 = substr($firmaGZp,75,25);
		//mcrypt_generic_init($td, $key, $iv);
		//$firmaDZ = mdecrypt_generic($td, $firmaDZ);
		//mcrypt_generic_deinit($td);
		mcrypt_module_close($td); 
		$firmaMD5 = strtoupper(md5($firma3D));
		$firma1 = substr($firmaMD5,0,16);
		$firma2 = substr($firmaMD5,16,32);

        print <<<R003
        </table>
        </TR></TD></TABLE>
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="550">
          <tr>
             <td colspan="2"> &nbsp; </td>
          </tr>
          <tr><form name="imprime" action="">
               <td valign="bottom"><p align="left">
                    <input type="button" value=" Imprimir " name="bimp"
                         style="background:#FFFF33; color:black; font-family:arial; font-weight:bold;" onclick="imprimir(document.imprime)"></p> 
               </td>
               <td valign="bottom"><p align="left">
                       <input type="button" value="Finalizar" name="bexit"
                        onclick="verificarSiImprimio()"></p> 
                </td></form>
          </tr>
          <tr>
             <td>&nbsp;</td>
             <td>&nbsp;<br>
                </td>
                <tr>
                <td colspan="2" class="nota">
                La carga acad&eacute;mica inscrita por  el estudiante en esta
                planilla est&aacute; sujeta a control posterior por parte de URACE
                en relaci&oacute;n al cumplimiento de los prerrequisitos y 
                correquisitos sustentados en los pensa vigentes y a las cargas
                acad&eacute;micas m&aacute;ximas establecidas en el
                Reglamento de Evaluaci&oacute;n y Rendimiento Estudiantil vigente.
                La violaci&oacute;n de los requisitos y normativas antes mencionados
                conllevar&aacute; a la eliminaci&oacute;n de las asignaturas que no
                los cumplan.
                </td>
            </tr>
		 <tr><td colspan="2" class="matB"><br>C&Oacute;DIGO DE VALIDACI&Oacute;N:<br></td></tr>
		 <tr><td colspan="2" class="mat"><br>$firmaMD5<br></td></tr>
		 <tr><td colspan="2" class="matB">
			<IMG SRC="/inc/barcode.php?barcode={$firma1}&width=350&height=25&text=0" align="center">
		    </td>
		 </tr>
		 <tr><td colspan="2" class="nota">&nbsp;</td></tr>
         <tr><td colspan="2" class="matB">
			<IMG SRC="/inc/barcode.php?barcode={$firma2}&width=350&height=25&text=0" align="center">
		    </td>
		 </tr>
          </table>
        </tr>
        </table>
    </td>
    </tr>

R003
;
        
    }
       
    function asigYaInscrita($asig, $lapso, $i, $deshacer){
            
        global $Cmat;
        global $todoOK;
        global $datos_p;
        global $errstr;
        global $secc;
        global $statusI;
           
        $dSQL   = "SELECT A.seccion, status from dace006 A, ";
        $dSQL   = $dSQL . "tblaca004 B WHERE A.exp_e='$datos_p[1]' AND A.c_asigna='$asig' AND ";
        $dSQL   = $dSQL . "A.c_asigna=B.c_asigna AND A.seccion=B.seccion ";
        $dSQL   = $dSQL . "AND A.lapso=B.lapso AND A.lapso='$lapso'";
        $Cmat->ExecSQL($dSQL,__LINE__);
        $Yainsc = ($Cmat->filas == 1);
        if ($Yainsc) {
            $secc   = $Cmat->result[0][0];
            if (!$deshacer){
                $statusI[$i] = $Cmat->result[0][1];
            }                              
        }
        else {
            if (!$deshacer) {
                $statusI[$i] = '0'; //No inscrita;
            }
            $secc = '';
        }
        return $Yainsc;            
    }
    
    function eliminarAsignatura($asig, $secc, $lapso, $status, $retiro){
            
        global $Cmat;
        global $todoOK;
        global $datos_p;
        global $errstr; 
            
        $sm ='';
        if ($retiro || $status != '0') {
            // la marcamos como retirada o con el estatus anterior
            if ($retiro) { 
                $sm = '2';
            }
            else {
                $sm = $status;
            }
            $dSQL   = "UPDATE dace006 SET status='$sm' WHERE c_asigna='$asig' ";
            $dSQL   = $dSQL . "AND exp_e='$datos_p[1]' AND lapso='$lapso'";
            $Cmat->ExecSQL($dSQL,__LINE__, true);
        }
        else {// lo borramos de la seccion ...
            
            $dSQL   = "DELETE FROM dace006 where c_asigna='$asig' ";
            $dSQL   = $dSQL . "AND exp_e='$datos_p[1]' AND lapso='$lapso'";
            $Cmat->ExecSQL($dSQL,__LINE__,true);
        }
        // Luego actualizamos los inscritos...
        if (($sm == '7') || ($sm == '9')) {
            $actInscritos='inscritos+1'; //hemos deshecho un retiro
            $condInscritos='inscritos>=0';
        }
        else {
            $actInscritos='inscritos-1'; //hemos deshecho una inscripcion o inclusion
            $condInscritos='inscritos>0';
        }
        if ($todoOK && ($Cmat->fmodif == 1)){
            if ($status !='2') {
                $dSQL   = "UPDATE tblaca004 SET inscritos=$actInscritos WHERE ";
                $dSQL   = $dSQL."c_asigna='$asig' AND seccion='$secc' AND lapso='$lapso' AND $condInscritos";
            $Cmat->ExecSQL($dSQL,__LINE__,true);
            }
        }
    }
 
     function borrarTodas($lapso){
        
        global $Cmat;
        global $todoOK;
        global $datos_p;
        global $errstr; 
            
        $dSQL   = "SELECT A.c_asigna, A.seccion from dace006 A, ";
        $dSQL   = $dSQL . "tblaca004 B WHERE A.exp_e='$datos_p[1]' AND ";
        $dSQL   = $dSQL . "A.c_asigna=B.c_asigna AND A.seccion=B.seccion ";
        $dSQL   = $dSQL . "AND A.lapso=B.lapso AND A.lapso='$lapso'";
        $Cmat->ExecSQL($dSQL,__LINE__);
        $hayQueBorrar = ($Cmat->filas > 0);

        if ($todoOK && $hayQueBorrar) {
            foreach($Cmat->result as $bAsig) {
                eliminarAsignatura($bAsig[0], $bAsig[1], $lapso, '0', false);
                if (!$todoOK) {
                    break;
                }    
            }
        }
        return $todoOK;            

    }

    function deshacerTodo($dAsig, $i, $lapso){
        
        global $datos_p;
        global $secc;
        global $statusI;

        $secc = "";
        $k=0;
        while ($k<$i) {
            $asig = $dAsig[$k];
            $iSec = $dAsig[$k+1];
            $iRep = $dAsig[$k+2];
            if (asigYaInscrita($asig, $lapso, $k, true)) {
                eliminarAsignatura($asig, $secc, $lapso, $statusI[$k], false);
            }
            $k=$k+4;
        }    
    }
 
    function inscribirAsignatura($asig, $iSecc, $repite, $lapso){
            
        global $Cmat;
        global $todoOK;
        global $datos_p;
        global $errstr;
        global $E;
        global $inscribe; 
        global $fecha;
        
        $inscrita = false;
        //Buscar nro de acta
        $dSQL   = "SELECT acta FROM tblaca004 WHERE c_asigna='$asig' ";
        $dSQL   = $dSQL . "AND seccion='$iSecc' AND lapso='$lapso'";
        $Cmat->ExecSQL($dSQL,__LINE__);
        if ($todoOK) {
            $acta = $Cmat->result[0][0];
            if ($inscribe == 1) {
                $iStatus = '7'; //modo inscripcion
            }
            else {
                $iStatus = '9';//modo inclusion
            }   
            //Sumar un inscrito y si lo hace entonces proceder a insertar
            $dSQL   = "UPDATE tblaca004 SET inscritos=inscritos+1 WHERE ";
            $dSQL   = $dSQL."c_asigna='$asig' AND seccion='$iSecc' AND lapso='$lapso'";
            $dSQL   = $dSQL. " AND inscritos<tot_cup";
            $Cmat->ExecSQL($dSQL,__LINE__,true);
            if ($Cmat->fmodif == 1){ //se sumo un inscrito, proceder a insertarlo
                $dSQL = "INSERT INTO dace006 (acta, lapso, c_asigna, seccion, exp_e, status, ";
                $dSQL = $dSQL."status_c_nota, fecha) VALUES ('$acta','$lapso','$asig', ";
                $dSQL = $dSQL. "'$iSecc','$datos_p[1]','$iStatus','$repite','$fecha')";
				$Cmat->ExecSQL($dSQL,__LINE__,true);
                $inscrita = ($Cmat->fmodif == 1);
            }
        }
        return($inscrita);
    }
    
    function registrar_asig() {
        
        global $ODBCSS_IP;
        global $datos_p;
        global $errstr;
        global $lapso;
        global $todoOK;
        global $secc;
        global $inscribe;
        global $Cmat;

        //$fecha=date("Y-m-d");                
        $todoOK    = true;
        $aInscrita = false; 
        $dAsig     = array();
        //print_r($_POST['asignaturas']);
        // $_POST['asignaturas'] trae : CODIGO1 SECCION1 condREP1 CODIGO2 SECCION2 condREP2...    
        $dAsig   = explode(" ",$_POST['asignaturas']);
        array_pop($dAsig);
        $total_a = count($dAsig);
        $secc    = "";
        $cupo    = 0;
        $acta    = "";
        $noInscritas ="";
        $i = 0;
        if ($inscribe == '1') {         //si estamos en inscripciones y vuelve a entrar
          $todoOK = borrarTodas($lapso);//ocurrio antes un error. Borrar todo lo inscrito.
        }         
        while ($i<$total_a) {
            $asig = $dAsig[$i];
            $iSec = $dAsig[$i+1];
            $iRep = $dAsig[$i+2];
            //print_r($dAsig);
            $retiro = ($iSec == '-1');
            if (asigYaInscrita($asig, $lapso, $i, false)){
                if ($iSec != $secc) {
                    //eliminar la asignatura con status='0' (borrarla completa)
                    eliminarAsignatura($asig, $secc, $lapso,'0', $retiro);
                    //print "ya inscrita y eliminada $asig $secc<br>";
                }
            }
            if ($todoOK) {
                $aInscrita = ($iSec == $secc);
                if (!$aInscrita && !$retiro) {
                    $aInscrita = inscribirAsignatura($asig, $iSec, $iRep, $lapso);
                    //print "Inscrita $asig $secc<br>";
    
                    if (!$aInscrita) {
                        //print "No inscrita, deshacer $asig $secc<br>";
                        deshacerTodo($dAsig, $i, $lapso);
                        return array($todoOK, true, $asig, $iSec);
                    }
                }
            }
            $i=$i+4;
        }
        return array($todoOK, false, '','');      
    }

     function imprime_h() {
        
        global $hora;
        global $ampm;
        global $datos_p;
        global $lapso;
        global $inscribe;
        
        $fecha = date('d/m/Y', time() - 3600*date('I'));
        if ($inscribe == '1') {
            $titulo = "Inscripci&oacute;n";
        }
        else if ($inscribe == '2'){
            $titulo = "Inclusi&oacute;n y Retiro";
        }
        print <<<TITULO
    <tr>
        <td width="750">
        <p align="center"><font face="Arial" size="3">
        Planilla de $titulo Lapso $lapso</font></p></td>
    </tr>
TITULO
;
?>
    <tr><td width="750">
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="550">
            <tr><td><font size="2" color="#000000" face="Arial"><br>
<?php 
        print "<p align=\"right\"> Barquisimeto, $fecha $hora $ampm </p><br>";
?>   
        </td></tr></table>
        </td>
    </tr>
    <tr>
    <td width="750">
        <font size="2" color="#000000" face="Arial">
            <center><b>DATOS DEL ESTUDIANTE</b></center></font>
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="570">
            <tbody>
                <tr>
                    <td style="width: 250px;" bgcolor="#FFFFFF">
                        <div class="dp">Apellidos:</div></td>
                    <td style="width: 250px;" bgcolor="#FFFFFF">
                        <div class="dp">Nombres:</div></td>
                    <td style="width: 110px;" bgcolor="#FFFFFF">
                        <div class="dp">C&eacute;dula:</div></td>
                    <td style="width: 114px;" bgcolor="#FFFFFF">
                        <div class="dp">Expediente:</font></td>
                </tr>

                <tr>
                    <td bgcolor="#FFFFFF">
                        
<?php
        print <<<P002
                       <div class="dp">{$datos_p[3]}</div></td>
                    <td bgcolor="#FFFFFF">
                       <div class="dp">{$datos_p[2]}</div></td>
                    <td bgcolor="#FFFFFF">
                       <div class="dp">{$datos_p[0]}</div></td>
                    <td style="width: 114px;" bgcolor="#FFFFFF">
                       <div class="dp">{$datos_p[1]}</div></td>
                </tr>
            </tbody>
        </table>
    </td>
    </tr>
    <tr>
    <td width="750">
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="570">
            <tbody>
                <tr>
                    <td style="width: 570px;" bgcolor="#FFFFFF">
                        <div class="dp">Especialidad: {$_POST['carrera']} </div></td>
                </tr>
            </tbody>
        </table>
    </td>
    </tr>
P002
; 
    } //imprime_h   
?>
    
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<?php    
    $inscribeN = 0;
    if (isset($_POST['inscribe'])){
       $inscribe = $_POST['inscribe'];
       $inscribeN = intval('0'.$inscribe);
    }
    if(isset($_POST['exp_e']) && ($inscribeN>0)) {
		$lapso     = $_POST['lapso'];    
		$inscrito  = intval($_POST['inscrito']);
		$sede	   = $_POST['sede'];
//		$Cmat->DSN = $sede;
	    $Cmat      = new ODBC_Conn($sede,"usuario2","usuario2",$ODBCC_conBitacora, $laBitacora);

		leer_datos_p($_POST['exp_e']);
?>  

		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Planilla de Inscripci&oacute;n Lapso <?php print $lapso; ?></title>
  		<script language="Javascript" src="md5.js">
		<!--
		alert('Error con el fichero js');
		// -->
        </script>
		<script languaje="Javascript">
		<!--
<?php
        print "Estudiante = '$datos_p[2]';";
?>
        var Imprimio = false;
        
        function imprimir(fi) {
            with (fi) {
                bimp.style.display="none";
                bexit.style.display="none";
                window.print();
                Imprimio = true;
                msgI = Estudiante + ':\nSi mandaste a imprimir tu planilla\n';
                msgI = msgI + "pulsa el botón 'Finalizar' y ve a retirar tu planilla por la impresora,\n";
                msgI = msgI + 'de lo contrario vuelve a pulsar Imprimir\n';
                //alert(msgI);
                bimp.style.display="block";
                bexit.style.display="block";
            }
        }
        function verificarSiImprimio(){
            window.status = Estudiante + ': NO TE VAYAS SIN IMPRIMIR TU PLANILLA';
            if (Imprimio){
                window.close();
            }
            else {
                msgI = '            ATENCION!\n' + Estudiante;
                alert(msgI +':\nNo te vayas sin imprimir tu planilla');
            }
        }
		<!--
        document.writeln('</font>');
		//-->
        </script>
		<style type="text/css">
		<!--
		.nota {
			text-align: justify; 
			font-family: Arial; 
			font-size: 11px; 
			font-weight: normal;
			color: black;
		}
		.mat {
			text-align: center; 
			font-family: Arial; 
			font-size: 12px; 
			font-weight: normal;
			color: black;
			vertical-align: top;
		}
		.matB {
			font-family:Arial; 
			font-size: 11px; 
			font-weight: bold;
			color: black; 
			text-align: center;
			vertical-align: top;
		}
		.dp {
			font-family:Arial; 
			font-size: 14px; 
			font-weight: normal;
			color: black; 
		}
		-->
		</style>
		</head>
        <body  onload="javascript:self.focus();" 
		      onclose="return false">
		<table border="0" width="750" id="table1" cellspacing="1" cellpadding="0" 
			   style="border-collapse: collapse">
			<tr><td>
				<table align="center" border="0" width="600" id="table2" bordercolor="#808080">
				<tr>
					<td width="100" bordercolor="#FFFFFF" 
					    bordercolorlight="#FFFFFF" bordercolordark="#FFFFFF">
					<p align="right" style="margin-top: 0; margin-bottom: 0">
					<img border="0" src="imagenes/unex1bw.jpg" 
					     width="75" height="75"></p></td>
					<td width="400" bordercolor="#FFFFFF" 
					    bordercolorlight="#FFFFFF" bordercolordark="#FFFFFF">
					<p align="center" style="margin-top: 0; margin-bottom: 0">
					<font face="Arial" size="2" >
					Universidad Nacional Experimental Polit&eacute;cnica</p>
					<p align="center" style="margin-top: 0; margin-bottom: 0">
					<font face="Arial" size="2" >
					Vicerrectorado Barquisimeto</font></p>
					<p align="center" style="margin-top: 0; margin-bottom: 0">
					<font face="Arial" size="2">
					Unidad Regional de Admisi&oacute;n y Control de Estudios</font></td>
					<td width="100">&nbsp;</td>
				</tr>
			    </table></td>
			</tr>
<?php
        if (intval('0'.$inscrito) != 1 || $inscribeN=2){
            list ($inscOK, $sinCupo, $asig, $seccion) = registrar_asig();
			//$Cmat->escribirBitacora();
//            reportar_error($errstr,false);
        }
        else {
            $inscOK = true;
            $sinCupo = false;
        }
        if ($inscOK){
            $datosOK = consultar_datos($sinCupo);
            if (!$sinCupo){
                imprime_h();
                reportarInscripcion();
                reportar_error($errstr,false);
            print <<<FINAL0
        </td></tr>
        </table>
        </body>
        </html>
FINAL0
;        
            }
            else if (!$datosOK) {
                imprime_h();
                reportar_error($errstr);
                print <<<FINAL1
        </td></tr>
        </table>
        </body>
        </html>
FINAL1
;
                exit;
            }
            if ($sinCupo) { //reportar el error de sin cupo
            reportar_error($errstr,false);    
            print <<<ERRORSC
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>Asignatura sin cupo : $asig, Secci&oacute;n: $seccion</title>
        </head>
        <body   onload="javascript:self.focus()">
        <form name ="sincupo" method="POST" action="planilla_r.php">
            <input type="hidden" name="cedula" value="{$_POST['cedula']}">
            <input type="hidden" name="contra" value="{$_POST['contra']}">
            <input type="hidden" name="asignaturas" value="{$_POST['asignaturas']}">
            <input type="hidden" name="asigSC" value="$asig">
            <input type="hidden" name="seccSC" value="$seccion">
            <input type="submit" name="enter" value="$seccion">
        </form>
        <script languaje="Javascript">
        <!--
        with (document){
    //alert('No hay cupo en la'+ sincupo.asigSC.value +' ' + sincupo.seccSC.value + '\\n' + sincupo.asignaturas.value);
     //      sincupo.submit();
        }
        -->
        </script>
        </body>
</html>

ERRORSC
;        
            } //if($sinCupo)
        
        }//if insc_ok
        else {
            imprime_h();
            reportar_error($errstr);
            print <<<FINAL2
        </td></tr>
        </table>
        </body>
        </html>
FINAL2
;        
        }
    } //if(isset($_POST['exp_e']) && ($inscribeN>0))
    else {
?>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <META HTTP-EQUIV="Refresh"
        CONTENT="0;URL=<?php echo $raizDelSitio; ?>">
        </head>
        <body>
        </body>
        </html>
<?php
    }

?>
