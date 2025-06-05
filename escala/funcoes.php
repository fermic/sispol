<?php
require_once 'db.php';

// Obter os policiais de plantão para hoje
function obterPoliciaisDePlantao($data) {
    global $conn;
    $query = "
        SELECT e.id AS escala_id, p1.nome AS policial1, p2.nome AS policial2, 
               s.policial_substituto_id, s.dia_substituicao, ps.nome AS substituto
        FROM escala e
        LEFT JOIN policiais p1 ON e.policial1_id = p1.id
        LEFT JOIN policiais p2 ON e.policial2_id = p2.id
        LEFT JOIN substituicoes s ON e.id = s.escala_id AND s.dia_substituicao = :data
        LEFT JOIN policiais ps ON s.policial_substituto_id = ps.id
        WHERE :data BETWEEN e.data_inicio AND e.data_fim;
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute(['data' => $data]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obterEscalas() {
    global $conn;
    $query = "
        SELECT e.id, e.data_inicio, e.data_fim, p1.nome AS policial1, p2.nome AS policial2
        FROM escala e
        LEFT JOIN policiais p1 ON e.policial1_id = p1.id
        LEFT JOIN policiais p2 ON e.policial2_id = p2.id
        ORDER BY e.data_inicio DESC;
    ";
    $stmt = $conn->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function obterEscalasComSubstituicoes() {
    global $conn;
    $query = "
        SELECT e.id, e.data_inicio, e.data_fim,
               p1.nome AS policial1_original, p2.nome AS policial2_original,
               s1.policial_substituto_id AS policial1_substituto_id, s2.policial_substituto_id AS policial2_substituto_id,
               ps1.nome AS policial1_substituto, ps2.nome AS policial2_substituto,
               s1.dia_substituicao AS dia_substituicao1, s2.dia_substituicao AS dia_substituicao2
        FROM escala e
        LEFT JOIN policiais p1 ON e.policial1_id = p1.id
        LEFT JOIN policiais p2 ON e.policial2_id = p2.id
        LEFT JOIN substituicoes s1 ON e.id = s1.escala_id AND s1.policial_substituido_id = e.policial1_id
        LEFT JOIN substituicoes s2 ON e.id = s2.escala_id AND s2.policial_substituido_id = e.policial2_id
        LEFT JOIN policiais ps1 ON s1.policial_substituto_id = ps1.id
        LEFT JOIN policiais ps2 ON s2.policial_substituto_id = ps2.id
        ORDER BY e.data_inicio DESC;
    ";
    $stmt = $conn->query($query);
    $escalas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];
    foreach ($escalas as $escala) {
        $escalaId = $escala['id'];

        // Inicia a estrutura básica
        if (!isset($resultado[$escalaId])) {
            $resultado[$escalaId] = [
                'id' => $escala['id'],
                'data_inicio' => $escala['data_inicio'],
                'data_fim' => $escala['data_fim'],
                'policial1_original' => $escala['policial1_original'],
                'policial2_original' => $escala['policial2_original'],
                'policial1_substituicoes' => [],
                'policial2_substituicoes' => [],
                'policial1_substituido_todos' => false,
                'policial2_substituido_todos' => false,
                'policial1_substituto' => null, // Inicializar chave
                'policial2_substituto' => null, // Inicializar chave
            ];
        }

        // Coletar substituições
        if (!empty($escala['dia_substituicao1'])) {
            $resultado[$escalaId]['policial1_substituicoes'][] = [
                'dia' => $escala['dia_substituicao1'],
                'substituto' => $escala['policial1_substituto'],
            ];
        }

        if (!empty($escala['dia_substituicao2'])) {
            $resultado[$escalaId]['policial2_substituicoes'][] = [
                'dia' => $escala['dia_substituicao2'],
                'substituto' => $escala['policial2_substituto'],
            ];
        }

        // Verificar substituições completas para Policial 1
        $diasIntervalo1 = iterator_to_array(new DatePeriod(
            new DateTime($resultado[$escalaId]['data_inicio']),
            new DateInterval('P1D'),
            (new DateTime($resultado[$escalaId]['data_fim']))->modify('+1 day')
        ));

        $diasIntervalo1 = array_map(fn($date) => $date->format('Y-m-d'), $diasIntervalo1); 

        $diasSubstituidos1 = array_map(function ($substituicao) {
            return $substituicao['dia'];
        }, $resultado[$escalaId]['policial1_substituicoes']);

        if (empty(array_diff($diasIntervalo1, $diasSubstituidos1))) {
            $resultado[$escalaId]['policial1_substituido_todos'] = true;
            $resultado[$escalaId]['policial1_substituto'] = $escala['policial1_substituto'];
        }

        // Verificar substituições completas para Policial 2
        $diasIntervalo2 = iterator_to_array(new DatePeriod(
            new DateTime($resultado[$escalaId]['data_inicio']),
            new DateInterval('P1D'),
            (new DateTime($resultado[$escalaId]['data_fim']))->modify('+1 day')
        ));

        $diasIntervalo2 = array_map(fn($date) => $date->format('Y-m-d'), $diasIntervalo2); // Converter para strings

        $diasSubstituidos2 = array_map(function ($substituicao) {
            return $substituicao['dia'];
        }, $resultado[$escalaId]['policial2_substituicoes']);

        if (empty(array_diff($diasIntervalo2, $diasSubstituidos2))) {
            $resultado[$escalaId]['policial2_substituido_todos'] = true;
            $resultado[$escalaId]['policial2_substituto'] = $escala['policial2_substituto'];
        }
    }

    return $resultado;
}

/**
 * Obtém a lista de todos os policiais registrados no banco de dados.
 *
 * @return array Retorna um array associativo contendo os dados dos policiais.
 */
function obterTodosPoliciais()
{
    global $conn; // Certifique-se de que a conexão com o banco está configurada globalmente.

    try {
        $query = "SELECT id, nome FROM policiais ORDER BY nome ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Exibe erro, mas isso deve ser melhor tratado em um ambiente de produção
        die("Erro ao obter a lista de policiais: " . $e->getMessage());
    }
}



?>