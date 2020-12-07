/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(function() {
                				
    $('#modal').dialog({
        autoOpen: false,
        position: 'top',
        modal: true
    });
    
//    $('#modalTreino').dialog({
//        autoOpen: false,
//	  position: 'top',
//        modal: true
//    });  

    //carregarGridTreinos();
    //carregarGridPeriodizacao();

    $('#fotoAluno').mouseover(function(){ 
        $('#editarFoto').show();
    }).mouseout(function(){
        $('#editarFoto').hide();
    });

});

function carregarGridTreinos(){
    $( "#gridTreinos" ).load( BASE_URL+"admin/gridTreinos.php" );
}

//function carregarGridPeriodizacao(){
//    $( "#gridPeriodizacoes" ).load( BASE_URL+"admin/gridPeriodizacao.php" );
//}


//function modalExerciciosAdd(ficha_id, aluno_id) {
//    $( "#modalTreino" ).dialog( "option", "title", "Adicionar Exercicios" );
//    $( "#modalTreino" ).dialog( "option", "width", 650 );
//    $( "#modalTreino" ).dialog( "option", "height", 250 );
//    $( "#modalTreino" ).dialog( "option", "buttons", [{
//                                                text: "Salvar", 
//                                                "class" : "btn btn-primary btn-xs",
//                                                click: function() { 
//                                                    var params = $('#frmExercicioFicha').serialize();
//
//                                                    $.ajax({
//                                                        type:'post',
//                                                        url: BASE_URL+'admin/salvar-exercicio-ficha',
//                                                        data: params,
//                                                        dataType: 'json',
//                                                        success: function(resp) {
//                                                            if(resp.retorno == 'sucesso') {
//                                                                pesquisarFichaAluno(aluno_id, ficha_id);
//                                                                inicializar();
//                                                                alert(resp.msg);
//                                                            }else {
//                                                                alert(resp.msg);
//                                                                return false;
//                                                            }
//                                                        },
//                                                        error: function (xhr, ajaxOptions, thrownError) {
//                                                            alert(xhr.status);
//                                                            alert(thrownError);
//                                                        }
//                                                    })
//
//                                                } 
//                                                },{
//                                                    text: "Fechar", 
//                                                    "class" : "btn btn-danger btn-xs",
//                                                    click: function() { 
//                                                        $( this ).dialog( "close" ); 
//                                                    } 
//                                                }]);
//    $('.ui-dialog-titlebar').hide();                                            
//
//    $( "#modalTreino" ).load(BASE_URL+"admin/modal-montar-treino-aluno", {treino_id: ficha_id, aluno_id: aluno_id}).dialog( "open" );
//}

//function modalPeriodizacao() {
//    $( "#modal" ).dialog( "option", "title", "Adicionar Periodização" );
//    $( "#modal" ).dialog( "option", "width", 600 );
//    $( "#modal" ).dialog( "option", "height", 450 );
//    $( "#modal" ).dialog( "option", "buttons", [{
//                                                    text: "Fechar", 
//                                                    click: function() { 
//                                                        $( this ).dialog( "close" ); 
//                                                    } 
//                                                }]);
//
//    $( "#modal" ).load(BASE_URL+"admin/frm-periodizacao", {aluno_id: $('#P_matricula').val()}).dialog( "open" );
//}

function addCarga(treino_id, ficha_id) {
    $( "#modal" ).dialog( "option", "title", "Adicionar Carga" );
    $( "#modal" ).dialog( "option", "width", 400 );
    $( "#modal" ).dialog( "option", "height", 400 );
    $( "#modal" ).dialog( "option", "buttons", [{
                                                    text: "Fechar", 
                                                    "class" : "btn btn-danger btn-xs",
                                                    click: function() { 
                                                        $( this ).dialog( "close" ); 
                                                    } 
                                                }]);

    $('.ui-dialog-titlebar').hide();                                                  
    $( "#modal" ).load(BASE_URL+"admin/modal-adiciona-carga-treino", 
        {
            ficha_id: ficha_id,
            treino_id: treino_id, 
            aluno_id: $('#P_matricula').val()
        }
    ).dialog( "open" );
}

function removerTreino(treino_id) {
    $('#modalRemove').dialog({
        autoOpen: true,
        position: 'top',
        modal: true,
        width: 300,
        height: 230,
        buttons: [
            {
                html: "Sim",
                "class" : "btn btn-danger btn-xs",
                click: function() {
                    var sucesso = false;
                    
                    var aluno_id = $('#P_matricula').val();
                    var ficha_id = $('#ficha_id').val();

                    $.ajax({
                        type:'post',
                        url: BASE_URL+'admin/remover-treino',
                        data: {treino_id: treino_id},
                        dataType:'json',
                        async: false,
                        success: function(resp){
                            if(resp.retorno != 'sucesso'){
                                alert(resp.msg); return false;
                            }else {
                                sucesso = true;
                                pesquisarFichaAluno(aluno_id, ficha_id);
                                alert(resp.msg);
                            }
                        }
                    })
                    
                    if(sucesso) $( this ).dialog( "close" );
                    
                }
            },
            {
                text: "Não",
                "class" : "btn btn-primary btn-xs",
                click: function() {
                    $( this ).dialog( "close" );
                } 
            }
            
        ]
    })
    $('.ui-dialog-titlebar').hide();
    
}

function imprimirTreino(ficha_id, aluno_id) {
    
    window.open(BASE_URL+"admin/imprimir-treino?ficha_id="+ficha_id+"&matricula="+aluno_id,"Impressao","location=no,directories=no,scrollbars=yes,menubar=no,statusbar=no,resize=yes,width=600,height=700");
    
}

function pesquisarFichaAluno(matricula, ficha_id){

    var matricula = (matricula)? matricula : $('#P_matricula').val();

    if(matricula) {
        
        $.ajax({
            type:"post",
            url: BASE_URL+'admin/edit-treino-aluno',
            data: {
                ficha_id: ficha_id, 
                matricula: matricula
            },
            dataType: 'json',
            async: false,
            success: function(resp){
                if(resp.retorno != 'sucesso'){
                    alert(resp.msg);
                    return false;
                }else{
                    $('#contentGeral').html(resp.html).show();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                //alert(xhr.status);
                alert(thrownError);
            }
        })
    }

}

function listarTreinosAluno(matricula){

    var matricula = (matricula)? matricula : $('#P_matricula').val();
    //var matricula = $('#P_matricula').val();

    if(matricula) {
        
        $.ajax({
            type:"post",
            url: BASE_URL+'admin/listar-treinos-aluno',
            data: {matricula: matricula},
            async: false,
            success: function(respHTML){
                $('#contentGeral').html(respHTML).show();
            }
        })
    }

}