function getUsers(){
	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	var users = Array();

	$.get("php/API/v1/getUsers", function (data) {

		for (var i = 0; i < data.stuff.length; i++) {
			users[i] = data.stuff[i];
		}
	    
	}, 'json').done(function() {
	    
	  })
	  .fail(function() {
	    
	  })
	  .always(function() {
	  	$(".sidebar-nav li ul.second-level").empty();
	  	getUserReports(0, null);
	  	var n = 0, s = 0, e = 0, o = 0;
	    for (var i = 0; i < users.length; i++) {


	    	var arr = [];

			for(var x in users[i]){
			  arr.push(users[i][x]);
			}
	    	

	    	var li = '<li class="u-'+users[i].idUsuario+'" data-user="'+arr+'"><a>'+users[i].nombre+'</a></li>';


	    	$(document).on('click', '.u-'+users[i].idUsuario, function(){
	    		$(".username").text($(this).text());
	    		var classname =  $(this).attr('class');
	    		var split =classname.split('-');
	    		var id = split[1];
	    		getUserReports(id, $(this).data("user"));
	    	});
	    	

	    	if(users[i].tipo != "1"){
	    		switch(users[i].zona){
	    			case 'Norte':
	    				$(".N ul.second-level").append(li);
	    				n++;
	    				$(".N i").empty();
	    				$(".N i").append(n);
	    			break;
	    			case 'Sur':
	    				$(".S ul.second-level").append(li);
	    				s++;
	    				$(".S i").empty();
	    				$(".S i").append(s);
	    			break;
	    			case 'Este':
	    				$(".E ul.second-level").append(li);
	    				e++;
	    				$(".E i").empty();
	    				$(".E i").append(e);
	    			break;
	    			case 'Oeste':
	    				$(".O ul.second-level").append(li);
	    				o++;
	    				$(".O i").empty();
	    				$(".O i").append(o);
	    			break;
	    		}
	   		}
	   	}

	  });
}

function getUserReports(iduser, datauser){
	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	$(".table").empty();
	var ft = FooTable.init(".table" ,{
		"paging": {
			"enabled": true
		},
		"filtering": {
			"enabled": true
		},
		"columns": $.get('json/columns.json')
	});

	$.post( "php/API/v1/getReport",{id:iduser},  function(data) {


		for (var i = 0; i < data.stuff.length; i++) {
			data.stuff[i]['info'] = "<a class='rep-"+data.stuff[i].idReporte+"' href='view/' target='_blank' >Ver Reporte</a>";


			$(document).on('click', '.rep-'+data.stuff[i].idReporte, function(){
				localStorage.setItem("ID-Rep", $(this).attr('class'));
				localStorage.setItem($(this).attr('class'), datauser);
			});
		}

		ft.rows.load(data.stuff);
	});
}
	    
function getLocation(id){
	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	$.post('../php/API/v1/getLoc', {idReporte: id}, function(data) {
		setLocation(data, id);
	});

}

function setLocation(dataLoc, id){
	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	$.post( "../php/API/v1/getOneReport",{idReporte:id},  function(dataRep) {
		if(dataLoc != null && dataRep != null){
			$("#map").googleMap();
		    $("#map").addMarker({
		      	coords: [dataLoc.stuff[0].latitud, dataLoc.stuff[0].longitud], // GPS coords
		      	id: 'Promotor', // Unique ID for your marker
		      	title: dataRep.stuff[0].marca, // Title
				text:  '<b>'+dataLoc.stuff[0].direccion // HTML content
		    });
		    $(".entrada").text("Entrada: "+dataLoc.stuff[0].entrada);
		    $(".salida").text("Salida: " + dataLoc.stuff[0].salida);
		    $(".map-dir").text(dataLoc.stuff[0].direccion);
		}
	});
}

function getPresentations(id){
	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	$.post( "../php/API/v1/getPresentations",{id:id},  function(data) {
		$(".table-wrapper tbody").empty();
		for(var i = 0; i < data.stuff.length; i++){
			var pre = data.stuff[i];
			$(".table-wrapper tbody").append('<tr><td>'+pre.idPresentacion+'</td><td>'+pre.tipo+'</td><td>'+pre.inv_inicial+'</td><td>'+pre.inv_final+'</td><td>'+pre.ventas+'</td><td>'+pre.observaciones+'</td></tr>')
		}
	});
}

function getPictures(id){
	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	$.post( "../php/API/v1/getPictures",{id:id},  function(data) {
		for(var i = 0; i < data.stuff.length; i++){
			var pic = data.stuff[i];
			$(".i"+ (i + 1)+" img").attr("src" , pic.ruta );
		}
	});
}


function login(user, pass){

	$.ajaxSetup({
	   headers:{
	      'Authorization': "3d524a53c110e4c22463b10ed32cef9dAMK"
	   }
	});

	$.post( "php/API/v1/login",{username:user, password: pass},  function(data) {
		if(data.stuff.length > 0){
			$(".reports").fadeIn("slow").addClass('isActive').addClass('loggued');
			document.getElementById('id01').style.display='none';
		}else{
			$(".home").fadeIn("slow").addClass('isActive');		
			document.getElementById('id01').style.display='none';
			alert("Usuario Inexistente");
		}
	});
}