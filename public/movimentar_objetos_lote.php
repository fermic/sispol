<?php
session_start();
require_once '../config/db.php';
include '../includes/header.php';
require_once '../includes/helpers.php';

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Método de requisição inválido.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Obtém os dados do formulário
$procedimentoID = $_POST['procedimento_id'] ?? null;
$objetos = $_POST['objetos'] ?? [];
$tipoMovimentacaoID = $_POST['tipo_movimentacao'] ?? null;
$dataMovimentacao = $_POST['data_movimentacao'] ?? null;
$observacao = $_POST['observacao'] ?? '';
$novoLacre = $_POST['novo_lacre'] ?? null;
$lacreAnterior = $_POST['lacre_anterior'] ?? null;

// Validações básicas
if (!$procedimentoID || empty($objetos) || !$tipoMovimentacaoID || !$dataMovimentacao) {
    $_SESSION['error_message'] = "Todos os campos obrigatórios devem ser preenchidos.";
    header("Location: ver_procedimento.php?id=" . $procedimentoID);
    exit;
}

// Validação da data
if (!empty($dataMovimentacao)) {
    $dataMovimentacao = date('Y-m-d H:i:s', strtotime($dataMovimentacao));
} else {
    $dataMovimentacao = date('Y-m-d H:i:s');
}

// Busca o nome do tipo de movimentação
$stmtTipo = $pdo->prepare("SELECT Nome FROM TiposMovimentacaoObjeto WHERE ID = ?");
$stmtTipo->execute([$tipoMovimentacaoID]);
$tipoMovimentacao = $stmtTipo->fetch(PDO::FETCH_ASSOC);

// Log para debug
error_log("Tipo de Movimentação ID: " . $tipoMovimentacaoID);
error_log("Tipo de Movimentação Nome: " . $tipoMovimentacao['Nome']);

// Normaliza o nome do tipo (remove acentos e converte para maiúsculas)
function normalizarTexto($texto) {
    return mb_strtoupper(removerAcentos($texto));
}

$tipoMovimentacaoNormalizado = normalizarTexto($tipoMovimentacao['Nome']);
error_log("Tipo de Movimentação Normalizado: " . $tipoMovimentacaoNormalizado);

try {
    // Verifica se é Retorno da Perícia
    if (normalizarTexto($tipoMovimentacao['Nome']) === 'RETORNO DA PERICIA' && !$novoLacre) {
        throw new Exception("Para Retorno da Perícia, o novo lacre é obrigatório.");
    }

    $pdo->beginTransaction();

    // Prepara a observação com informações do lacre se for Retorno da Perícia
    $observacaoCompleta = $observacao;
    if (normalizarTexto($tipoMovimentacao['Nome']) === 'RETORNO DA PERICIA') {
        $observacaoCompleta = "TROCA DE LACRE - ";
        if ($lacreAnterior) {
            $observacaoCompleta .= "Lacre anterior: " . $lacreAnterior . ". ";
        }
        $observacaoCompleta .= "Novo lacre: " . $novoLacre;
        if ($observacao) {
            $observacaoCompleta .= ". " . $observacao;
        }
    }

    $usuarioID = $_SESSION['usuario_id'] ?? null;

    // Prepara a query de inserção
    $query = "
        INSERT INTO MovimentacoesObjeto (
            ObjetoID,
            TipoMovimentacaoID,
            Observacao,
            DataMovimentacao,
            UsuarioID
        ) VALUES (
            :objeto_id,
            :tipo_movimentacao_id,
            :observacao,
            :data_movimentacao,
            :usuario_id
        )
    ";

    $stmt = $pdo->prepare($query);

    // Insere a movimentação para cada objeto selecionado
    foreach ($objetos as $objetoID) {
        // Insere a movimentação
        $stmt->execute([
            'objeto_id' => $objetoID,
            'tipo_movimentacao_id' => $tipoMovimentacaoID,
            'observacao' => $observacaoCompleta,
            'data_movimentacao' => $dataMovimentacao,
            'usuario_id' => $usuarioID
        ]);

        // Se for Retorno da Perícia, atualiza o lacre do objeto
        if (normalizarTexto($tipoMovimentacao['Nome']) === 'RETORNO DA PERICIA') {
            $stmtAtualizaLacre = $pdo->prepare("
                UPDATE Objetos 
                SET LacreAtual = ? 
                WHERE ID = ?
            ");
            $stmtAtualizaLacre->execute([$novoLacre, $objetoID]);
        }
    }

    $pdo->commit();
    $_SESSION['success_message'] = 'Objetos movimentados com sucesso!';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = 'Erro ao movimentar os objetos: ' . $e->getMessage();
}

// Redireciona de volta para a página do procedimento
header('Location: ver_procedimento.php?id=' . $procedimentoID);
exit; 