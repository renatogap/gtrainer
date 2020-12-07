$(document).ready(function () {

    // Planos
    $("#detalhes-plano .cabecalho").click(function (e) {
        if ($(this).parent().find(".detalhes").hasClass("ativo")) {
            $(this).find("p").html("Clique aqui para ver <span class='destaque'>todos os detalhes</span> de cada plano");
            $(this).find("p").css("margin-left", "140px");
            $(this).find("div").attr("class", "seta-baixo");
            $(this).parent().find(".detalhes").stop().slideUp({ duration: 1500 });
            $(this).parent().find(".detalhes").removeClass("ativo");
        }
        else {
            $(this).find("p").html("Clique aqui para ocultar <span class='destaque'>todos os detalhes</span> de cada plano");
            $(this).find("p").css("margin-left", "110px");
            $(this).find("div").attr("class", "seta-cima");
            $(this).parent().find(".detalhes").stop().slideDown({ duration: 1500 });
            $(this).parent().find(".detalhes").addClass("ativo");
        }
    });

    // Botão para impressão
    $("button#imprimir-pagina").click(function (e) {
        e.preventDefault();
        window.print();
    });

});

function exibirTopoAviso(mensagemExcecao) {
    var contentExcecao = $('#mensagem-excecao');
    if (contentExcecao.length == 0) {
        var html = "<div id='mensagem-excecao'></div>";
        if ($('#site').length > 0) { $('#site').before(html); }
        else { $('body').prepend(html); }
        contentExcecao = $('#mensagem-excecao');
    }

    contentExcecao.css({ top: -50 });
    contentExcecao.html("<p>" + mensagemExcecao + "</p>");
    contentExcecao.show();
    contentExcecao.animate({ top: 0 }, {
        duration: 500,
        complete: function () {
            setTimeout(function () {
                contentExcecao.animate({ top: -50 }, {
                    duration: 500,
                    complete: function () {
                        contentExcecao.hide();
                    }
                });
            }, 5000);
        }
    });
}

function mascaraTelefone(objeto, dddInformado) {
    var DddNoveDigitos = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28'];
    if (DddNoveDigitos.indexOf(dddInformado) == -1 || (objeto.val() != "" && objeto.val().charAt(0) != 9)) {
        objeto.setMask({ mask: '9999-9999', autoTab: false });
    }
    else {
        objeto.setMask({ mask: '999-999-999', autoTab: false });
    }
};

function tratarDuploClique(objeto) {
    var retorno = objeto.hasClass("btn-submiting");
    objeto.addClass("btn-submiting");
    return retorno;
}