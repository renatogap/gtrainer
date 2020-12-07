$(document).ready(function () {
    $("input").eq(0).focus();

    $("#login form").submit(function (e) {
        e.preventDefault();
        if ($(this).find(".btn-login").attr("disabled") == "disabled") {
            return false;
        }

        $(this).find(".btn-login").html("<img src='"+BASE_URL+"images/aguarde4.gif' />  Aguarde...");
        $(this).find(".btn-login").attr("disabled", "disabled");
        $(this).unbind("submit").submit();
    });

    $("#esqueci-minha-senha form").submit(function (e) {
        e.preventDefault();
        if ($(this).find(".btn-esqueci-minha-senha").attr("disabled") == "disabled") {
            return false;
        }
        
        $(this).find(".btn-esqueci-minha-senha").html("Aguarde...");
        $(this).find(".btn-esqueci-minha-senha").attr("disabled", "disabled");
        //$(this).unbind("submit").submit();

        $.ajax({
            type: 'post',
            url: BASE_URL+'admin/solicitar-nova-senha',
            data: { email: $('#email').val() },
            dataType: 'json',
            success: function(resp) {
                alert(resp.msg);
                if(resp.retorno === 'sucesso'){
                    window.location = BASE_URL+'/admin/login';
                }
            }
        });
    });
    

    $("#reativar-conta button").click(function (e) {
        e.preventDefault();
        var self = $(this);
        var labelBotao = self.html();

        if (self.attr("disabled") == "disabled") {
            return false;
        }

        self.html("Aguarde...");
        self.attr("disabled", "disabled");

        $.ajax({
            url: "/ajax-enviar-reativacao-conta",
            type: "POST",
            data: { email: self.attr("email") },
            success: function (data) {
                data = $.parseJSON(data);
                if (data.Erro || data.MsgErro) {
                    exibirTopoAviso(data.MsgErro);

                    self.html(labelBotao);
                    self.removeAttr("disabled");
                }
                else {
                    self.parent().removeClass("mensagem-erro");
                    self.parent().addClass("mensagem-sucesso");
                    self.parent().html("Foi enviado um e-mail para \"" + self.attr("email") + "\" com instruções de reativação de conta!");
                }
            }
        });
    });
    

    $('#btnEntrar').click(function(){
        $(document).ajaxStart(function(){
            $('#btnEntrar').html("<img src='"+BASE_URL+"images/aguarde4.gif' /> Aguarde...");
            $('#btnEntrar').attr("disabled", "disabled");
        });
        $(document).ajaxComplete(function(){
            $('#btnEntrar').html("Entrar");
            $('#btnEntrar').attr("disabled", false);
        });
        
        if( $("#login").val() === "" ){
            alert("Informe o seu email."); 
        }else 
        if( !$("#senha").val() ){
            alert("Informe o sua senha.");
        }else {
            var params = $('#form-login').serialize();

            logar(params);

            return false;
        }
    });//fim click
    
    
    $('#ckVerSenha').click(function(){
        var value = $('input[name="ckVerSenha"]:checked').val();

        if(value == 'on'){
            $('#senhaCad').hide().attr('disabled', true);
            $('#hdsenha').val( $('#senhaCad').val() ).show().attr('disabled', false);
        }else {
            $('#hdsenha').hide().attr('disabled', true);
            $('#senhaCad').show().attr('disabled', false);
        }
    });
    
    
    $('#btnCadastrar').click(function(){
        $(document).ajaxStart(function(){
            $('#btnCadastrar').html("<img src='"+BASE_URL+"images/aguarde4.gif' /> Aguarde...");
            $('#btnCadastrar').attr("disabled", "disabled");
        });
        $(document).ajaxComplete(function(){
            $('#btnCadastrar').html("Cadastrar");
            $('#btnCadastrar').attr("disabled", false);
        });

        var params = $('#form-login-cad').serialize();

        $.ajax({
            type: 'post',
            url: BASE_URL+'admin/cadastrar-novo-usuario',
            data: params,
            dataType: 'json',
            success: function(resp) {
                if(resp.retorno !== 'sucesso'){
                    alert(resp.msg);
                    return false;
                }else
                if(resp.retorno === 'sucesso'){
                    if( !resp.url ){
                        var params = {
                            login: $('#email').val(),
                            senha: $('#senhaCad').val()
                        };

                        logar(params);
                    }else {
                        window.location = resp.url;
                    }
                }else{
                    alert(resp.msg);
                    return false;
                }
            }
        });

        return false;
    });

});


function logar(params) {
    $.ajax({
        type: 'post',
        url: BASE_URL+'admin/autenticacao',
        data: params,
        dataType: 'json',
        success: function(resp) {
            if(resp.retorno === 'erro'){
                alert(resp.msg);
                if(resp.url){
                    window.location = resp.url;
                }
                return false;
            }else
            if(resp.retorno === 'info'){
                alert(resp.msg);
            }else
            if(resp.retorno === 'aluno'){
                if(resp.url){
                    window.location = resp.url;
                }else {
                    alert(resp.msg); 
                }
                return false;
            }else
            if(resp.retorno === 'sucesso'){
                window.location = BASE_URL+'admin/index';
            }else{
                alert(resp.msg);
                return false;
            }
        }
    });
}