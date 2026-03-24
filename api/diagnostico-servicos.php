<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

// 1. Buscar todos os serviços ativos
$stmt = $pdo->prepare("SELECT id, nome, valor_padrao, categoria FROM servicos_eventpro WHERE ativo = 1 AND user_id = ? ORDER BY nome");
$stmt->execute([$userId]);
$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Buscar todos os eventos com status em_andamento ou concluido
$stmt = $pdo->prepare("
    SELECT e.id, e.tipo, e.data_evento, e.status, e.valor_total, c.nome as cliente_nome
    FROM eventos_eventpro e
    LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
    WHERE e.user_id = ? AND e.status IN ('em_andamento', 'concluido')
    ORDER BY e.data_evento DESC
");
$stmt->execute([$userId]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Buscar TODOS os registros de evento_servicos desses eventos
$eventIds = array_column($eventos, 'id');
$eventoMap = [];
foreach ($eventos as $ev) {
    $eventoMap[$ev['id']] = $ev;
}

$servicoStats = [];
foreach ($servicos as $s) {
    $servicoStats[$s['id']] = [
        'nome' => $s['nome'],
        'valor_padrao_atual' => $s['valor_padrao'],
        'categoria' => $s['categoria'],
        'vezes_contratado' => 0,
        'receita_total' => 0,
        'eventos_detalhes' => []
    ];
}

if (!empty($eventIds)) {
    $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
    $stmt = $pdo->prepare("
        SELECT es.*, s.nome as servico_nome_atual
        FROM evento_servicos es
        LEFT JOIN servicos_eventpro s ON es.servico_id = s.id
        WHERE es.evento_id IN ($placeholders)
        ORDER BY es.evento_id, es.servico_id
    ");
    $stmt->execute($eventIds);
    $eventoServicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($eventoServicos as $es) {
        $sid = $es['servico_id'];
        $eid = $es['evento_id'];

        $valorTotal = floatval($es['valor_total'] ?? 0);
        if ($valorTotal == 0) {
            $valorTotal = floatval($es['valor_unitario'] ?? 0) * intval($es['quantidade'] ?? 1);
        }

        if (!isset($servicoStats[$sid])) {
            $servicoStats[$sid] = [
                'nome' => $es['servico_nome_atual'] ?? "ID: $sid (INATIVO/EXCLUÍDO)",
                'valor_padrao_atual' => '---',
                'categoria' => '---',
                'vezes_contratado' => 0,
                'receita_total' => 0,
                'eventos_detalhes' => []
            ];
        }

        $servicoStats[$sid]['vezes_contratado']++;
        $servicoStats[$sid]['receita_total'] += $valorTotal;
        $servicoStats[$sid]['eventos_detalhes'][] = [
            'evento_id' => $eid,
            'cliente' => $eventoMap[$eid]['cliente_nome'] ?? '?',
            'data' => $eventoMap[$eid]['data_evento'] ?? '?',
            'status' => $eventoMap[$eid]['status'] ?? '?',
            'tipo' => $eventoMap[$eid]['tipo'] ?? '?',
            'quantidade' => $es['quantidade'],
            'valor_unitario' => $es['valor_unitario'],
            'valor_total_snapshot' => $es['valor_total'],
            'valor_calculado' => $valorTotal
        ];
    }
}

// Também buscar eventos NÃO contabilizados (aguardando, cancelado) para comparação
$stmt = $pdo->prepare("
    SELECT e.id, e.status, e.tipo, e.data_evento, c.nome as cliente_nome
    FROM eventos_eventpro e
    LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
    WHERE e.user_id = ? AND e.status NOT IN ('em_andamento', 'concluido')
    ORDER BY e.data_evento DESC
");
$stmt->execute([$userId]);
$eventosNaoContabilizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$naoContabIds = array_column($eventosNaoContabilizados, 'id');
$servicosNaoContab = [];
if (!empty($naoContabIds)) {
    $ph2 = implode(',', array_fill(0, count($naoContabIds), '?'));
    $stmt = $pdo->prepare("
        SELECT es.evento_id, es.servico_id, es.quantidade, es.valor_unitario, es.valor_total,
               s.nome as servico_nome, e.status, e.data_evento, c.nome as cliente_nome
        FROM evento_servicos es
        LEFT JOIN servicos_eventpro s ON es.servico_id = s.id
        LEFT JOIN eventos_eventpro e ON es.evento_id = e.id
        LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
        WHERE es.evento_id IN ($ph2)
        ORDER BY es.evento_id
    ");
    $stmt->execute($naoContabIds);
    $servicosNaoContab = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Diagnóstico - Cruzamento Serviços x Eventos</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #0A0E27; color: #fff; padding: 20px; }
h1, h2, h3 { color: #00D4AA; }
table { border-collapse: collapse; width: 100%; margin: 10px 0 30px; }
th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; font-size: 13px; }
th { background: #1a1f3a; color: #00D4AA; }
tr:nth-child(even) { background: #0d1230; }
.highlight { background: #1a3a2a !important; }
.warn { color: #FFD700; }
.error { color: #FF4444; }
.ok { color: #00D4AA; }
.section { background: #111535; border-radius: 8px; padding: 20px; margin: 20px 0; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
.badge-em_andamento { background: #2563eb; }
.badge-concluido { background: #16a34a; }
.badge-aguardando { background: #d97706; }
.badge-cancelado { background: #dc2626; }
</style>
</head>
<body>
<h1>🔍 Diagnóstico: Cruzamento Serviços × Eventos</h1>
<p>Data: <?= date('d/m/Y H:i:s') ?></p>

<div class="section">
<h2>📊 Resumo por Serviço (apenas eventos em_andamento + concluido)</h2>
<table>
<tr>
    <th>ID</th>
    <th>Serviço</th>
    <th>Valor Atual (R$)</th>
    <th>Vezes Contratado</th>
    <th>Receita Total (R$)</th>
    <th>Ticket Médio (R$)</th>
</tr>
<?php
$totalGeralVezes = 0;
$totalGeralReceita = 0;
// Ordenar por vezes contratado desc
uasort($servicoStats, function($a, $b) { return $b['vezes_contratado'] - $a['vezes_contratado']; });
foreach ($servicoStats as $id => $s):
    $totalGeralVezes += $s['vezes_contratado'];
    $totalGeralReceita += $s['receita_total'];
    $ticket = $s['vezes_contratado'] > 0 ? $s['receita_total'] / $s['vezes_contratado'] : 0;
?>
<tr>
    <td><?= $id ?></td>
    <td><strong><?= htmlspecialchars($s['nome']) ?></strong></td>
    <td><?= is_numeric($s['valor_padrao_atual']) ? number_format($s['valor_padrao_atual'], 2, ',', '.') : $s['valor_padrao_atual'] ?></td>
    <td><strong><?= $s['vezes_contratado'] ?></strong></td>
    <td><strong>R$ <?= number_format($s['receita_total'], 2, ',', '.') ?></strong></td>
    <td>R$ <?= number_format($ticket, 2, ',', '.') ?></td>
</tr>
<?php endforeach; ?>
<tr style="background: #1a3a2a; font-weight: bold;">
    <td colspan="3">TOTAL</td>
    <td><?= $totalGeralVezes ?></td>
    <td>R$ <?= number_format($totalGeralReceita, 2, ',', '.') ?></td>
    <td>-</td>
</tr>
</table>
</div>

<div class="section">
<h2>📋 Detalhamento: Quais eventos contrataram cada serviço</h2>
<?php foreach ($servicoStats as $id => $s):
    if ($s['vezes_contratado'] === 0) continue;
?>
<h3><?= htmlspecialchars($s['nome']) ?> (ID: <?= $id ?>) — <?= $s['vezes_contratado'] ?> contratação(ões)</h3>
<table>
<tr>
    <th>Evento ID</th>
    <th>Cliente</th>
    <th>Tipo</th>
    <th>Data</th>
    <th>Status</th>
    <th>Qtd</th>
    <th>Valor Unit. (R$)</th>
    <th>Valor Total Snapshot (R$)</th>
    <th>Valor Calculado (R$)</th>
</tr>
<?php foreach ($s['eventos_detalhes'] as $det): ?>
<tr>
    <td>#<?= $det['evento_id'] ?></td>
    <td><?= htmlspecialchars($det['cliente']) ?></td>
    <td><?= htmlspecialchars($det['tipo']) ?></td>
    <td><?= date('d/m/Y', strtotime($det['data'])) ?></td>
    <td><span class="badge badge-<?= $det['status'] ?>"><?= $det['status'] ?></span></td>
    <td><?= $det['quantidade'] ?></td>
    <td><?= number_format(floatval($det['valor_unitario']), 2, ',', '.') ?></td>
    <td><?= $det['valor_total_snapshot'] !== null ? number_format(floatval($det['valor_total_snapshot']), 2, ',', '.') : '<span class="warn">NULL</span>' ?></td>
    <td><?= number_format($det['valor_calculado'], 2, ',', '.') ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endforeach; ?>
</div>

<?php if (!empty($servicosNaoContab)): ?>
<div class="section">
<h2>⚠️ Serviços em eventos NÃO contabilizados (aguardando/cancelado)</h2>
<p class="warn">Estes NÃO estão sendo contados na performance (correto)</p>
<table>
<tr>
    <th>Evento ID</th>
    <th>Cliente</th>
    <th>Status</th>
    <th>Data</th>
    <th>Serviço</th>
    <th>Qtd</th>
    <th>Valor Unit.</th>
    <th>Valor Total</th>
</tr>
<?php foreach ($servicosNaoContab as $snc): ?>
<tr>
    <td>#<?= $snc['evento_id'] ?></td>
    <td><?= htmlspecialchars($snc['cliente_nome'] ?? '?') ?></td>
    <td><span class="badge badge-<?= $snc['status'] ?>"><?= $snc['status'] ?></span></td>
    <td><?= $snc['data_evento'] ? date('d/m/Y', strtotime($snc['data_evento'])) : '?' ?></td>
    <td><?= htmlspecialchars($snc['servico_nome'] ?? 'ID: '.$snc['servico_id']) ?></td>
    <td><?= $snc['quantidade'] ?></td>
    <td>R$ <?= number_format(floatval($snc['valor_unitario']), 2, ',', '.') ?></td>
    <td>R$ <?= number_format(floatval($snc['valor_total']), 2, ',', '.') ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<div class="section">
<h2>📌 Todos os Eventos (<?= count($eventos) + count($eventosNaoContabilizados) ?> total)</h2>
<table>
<tr>
    <th>ID</th>
    <th>Cliente</th>
    <th>Tipo</th>
    <th>Data</th>
    <th>Status</th>
    <th>Valor Total Evento</th>
    <th>Contabilizado?</th>
</tr>
<?php
$todosEventos = array_merge(
    array_map(function($e) { $e['_contab'] = true; return $e; }, $eventos),
    array_map(function($e) { $e['_contab'] = false; return $e; }, $eventosNaoContabilizados)
);
usort($todosEventos, function($a, $b) { return strcmp($b['data_evento'], $a['data_evento']); });
foreach ($todosEventos as $ev):
?>
<tr>
    <td>#<?= $ev['id'] ?></td>
    <td><?= htmlspecialchars($ev['cliente_nome'] ?? '?') ?></td>
    <td><?= htmlspecialchars($ev['tipo'] ?? '') ?></td>
    <td><?= $ev['data_evento'] ? date('d/m/Y', strtotime($ev['data_evento'])) : '?' ?></td>
    <td><span class="badge badge-<?= $ev['status'] ?>"><?= $ev['status'] ?></span></td>
    <td>R$ <?= number_format(floatval($ev['valor_total'] ?? 0), 2, ',', '.') ?></td>
    <td><?= $ev['_contab'] ? '<span class="ok">✅ SIM</span>' : '<span class="warn">❌ NÃO</span>' ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<p style="color:#666; margin-top:40px;">Script de diagnóstico temporário — pode ser removido após verificação.</p>
</body>
</html>
