


$(function() {
   
   
    $('#provincia').on('change', onSelectProvinciaChange);
    $('#localidad').on('change', onFiltersChange);
    $('#fuente').on('change', onFiltersChange);
});

function onSelectProvinciaChange() {
   
    
    var provincia_id = $(this).val()
    //Ajax
    var htlm_select = '<option value="">Todas</option>';
    if(provincia_id == '' ) {
        onFiltersChange(); 
        return;
    }
   
    $.get('/api/provincia/'+provincia_id+'/localidades', function(data){
       
        for (var i=0; i<data.length; i++) {
            htlm_select += '<option value="'+data[i].id+'">'+data[i].name+'</option>'
            $('#localidad').html(htlm_select);
        }
    }).done(function (){
        onFiltersChange();
    });
}

function onFiltersChange() {
    console.log("paso");
    const provincia_id = $('#provincia').val();
    const localidad_id = $('#localidad').val();
    const fuente_id = $('#fuente').val();
    

    if (provincia_id == '' && localidad_id == '' && fuente_id == '' ) {
       
        location.href = '/';
        return;
    }

   /*  var getPet = '/empleoPrivado?'; */
var getPet = '/?'

    if (provincia_id != '') {
        getPet = getPet.concat('&provincia_id=', provincia_id);

    }

    if (localidad_id != '') {
        getPet = getPet.concat('&localidad_id=', localidad_id);
        
    } 

    if (fuente_id != '') {
        getPet = getPet.concat('&fuente_id=', fuente_id);
        
    } 

    location.href = getPet;


    

}





