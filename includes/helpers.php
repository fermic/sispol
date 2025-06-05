<?php
/**
 * Retorna a classe CSS do badge para o tipo de movimentação
 * @param string|null $tipoMovimentacao Nome do tipo de movimentação
 * @param string|null $corCorreta Cor correta do banco de dados
 * @return string Classe CSS do badge
 */
function getBadgeClassMovimentacao($tipoMovimentacao = null, $corCorreta = null) {
    // Classe base para todos os badges - tamanho intermediário
    $classeBase = 'badge fw-semibold fs-6 px-2 py-1';
    
    // Se tiver uma cor definida no banco, usa ela
    if ($corCorreta) {
        return $classeBase . ' ' . $corCorreta;
    }

    // Fallback para cores padrão baseadas no nome
    if (!$tipoMovimentacao) {
        return $classeBase . ' bg-secondary';
    }

    $tipoNormalizado = mb_strtoupper(removerAcentos($tipoMovimentacao));
    
    // Mapeamento de cores padrão
    $coresPadrao = [
        'DESTRUICAO' => 'bg-danger',
        'DEVOLUCAO' => 'bg-warning',
        'PERICIA' => 'bg-info',
        'DEPOSITO JUDICIAL' => 'bg-primary',
        'ENTRADA' => 'bg-success',
        'SAIDA' => 'bg-warning',
        'TRANSFERENCIA' => 'bg-info'
    ];

    // Procura por correspondências parciais
    foreach ($coresPadrao as $padrao => $cor) {
        if (strpos($tipoNormalizado, $padrao) !== false) {
            return $classeBase . ' ' . $cor;
        }
    }

    return $classeBase . ' bg-secondary';
}

/**
 * Remove acentos de uma string
 * @param string $texto Texto com acentos
 * @return string Texto sem acentos
 */
function removerAcentos($texto) {
    $texto = str_replace(
        ['á', 'à', 'â', 'ã', 'ä', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ö', 'ú', 'ü', 'ç', 'Á', 'À', 'Â', 'Ã', 'Ä', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ö', 'Ú', 'Ü', 'Ç'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'O', 'U', 'U', 'C'],
        $texto
    );
    return $texto;
}
?>