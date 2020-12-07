var timerAlternarOpiniao = null;

$(document).ready(function () {
    estilizarOpiniaoUsuarios();

    $(document).delegate("#cadastrar input[title]", "focus", function (e) {
        $(this).qtip('destroy');
        $(this).qtip({
            viewport: $(window),
            position: {
                my: 'left center',
                adjust: { x: -8, y: -22 }
            },
            show: {
                event: e.type,
                ready: true
            },
            style: { classes: 'qtip-dark qtip-rounded' }
        }, e);
    });

    $(document).delegate("#cadastrar input[title]", "blur", function (e) {
        $(this).qtip('destroy');
    });
});

function estilizarOpiniaoUsuarios() {
    if ($('#opiniao-usuario').length == 0) {
        return false;
    }

    var opiniaoUsuario = $('#opiniao-usuario');
    var quantidadeOpinioes = opiniaoUsuario.find('div.lista-opiniao ul li').length;
    if (quantidadeOpinioes == 0) {
        return false;
    }

    var html = "<div class='seletor'><ul>";
    for (var i = quantidadeOpinioes; i > 0; i--) {
        html += "<li><span class='externo'></span><span class='interno'></span></li>";
    }
    html += "</ul><div class='bg-seletor'></div></div>";
    opiniaoUsuario.prepend(html);

    timerAlternarOpiniao = setInterval(function () { alternarOpinioes(); }, 10000);

    opiniaoUsuario.find('div.lista-opiniao ul li').removeClass('ativo');
    opiniaoUsuario.find('div.seletor ul li').first().click();
}

function alternarOpinioes() {
    var opiniaoUsuario = $('#opiniao-usuario');

    var seletorAtual = opiniaoUsuario.find('div.seletor ul li.ativo').index();
    opiniaoUsuario.find('div.seletor ul li').removeClass('ativo');

    var atual = opiniaoUsuario.find('div.lista-opiniao ul li.ativo');
    var futuro = null;

    if (seletorAtual == $('#opiniao-usuario div.seletor ul li').length - 1) {
        futuro = opiniaoUsuario.find('div.lista-opiniao ul li').eq(0);
        opiniaoUsuario.find('div.seletor ul li').eq(0).addClass('ativo');
    }
    else {
        opiniaoUsuario.find('div.seletor ul li').eq(seletorAtual + 1).addClass('ativo');
        futuro = opiniaoUsuario.find('div.lista-opiniao ul li').eq(seletorAtual + 1);
    }

    var tempoAnimacao = 200;
    atual.animate({ opacity: 0 }, { duration: tempoAnimacao,
        complete: function () {
            atual.removeClass('ativo');

            futuro.addClass('ativo');
            futuro.css('opacity', 0);
            futuro.animate({ opacity: 1 }, { duration: tempoAnimacao });
        }
    });
}

$(document).delegate('#opiniao-usuario div.seletor ul li', 'click', function () {
    var self = $(this);
    var opiniaoUsuario = $('#opiniao-usuario');

    // Opinião usuario alternar
    clearInterval(timerAlternarOpiniao);
    timerAlternarOpiniao = setInterval(function () { alternarOpinioes(); }, 10000);

    $('#opiniao-usuario div.seletor ul li').removeClass('ativo');
    self.addClass('ativo');

    if (opiniaoUsuario.find('div.lista-opiniao ul li.ativo').length > 0) {
        var atual = opiniaoUsuario.find('div.lista-opiniao ul li.ativo');
        var futuro = opiniaoUsuario.find('div.lista-opiniao ul li').eq(self.index());
        var tempoAnimacao = 200;

        atual.animate(
        {
            opacity: 0
        }, {
            duration: tempoAnimacao,
            complete: function () {
                atual.removeClass('ativo');

                futuro.addClass('ativo');
                futuro.css('opacity', 0);
                futuro.animate({ opacity: 1 }, { duration: tempoAnimacao });
            }
        });
    }
    else {
        opiniaoUsuario.find('div.lista-opiniao ul li').eq(self.index()).addClass('ativo');
    }
});
