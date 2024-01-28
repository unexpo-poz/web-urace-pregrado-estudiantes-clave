function marcarAsignaturas(asignaturas,asigSC) {

    var cod_uc = new Array();
    scod_uc = "";
    asigs = asignaturas.split(" ");
    with (document.pensum) {
        i = 0; 
        j = 0;
        while (j < asignaturas.length){
            i = 0;
            while(i < (CB.length - 1)){
                cod_uc = CB[i].value.split(" ");  
                if ((cod_uc[0] == asigs[j]) && (cod_uc[0] != asigSC )){
                    CB[i].selectedIndex = parseInt(asigs[j+3],10); 
                }
                i++;
            }
            j = j + 4;
        } 
    }
}

function prepdata(fp,fd) {
    
    fd.cedula.value = ced;
    fd.exp_e.value = exp_e;
    fd.contra.value = contra;
    fd.carrera.value = carrera;
    with (fd) {
        if(asigSC.value != "") {
            marcarAsignaturas(asignaturas.value, asigSC.value);            
            scMsg = "Lo siento, ya no hay cupo en \n";
            scMsg = scMsg + "la sección: " + seccSC.value + "\nde la asignatura: " + asigSC.value;
            scMsg = scMsg + "\n Por favor, modifique su selección";
            asigSC.value ="";
            alert(scMsg);
       }
        else asignaturas.value = "";
    }
    
    var cod_uc = new Array();
    scod_uc ="";
    with(fp) {
        i = 0;
        while(i < (CB.length - 1)){
          cod_uc = CB[i].value.split(" ");  
          if (cod_uc[5] !='0'){
              //alert(CB[i].value +" seleccionado");
              scod_uc = cod_uc[0] + " " + cod_uc[5] + " " + cod_uc[6] + " " + cod_uc[8];
              //alert(scod_uc);
             fd.asignaturas.value = fd.asignaturas.value + scod_uc  + " "; 
          }
          i++;
        }
    }
    //registra sexo y fecha de nac:
	if (fd.c_inicial.value != "0"){
		laFechaS =	1900 + parseInt(document.getElementById('anioN').value,10); 
		laFechaS += '-';
		laFechaS +=	document.getElementById('mesN').selectedIndex + 1;
		laFechaS += '-';
		laFechaS +=	document.getElementById('diaN').selectedIndex + 1; 
		document.f_c.f_nac_e.value = laFechaS;
		elSexo  = parseInt(document.getElementById('sexoN').value,10);
		aSexo   = Array('1','2','1');
		document.f_c.sexo.value = aSexo[elSexo];
	}
    if(fd.inscribe.value == fd.inscrito.value) {
        fd.submit();
        return true;
    }
    return true;
}

function actualizarTotales(fp,ft,$update) {
      
    ct_mat = 0;
    ct_uc = 0;
    k =fp.CB.length - 1;
    with(fp) {
       j = 0;
       while(j < k){
          //alert(k +'| '+CB[j].value+'['+CB[j].selectedIndex+']');
          if (CB[j].selectedIndex != '0'){ 
              cod_uc = CB[j].value.split(" ");               
              uc   = parseInt(cod_uc[1],10);
              ct_mat++;
              ct_uc+=uc;
          }
          j++;
       }
    }
    if ($update){
        with(ft){
            t_mat.value=ct_mat;
            t_uc.value =ct_uc;
             return true;
        }
    }
    else return ct_uc;
}
   
function correquisitoOK(fp) {

    cOK = true;
    var matAInscr = "";
    correq    = "";
    with (fp) {
        for(j=0;j < (CB.length - 1); j++){
            if (CB[j].selectedIndex != '0'){
                cod_uc = CB[j].value.split(" ");                    
                arrayMat[j] = cod_uc[0];
            }
            else arrayMat[j] = "";
        }
        matAInscr = arrayMat.join(" ");
        for(j=0;j < (CB.length - 1); j++){
           if (CB[j].selectedIndex != '0'){
               if (matAInscr.indexOf(CBC[j].value) < 0) {
                   correq = correq + "Para poder inscribir " + arrayMat[j];
                   correq = correq + " debes inscribir " + CBC[j].value +"\n"; 
                   cOK = false;
               } 
           }

       }
    }
    if (!cOK){
        alert("Conflicto de correquisito:\n" + correq);
    }
    return(cOK);
}

function actualizarSecciones() {

    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){             
            arraySecc[j] = CB[j].selectedIndex;
        }
    }
}

function estadoAnterior(lSeccion){

    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){
            cod_ucSel = lSeccion.value.split(" "); 
            cod_uc    = CB[j].value.split(" ");            
            if (cod_ucSel[0] == cod_uc[0]){
                        
                lSeccion.options[arraySecc[j]].selected = true;
            }
        }

    }
}

