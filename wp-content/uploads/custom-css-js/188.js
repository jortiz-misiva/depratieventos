<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
function upGoogle(){
	const enlace = document.querySelector('.btn-cal-google>a.mec-events-gcal');
	if (enlace) {  
	  const href = enlace.getAttribute('href');   
		window.open(href,'_blank');
		console.log('El href del enlace es:', href);    
	  } else {
			console.log('El enlace no fue encontrado');
	  }	
}
	
function upiOS(){
	const enlace = document.querySelector('.btn-cal-ios>a.mec-events-gcal');
	if (enlace) {  
	  const href = enlace.getAttribute('href');   
		window.open(href,'_blank');
		console.log('El href del enlace es:', href);    
	  } else {
			console.log('El enlace no fue encontrado');
	  }	
}



function upGoogleGrid(id){
	const enlace = document.querySelector('.btn-cal-google-'+id+'>a.mec-events-gcal');
	if (enlace) {  
	  const href = enlace.getAttribute('href');   
		window.open(href,'_blank');
		console.log('El href del enlace es:', href);    
	  } else {
			console.log('El enlace no fue encontrado');
	  }	
}
	
function upiOSGrid(id){
	const enlace = document.querySelector('.btn-cal-ios-'+id+'>a.mec-events-gcal');
	if (enlace) {  
	  const href = enlace.getAttribute('href');   
		window.open(href,'_blank');
		console.log('El href del enlace es:', href);    
	  } else {
			console.log('El enlace no fue encontrado');
	  }	
}



document.addEventListener('DOMContentLoaded', function() {
  const botonAgendar = document.querySelector('.btn-agendar');
  botonAgendar.addEventListener('click', function() {    
    const sistemaOperativo = navigator.platform.toLowerCase();   
    switch (true) {
      case sistemaOperativo.includes('win'):
		upGoogle();
        //console.log('El sistema operativo del usuario es Windows');        
        break;
      case sistemaOperativo.includes('mac'):
		upiOS();
       // console.log('El sistema operativo del usuario es macOS');        
        break;
      case sistemaOperativo.includes('iphone') || sistemaOperativo.includes('ipad'):
		upiOS();
     //   console.log('El sistema operativo del usuario es iOS');        
        break;
      default:
		upGoogle();
        console.log('Sistema operativo no identificado');        
    }    
  });	
});

document.addEventListener('DOMContentLoaded', function() {	
 const botonesAgendarEvento = document.querySelectorAll('.btn-agenda-evento');  
  botonesAgendarEvento.forEach(function(boton) {
    boton.addEventListener('click', function() {
		const dataRef = this.getAttribute('data-ref');
		var sistemaOperativo = navigator.platform.toLowerCase();
		switch (true) {
		  case sistemaOperativo.includes('win'):
			upGoogleGrid(dataRef);        
			break;
		  case sistemaOperativo.includes('mac'):
			upiOSGrid(dataRef);     
			break;
		  case sistemaOperativo.includes('iphone') || sistemaOperativo.includes('ipad'):
			upiOSGrid(dataRef);     
			break;
		  default:
			upGoogleGrid(dataRef);        
		}  
	});
  });
});



</script>
<!-- end Simple Custom CSS and JS -->
