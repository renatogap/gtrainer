$(document).ready(function () {
    $("#newsletter").button();

    $("input[type=text]").first().focus();

    $("form .input-submit.botoes button").click(function (e) {
        var html = "<span class='carregando' style='display:inline-block'></span>";
        html += "Enviando mensagem...";

        $(this).html(html);
        if (tratarDuploClique($(this))) {
            e.preventDefault();
            return false;
        }
    });
});