function calcularMaxCarga() {
    
    iMateria = -1; //indica que ninguna materia genera exceso de creditos
    limite   = 21;
    veces    = '';
    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){
            cod_uc  = CB[j].value.split(" ");
            uc      = parseInt(cod_uc[1],10);
            repite  = cod_uc[2];
            cre_cur = parseInt(cod_uc[3],10);
            t_lapso = cod_uc[4];
            if ((t_lapso !='I') && (CB[j].selectedIndex !='0')) {
                switch(repite) {
                    case '':
                            break;
                    case '0' :
                    case 'R' : //repite por primera vez
                            if (veces == '') {
                                limite = cre_cur;
                                iMateria = j;
                            }
                            else if((veces == '0')||(veces == 'R')){
                                if (limite < cre_cur) {
                                    limite = cre_cur;
                                    iMateria = j;
                                }
                            }
                            veces = repite;
                            break;
                    case '1' : //repite por 2da vez
                            if ((veces =='') || (veces =='0')) {
                                (cre_cur > 10) ? limite = 10 : limite = cre_cur;
                                iMateria = j;
                                veces = '1';
                            }
                            else if (veces == '1') {
                                if (limite < cre_cur ) {
                                    limite = cre_cur;
                                    iMateria = j;
                                }
                                if (limite > 10) {
                                    limite = 10;
                                }  

                            }
                            break;
                    case '2' : //repite por tercera vez : debe verla solita
                            if (veces != '2') {
                                limite = uc;
                                veces = '2';
                                iMateria = j
                            }
                } //switch (repite)
            }
   
        }
    }
    return(Array(iMateria,limite,veces));
}

function excesoDeCreditos(lSeccion) {
    
    exceso  = false;
    cod_uc  = lSeccion.value.split(" ");               
    ucm     = parseInt(cod_uc[1],10);
    repite  = cod_uc[2];
    cre_cur = parseInt(cod_uc[3],10);
    t_lapso = cod_uc[4];
    total_uc= parseInt(document.totales.t_uc.value);
    indice = parseFloat(document.f_c.ind_acad.value);

    maxCarga = new Array(3) //contiene maximo de creditos, condicion que aplica 
                            //y puntero a la materia que limita.
    if(indice >= 6.0) {
        CreditosAdic = 2;
    }
    else {
        CreditosAdic = 0;
    }
    //alert("seccion=" + lSeccion.selectedIndex );
    if (actualizarTotales(document.pensum,document.f_c, false) == total_uc) {
        ucm = 0
    }
    if (lSeccion.selectedIndex == '0') {
        ucm = -ucm;
    }
    maxCarga = calcularMaxCarga(); //Array(Imateria, limite, veces)
    iMateria = maxCarga[0];
    limite   = maxCarga[1];
    veces    = maxCarga[2];
    crAinsc  = total_uc + ucm;
    (veces =='2') ? maxCreditos = limite : maxCreditos = limite + CreditosAdic;
    
    if (iMateria >= 0) {
        matLim = document.pensum.CB[iMateria].value.split(" ");
        }
    else {
         matLim = "";
    }
    if (crAinsc > maxCreditos){
        exceso = true;
        mens1 = "    PROBLEMA DE EXCESO DE CRÉDITOS:\nNo puedes ";
        (ucm > 0) ? mAQ = "agregar" : mAQ = "borrar";
        mens1  = mens1 + mAQ + " esta asignatura.\n"
        mensLC = maxCreditos + " créditos\n";
        mensCS = " y estas intentando inscribir " + crAinsc + " créditos.\n";       
        mcausa = "Tu límite es ";
        if (veces != '') {
            mcausa = "La condición de repitencia de la asignatura \n";
            mcausa = mcausa + matLim[0] + " te limita a ";
        }
    }
    if (exceso) {
        alert(mens1 + mcausa + mensLC + mensCS);
    }
    return exceso;
}

function cambiarColor(lSeccion) {
    cod_uc = lSeccion.value.split(" ");
    for(i=0;i<7;i++){
        identCol = cod_uc[0]+i; //identificador de division
            //alert(identCol+' ' +cod_uc[7]);
		text_color = '#000000';
        switch (cod_uc[7]) { // de acuerdo a la seleccion y estatus, se establece el color:
            case 'G' :  lcolor='#F7F7F7'; //gris : NO SELECCIONADO
                        break;
            case 'B' :  lcolor='#99CCFF'; //azul : INSCRITO
                        break;
            case 'X' :  lcolor='#FF6666'; //rojo : RETIRO
						text_color ='#FFFFFF';
                        break;
        }
        document.getElementById(identCol).style.background = lcolor;
        document.getElementById(identCol).style.color = text_color;
    }

}

