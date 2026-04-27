<?php
session_start();
require_once __DIR__ . '/../controller/TravaCliente.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

function getNomeMes($mes) {
    $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
        7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
    return $meses[(int)$mes] ?? 'Indefinido';
}

function buscarFaturaPorId(PDO $pdo, int $id_fatura, int $id_usuario): ?array {
    $sql = "SELECT f.*, l.mes_referencia, l.ano_referencia, f.consumo_m3, l.id_hidrometro,
                   h.id_unidade, u.numero, u.bloco, us.nome AS usuario_nome, t.valor_m3, t.taxa_esgoto
            FROM fatura f
            JOIN leitura l ON f.id_leitura = l.id_leitura
            JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
            JOIN unidade u ON h.id_unidade = u.id_unidade
            JOIN usuario us ON u.id_usuario = us.id_usuario
            JOIN tarifa t ON f.id_tarifa = t.id_tarifa
            WHERE f.id_fatura = :id_fatura
              AND us.id_usuario = :id_usuario
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_fatura' => $id_fatura, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function buscarFaturaPorLeitura(PDO $pdo, int $id_leitura, int $id_usuario): ?array {
    $sql = "SELECT f.id_fatura
            FROM fatura f
            JOIN leitura l ON f.id_leitura = l.id_leitura
            JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
            JOIN unidade u ON h.id_unidade = u.id_unidade
            WHERE f.id_leitura = :id_leitura
              AND u.id_usuario = :id_usuario
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_leitura' => $id_leitura, ':id_usuario' => $id_usuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? buscarFaturaPorId($pdo, $row['id_fatura'], $id_usuario) : null;
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_fatura = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id_leitura = filter_input(INPUT_GET, 'leitura', FILTER_VALIDATE_INT);

// Verificação de parâmetros básica
if (!$id_fatura && !$id_leitura) {
    echo "Parâmetro ausente.";
    exit;
}

try {
    $pdo = Conexao::getConexao();
    $fatura = null;

    // Busca apenas o que já existe no banco (Calculado pelo GerarFaturaControl)
    if ($id_fatura) {
        $fatura = buscarFaturaPorId($pdo, $id_fatura, $id_usuario);
    } elseif ($id_leitura) {
        $fatura = buscarFaturaPorLeitura($pdo, $id_leitura, $id_usuario);
    }

    // Se não encontrou, avisa que a fatura não foi processada
    if (!$fatura) {
        echo "<script>alert('Esta fatura ainda não foi processada pela administração. Por favor, aguarde o fechamento do mês.'); window.location.href='dashboard.php';</script>";
        exit;
    }

    // Prepara dados para o layout faturapdf.php
    $usuario = ['nome' => $fatura['usuario_nome']];
    $unidade = ['numero' => $fatura['numero'], 'bloco' => $fatura['bloco']];
    $leitura = ['mes_referencia' => $fatura['mes_referencia'], 'ano_referencia' => $fatura['ano_referencia']];
    $tarifa = ['valor_m3' => $fatura['valor_m3'], 'taxa_esgoto' => $fatura['taxa_esgoto']];

    require_once __DIR__ . '/faturaPDF.php';

} catch (PDOException $e) {
    error_log('Erro ao carregar fatura PDF: ' . $e->getMessage());
    echo "Erro de banco de dados.";
    exit;
}