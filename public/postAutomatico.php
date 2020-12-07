<?php
header('Content-Type: text/html; charset=ISO-8859-1');

$aParams['VendedorEmail'] = 'renato.19gp@gmail.com'; //Email Gtrainer renato.19gp@gmail.com
$aParams['TransacaoID'] = 'VERIFICADO';
$aParams['Referencia'] = '2'; //academia_id
$aParams['DataTransacao'] = '10/07/2014 16:01:00'; //Formato: dd/mm/yyyy hh:mm:ss 
$aParams['TipoPagamento'] = 'Cartão de Crédito'; //Cartão de Crédito, Boleto e Pagamento Online
$aParams['StatusTransacao'] = 'Completo'; //Completo, Aguardando Pagto, Aprovado, Em Análise, Cancelado

$aParams['CliNome'] = 'Tiago Jofran'; //Nome do Cliente
$aParams['CliEmail'] = 'tiago@gmail.com'; //Email do Cliente
$aParams['CliTelefone'] = '(91)8083-2418'; //Telefone do Cliente

$aParams['ProdID_1'] = '1';
$aParams['ProdDescricao_1'] = 'Plano Semestral';
$aParams['ProdValor_1'] = '59,90';
$aParams['Parcelas'] = 1;

?>

<html>
    <head>
        
    </head>
    <body>
        <form method="post" action="retornoPagseguro.php">
            <?php foreach($aParams as $chave => $valor): ?>
                <p>
                    <label><?= $chave ?></label>
                    <input type="text" name="<?= $chave ?>" value="<?= utf8_decode($valor) ?>" />
                </p>                
            <?php endforeach; ?>
                
            <input type="submit" value="Enviar" />    
        </form>
    </body>
</html>