function resaltar(lSeccion) {
    
     if (correquisitoOK(document.pensum)) {
         if (!excesoDeCreditos(lSeccion)){
             actualizarTotales(document.pensum,document.totales, true);
             cambiarColor(lSeccion);
        }
        else {
            estadoAnterior(lSeccion);
        }
        
     }   
     else {
         estadoAnterior(lSeccion);
     }
     actualizarSecciones();     
}

function reiniciarTodo() {
    
    with (document) {
        ind_acad = f_c.ind_acad.value;
        pensum.reset();
        totales.reset();
        actualizarTotales(pensum,totales, true); 
        actualizarSecciones(); 
        prepdata(pensum,f_c);
        for(j=0;j < (pensum.CB.length - 1); j++) {
            cambiarColor(pensum.CB[j]);
        }
    }
	//Actualizamos sexo y fecha de nacimiento:
	//por cortesia, femenino primero (cambiamos M=2, F=1
	//aunque en la base de datos es al reves OJO!
	laFechaS = document.f_c.f_nac_e.value+"---"; //por si la fecha esta en blanco
	laFecha  = new Array();
	laFecha = laFechaS.split('-'); //anio,mes,dia
//	alert('['+laFecha+']'+laFecha[2]+laFecha[1]+laFecha[0]);
	if (laFechaS != ""){
		document.getElementById('diaN').selectedIndex = laFecha[2] - 1; 
		document.getElementById('mesN').selectedIndex = laFecha[1] - 1;
		document.getElementById('anioN').value = laFecha[0].substr(2,4); 
	}
	elSexo  = parseInt('0'+document.f_c.sexo.value,10);
	aSexo   = Array('1','2','1');
	document.getElementById('sexoN').value = aSexo[elSexo];
	document.f_c.c_inicial.value = "1"; //marcamos como validada la fecha
}

function verificar(){
    var dia = parseInt (document.getElementById('diaN').selectedIndex) + 1;
    var mes = parseInt (document.getElementById('mesN').selectedIndex) + 1;
    var anyo = parseInt ('0'+document.getElementById('anioN').value,10) + 1900;
	clearTimeout(miTempo);
    if (CancelPulsado){
        return false;
    }
	if (FechaValida(dia,mes,anyo)){
		vcontra = hex_md5(document.getElementById('pV').value);
		if(vcontra == contra){
			prepdata(document.pensum,document.f_c);
			if ((document.f_c.asignaturas.value != "") || (document.f_c.inscribe.value=="2")) {    
				document.f_c.submit();
				return true;
			}
			else {
				alert('Debes seleccionar al menos una materia');
				return false;
			}
		}
		else {
			alert('Clave incorrecta.\n Por favor intente de nuevo');
			document.getElementById('pV').value="";
			document.getElementById('pV').focus();
			return false;
		}
	}
}
 
 function verificarEnter() {
 
     miTiempo=setTimeout("verificar()",200);
 }

function cancelar() {
    CancelPulsado = true;
    document.getElementById('pV').value="";
    hideMe();
}
function Inscribirme(){

    //if( parseInt(document.totales.t_uc.value)>0){
    prepdata(document.pensum,document.f_c)
            if ((document.f_c.asignaturas.value != "") || (document.f_c.inscribe.value=="2")) {
                CancelPulsado = false;        
                showMe();
    }
    else {
        alert('Debes seleccionar al menos una materia');
    }
}

function anyoBisiesto(anyo)
 {
  var fin = anyo;
  if (fin % 4 != 0)
    return false;
    else
     {
      if (fin % 100 == 0)
       {
        if (fin % 400 == 0)
         {
          return true;
         }
          else
           {
            return false;
           }
       }
        else
         {
          return true;
         }
     }
 }

function FechaValida(dia,mes,anyo)
 {
  var anyohoy = new Date();
  var Mensaje = "";
  var yearhoy = anyohoy.getYear();
  if (yearhoy < 1999)
    yearhoy = yearhoy + 1900;
  if(anyoBisiesto(anyo))
    febrero = 29;
    else
      febrero = 28;
   if ((mes == 2) && (dia > febrero))
    {
     Mensaje += "- Día de nacimiento inválido\r\n";
    }
   if (((mes == 4) || (mes == 6) || (mes == 9) || (mes == 11)) && (dia > 30))
    {
     Mensaje += "- Día de nacimiento inválido\r\n";
    }
   if ((anyo<1950) || (yearhoy - anyo < 15))
    {
     Mensaje += "- Año de nacimiento inválido\r\n" + anyo;
    } 
   if (Mensaje != "")
   {
	   alert(Mensaje);
	   return false;
   }
   else {
	   return true;
   }
 }
 function mostrar_ayuda(ayudaURL) {
		window.open(ayudaURL,"instruciones","left=0,top=0,width=700,height=250,scrollbars=0,resizable=0,status=0");
 }
