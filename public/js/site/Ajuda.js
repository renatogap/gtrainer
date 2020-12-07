$(document).ready(function () {

    // Navegação de ajuda
    $(".navegacao-lista li.categoria").click(function (e) {
        e.preventDefault();

        var categoriaEscolhida = $(this).attr("id-categoria");
        var navegacaoEscolhida = $(".navegacao-lista#help_" + categoriaEscolhida);
        if (navegacaoEscolhida.length == 0) {
            exibirTopoAviso("Erro! Categoria não localizada!");
            return;
        }

        $(".navegacao-lista.ativo").slideUp(500, function () {
            $(".navegacao-lista.ativo").removeClass("ativo");
            navegacaoEscolhida.slideDown(500, function () {
                navegacaoEscolhida.addClass("ativo")
            });
        });
    });

    $(".navegacao-lista li.voltar").click(function (e) {

        var navegacaoEscolhida = null;
        var categoriaPai = $(this).attr("id-ajuda-categoria-pai");
        if (categoriaPai == undefined || categoriaPai == null || categoriaPai == "") {
            navegacaoEscolhida = $(".navegacao-lista#help");
        }
        else {
            navegacaoEscolhida = $(".navegacao-lista#help_" + categoriaPai);
        }

        if (navegacaoEscolhida.length == 0) {
            exibirTopoAviso("Erro! Categoria não localizada!");
            return;
        }

        $(".navegacao-lista.ativo").slideUp(500, function () {
            $(".navegacao-lista.ativo").removeClass("ativo");
            navegacaoEscolhida.slideDown(500, function () {
                navegacaoEscolhida.addClass("ativo")
            });
        });
    });

    // Votação
    $(document).delegate(".post .votacao button", "click", function (e) {
        e.preventDefault();
        var self = $(this);
        var contentVotacao = self.parent();
        var idAjudaPost = self.attr("id-post");
        var util = self.hasClass("sim");

        $.ajax({
            url: "/ajax-qualificar-ajuda-post",
            type: "POST",
            data: {
                idAjudaPost: idAjudaPost,
                util: util
            },
            beforeSend: function () {
                contentVotacao.html("<span class='notificacao carregando'>Aguarde...</span>");
            },
            success: function (data) {
                data = $.parseJSON(data);
                if (data.Erro || data.MsgErro != "") {
                    contentVotacao.html("Este artigo foi útil para você? <button type='button' class='sim' id-post='" + idAjudaPost + "'>Sim</button> ou <button type='button' class='nao' id-post='" + idAjudaPost + "'>Não</button>");
                    exibirTopoAviso(data);
                }
                else {
                    contentVotacao.html("<span class='notificacao sucesso'>Obrigado pela sua avaliação!</span>");
                }
            }
        });
    });

    // Pesquisa
    $(document).delegate("#busca #texto-busca", "keypress", function (e) {
        if (e.which == 13) {
            e.preventDefault();
            $("#busca button").click();
        }
    });

    $(document).delegate("#busca button", "click", function (e) {
        e.preventDefault();
        var self = $(this);
        var pesquisa = $("#busca #texto-busca").val();
        if (pesquisa == "" || self.attr("disabled") == "disabled") {
            return false;
        }

        $.ajax({
            url: "/ajax-pesquisar-ajuda-post",
            type: "POST",
            data: { pesquisa: pesquisa },
            beforeSend: function () {
                self.find("span").addClass("aguarde");
                self.attr("disabled", "disabled");
            },
            success: function (data) {
                data = $.parseJSON(data);
                if (data.Erro || data.MsgErro != "") {
                    exibirTopoAviso(data.MsgErro);
                }
                else {
                    var areaAjuda = $(".area-ajuda");
                    var html = "";

                    html += "<div class='resultado-busca'>";
                    html += "<p class='titulo'>Resultado da busca</p>";
                    html += "<p class='voce-pesquisou'>Você pesquisou por \"<b>" + pesquisa + "</b>\"</p>";
                    if (data.ListaAjudaPost.length == 0) {
                        html += "<p class='quantidade-ocorrencia'><b>Nenhuma ocorrência foi encontrada.</b></p>";
                    }
                    else {
                        if (data.ListaAjudaPost.length == 1) {
                            html += "<p class='quantidade-ocorrencia'><b>Foi encontrada apenas uma ocorrência.</b></p>";
                        }
                        else {
                            html += "<p class='quantidade-ocorrencia'><b>Foram encontradas " + data.ListaAjudaPost.length + " ocorrências.</b></p>";
                        }

                        html += "<ul class='lista-resultado'>"
                        for (var i = 0; i < data.ListaAjudaPost.length; i++) {
                            var ajudaPost = data.ListaAjudaPost[i];

                            html += "<li>";
                            html += "<p class='titulo-post'>" + ajudaPost.Titulo + "</p>";
                            html += "<p class='resumo-post'>" + ajudaPost.Texto + "</p>";
                            html += "<a href='/ajuda/" + ajudaPost.LinkAmigavel + "'></a>";
                            html += "</li>";
                        }
                        html += "</ul>";
                    }

                    html += "</div>";

                    // Limpa o conteúdo
                    if (areaAjuda.find(".resultado-busca").length == 1) {
                        areaAjuda.html(html);
                        $(".link-rapido").show();
                    }
                    else {
                        $(".area-ajuda .post, .area-ajuda .populares").fadeOut(500, function () {
                            $(".area-ajuda .post, .area-ajuda .populares").html("");
                            areaAjuda.html(html);
                            areaAjuda.fadeIn(500);
                            $(".link-rapido").fadeIn(500);
                        });
                    }
                }
            },
            complete: function () {
                self.removeAttr("disabled");
                self.find("span").removeClass("aguarde");
            }
        });
    });
});