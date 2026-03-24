<?php
/**
 * API de Geração de PDFs - EventProDJ
 * Gera orçamentos, contratos, relatórios MEI e recibos
 */

require_once '../libs/EventProPDF.php';
require_once '../config/auth.php';

$userId = requireAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        
        // ============================================
        // ORÇAMENTO PDF
        // ============================================
        
        case 'orcamento':
            $eventoId = $_GET['evento_id'] ?? $_POST['evento_id'] ?? 0;
            
            if (!$eventoId) {
                throw new Exception('ID do evento é obrigatório');
            }
            
            $pdf = new OrcamentoPDF();
            $pdf->gerar($eventoId);
            break;
        
        // ============================================
        // CONTRATO PDF
        // ============================================
        
        case 'contrato':
            $eventoId = $_GET['evento_id'] ?? $_POST['evento_id'] ?? 0;
            
            if (!$eventoId) {
                throw new Exception('ID do evento é obrigatório');
            }
            
            require_once '../config/database.php';
            // $pdo já disponível via require database.php
            
            // Buscar dados do evento
            $stmt = $pdo->prepare("
                SELECT 
                    e.*,
                    c.nome as cliente_nome,
                    c.telefone as cliente_telefone,
                    c.email as cliente_email,
                    c.cpf as cliente_cpf,
                    c.endereco as cliente_endereco
                FROM eventos_eventpro e
                LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
                WHERE e.id = :id AND e.user_id = :user_id
            ");
            $stmt->execute(['id' => $eventoId, 'user_id' => $userId]);
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evento) {
                throw new Exception('Evento não encontrado');
            }
            
            // Criar PDF
            $pdf = new EventProPDF();
            $pdf->AddPage();
            $pdf->SetY(50);
            
            // ===== TÍTULO =====
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'CONTRATO DE PRESTAÇÃO DE SERVIÇOS', 0, 1, 'C');
            $pdf->Ln(5);
            
            // ===== PARTES =====
            $pdf->adicionarSecao('CONTRATANTE');
            $pdf->adicionarCampo('Nome', $evento['cliente_nome']);
            if (!empty($evento['cliente_cpf'])) {
                $pdf->adicionarCampo('CPF', $evento['cliente_cpf']);
            }
            if (!empty($evento['cliente_telefone'])) {
                $pdf->adicionarCampo('Telefone', $evento['cliente_telefone']);
            }
            if (!empty($evento['cliente_endereco'])) {
                $pdf->adicionarCampo('Endereço', $evento['cliente_endereco']);
            }
            
            $pdf->Ln(3);
            
            // Buscar config da empresa
            $stmt = $pdo->query("SELECT chave, valor FROM configuracoes_sistema WHERE categoria = 'empresa'");
            $config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $pdf->adicionarSecao('CONTRATADO');
            $pdf->adicionarCampo('Nome', $config['empresa_nome'] ?? 'EventProDJ');
            if (!empty($config['empresa_cnpj'])) {
                $pdf->adicionarCampo('CNPJ', $config['empresa_cnpj']);
            }
            if (!empty($config['empresa_telefone'])) {
                $pdf->adicionarCampo('Telefone', $config['empresa_telefone']);
            }
            if (!empty($config['empresa_endereco'])) {
                $pdf->adicionarCampo('Endereço', $config['empresa_endereco']);
            }
            
            $pdf->Ln(5);
            
            // ===== CLÁUSULAS =====
            $clausulas = [
                [
                    'titulo' => 'CLÁUSULA PRIMEIRA - DO OBJETO',
                    'texto' => 'O presente contrato tem como objeto a prestação de serviços de sonorização e DJ para o evento de ' . 
                               $evento['tipo'] . ' do CONTRATANTE, conforme detalhamento abaixo.'
                ],
                [
                    'titulo' => 'CLÁUSULA SEGUNDA - DO VALOR E FORMA DE PAGAMENTO',
                    'texto' => 'O valor total dos serviços é de ' . EventProPDF::formatarValor($evento['valor_total']) . 
                               ($evento['sinal'] > 0 ? ', sendo ' . EventProPDF::formatarValor($evento['sinal']) . 
                               ' de sinal e o restante de ' . EventProPDF::formatarValor($evento['valor_total'] - $evento['sinal']) . 
                               ' no dia do evento.' : '.')
                ],
                [
                    'titulo' => 'CLÁUSULA TERCEIRA - DA DATA E LOCAL',
                    'texto' => 'O evento será realizado no dia ' . EventProPDF::formatarData($evento['data_evento']) .
                               (!empty($evento['hora_inicio']) ? ' às ' . $evento['hora_inicio'] : '') .
                               (!empty($evento['endereco']) || !empty($evento['cidade']) ? ', no endereço: ' . implode(', ', array_filter([
                                   !empty($evento['endereco']) ? explode(',', $evento['endereco'])[0] : '',
                                   $evento['numero'] ?? '',
                                   $evento['bairro'] ?? '',
                                   (!empty($evento['cidade']) && !empty($evento['estado'])) ? $evento['cidade'] . '/' . $evento['estado'] : ($evento['cidade'] ?? '')
                               ])) : '') . '.'
                ],
                [
                    'titulo' => 'CLÁUSULA QUARTA - DAS RESPONSABILIDADES DO CONTRATANTE',
                    'texto' => 'São responsabilidades do CONTRATANTE: a) Fornecer local adequado e energia elétrica para montagem dos equipamentos; ' .
                               'b) Garantir acesso ao local com antecedência mínima de 2 horas; ' .
                               'c) Efetuar o pagamento conforme acordado.'
                ],
                [
                    'titulo' => 'CLÁUSULA QUINTA - DAS RESPONSABILIDADES DO CONTRATADO',
                    'texto' => 'São responsabilidades do CONTRATADO: a) Comparecer no horário estabelecido; ' .
                               'b) Fornecer equipamentos em perfeito estado de funcionamento; ' .
                               'c) Executar os serviços contratados com qualidade e profissionalismo.'
                ],
                [
                    'titulo' => 'CLÁUSULA SEXTA - DO CANCELAMENTO',
                    'texto' => 'Em caso de cancelamento pelo CONTRATANTE com menos de 30 dias de antecedência, ' .
                               'o valor do sinal não será devolvido. Em caso de cancelamento pelo CONTRATADO, ' .
                               'o valor do sinal será devolvido em dobro.'
                ],
                [
                    'titulo' => 'CLÁUSULA SÉTIMA - DO FORO',
                    'texto' => 'Fica eleito o foro da comarca de ' . ($config['empresa_cidade'] ?? 'São Paulo') . 
                               ' para dirimir quaisquer dúvidas oriundas do presente contrato.'
                ]
            ];
            
            foreach ($clausulas as $clausula) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetX(15);
                $pdf->Cell(0, 5, $clausula['titulo'], 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetX(15);
                $pdf->MultiCell(0, 5, $clausula['texto'], 0, 'J');
                $pdf->Ln(3);
            }
            
            $pdf->Ln(10);
            
            // ===== DATA E LOCAL =====
            $cidade = $config['empresa_cidade'] ?? 'São Paulo';
            $pdf->SetX(15);
            $pdf->Cell(0, 5, $cidade . ', ' . date('d') . ' de ' . 
                       strftime('%B', time()) . ' de ' . date('Y'), 0, 1, 'C');
            
            $pdf->Ln(15);
            
            // ===== ASSINATURAS =====
            $pdf->Line(15, $pdf->GetY(), 100, $pdf->GetY());
            $pdf->Line(110, $pdf->GetY(), 195, $pdf->GetY());
            
            $pdf->Ln(1);
            $pdf->SetX(15);
            $pdf->Cell(85, 5, 'CONTRATANTE', 0, 0, 'C');
            $pdf->SetX(110);
            $pdf->Cell(85, 5, 'CONTRATADO', 0, 1, 'C');
            
            // Gerar PDF
            $nomeArquivo = 'Contrato_' . $eventoId . '_' . date('Y-m-d') . '.pdf';
            $pdf->Output($nomeArquivo, 'I');
            break;
        
        // ============================================
        // RELATÓRIO MEI
        // ============================================
        
        case 'relatorio_mei':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            $conta_id = $_GET['conta_id'] ?? '';
            
            require_once '../config/database.php';
            // $pdo já disponível via require database.php
            
            // Buscar movimentações
            $sql = "
                SELECT
                    data_movimentacao,
                    descricao,
                    valor
                FROM movimentacoes_financeiras
                WHERE user_id = :user_id AND tipo = 'receita'
                AND MONTH(data_movimentacao) = :mes
                AND YEAR(data_movimentacao) = :ano
            ";
            $params = ['user_id' => $userId, 'mes' => $mes, 'ano' => $ano];
            
            if ($conta_id) {
                $sql .= " AND conta_id = :conta_id";
                $params['conta_id'] = $conta_id;
            }
            
            $sql .= " ORDER BY data_movimentacao ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Criar PDF
            $pdf = new EventProPDF();
            $pdf->AddPage();
            $pdf->SetY(50);
            
            // ===== TÍTULO =====
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 8, 'RELATÓRIO MENSAL DAS RECEITAS BRUTAS', 0, 1, 'C');
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 5, 'Anexo I da Resolução CGSN nº 140/2018 - Art. 104-A', 0, 1, 'C');
            
            $pdf->Ln(5);
            
            // ===== PERÍODO =====
            $meses = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            ];
            
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, 'Período: ' . $meses[(int)$mes] . '/' . $ano, 0, 1, 'C');
            
            $pdf->Ln(5);
            
            // ===== TABELA =====
            $pdf->SetFillColor(70, 130, 180);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 10);
            
            $pdf->Cell(30, 7, 'Data', 1, 0, 'C', true);
            $pdf->Cell(100, 7, 'Descrição', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'Valor (R$)', 1, 1, 'C', true);
            
            // Corpo da tabela
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(245, 245, 245);
            
            $fill = false;
            $total = 0;
            
            foreach ($movimentacoes as $mov) {
                $valor = floatval($mov['valor']);
                $total += $valor;
                
                $pdf->Cell(30, 6, EventProPDF::formatarData($mov['data_movimentacao']), 1, 0, 'C', $fill);
                $pdf->Cell(100, 6, substr($mov['descricao'], 0, 50), 1, 0, 'L', $fill);
                $pdf->Cell(40, 6, EventProPDF::formatarValor($valor), 1, 1, 'R', $fill);
                
                $fill = !$fill;
            }
            
            // Total
            $pdf->SetFillColor(70, 130, 180);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(130, 8, 'TOTAL DO MÊS:', 1, 0, 'R', true);
            $pdf->Cell(40, 8, EventProPDF::formatarValor($total), 1, 1, 'R', true);
            
            // Gerar PDF
            $nomeArquivo = 'Relatorio_MEI_' . $mes . '_' . $ano . '.pdf';
            $pdf->Output($nomeArquivo, 'I');
            break;
        
        // ============================================
        // RECIBO DE PAGAMENTO
        // ============================================
        
        case 'recibo':
            $eventoId = $_GET['evento_id'] ?? $_POST['evento_id'] ?? 0;
            $valor = $_GET['valor'] ?? $_POST['valor'] ?? 0;
            $referente = $_GET['referente'] ?? $_POST['referente'] ?? '';
            
            if (!$eventoId || !$valor) {
                throw new Exception('Dados incompletos para gerar recibo');
            }
            
            require_once '../config/database.php';
            // $pdo já disponível via require database.php
            
            // Buscar dados do cliente
            $stmt = $pdo->prepare("
                SELECT c.nome, c.cpf
                FROM eventos_eventpro e
                LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
                WHERE e.id = :id AND e.user_id = :user_id
            ");
            $stmt->execute(['id' => $eventoId, 'user_id' => $userId]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Criar PDF
            $pdf = new EventProPDF();
            $pdf->AddPage();
            $pdf->SetY(50);
            
            // ===== TÍTULO =====
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->Cell(0, 10, 'RECIBO', 0, 1, 'C');
            
            $pdf->Ln(10);
            
            // ===== VALOR POR EXTENSO =====
            require_once '../libs/NumeroExtenso.php';
            $valorExtenso = NumeroExtenso::converter($valor);
            
            // ===== CORPO DO RECIBO =====
            $pdf->SetFont('helvetica', '', 11);
            
            $texto = "Recebi de " . ($cliente['nome'] ?? 'Cliente') . 
                     (!empty($cliente['cpf']) ? ", CPF " . $cliente['cpf'] : "") .
                     ", a importância de " . EventProPDF::formatarValor($valor) . 
                     " (" . $valorExtenso . "), referente a " . 
                     ($referente ?: 'serviços prestados') . ".";
            
            $pdf->SetX(15);
            $pdf->MultiCell(0, 7, $texto, 0, 'J');
            
            $pdf->Ln(10);
            
            // ===== DATA E LOCAL =====
            $stmt = $pdo->query("SELECT valor FROM configuracoes_sistema WHERE chave = 'empresa_cidade'");
            $cidade = $stmt->fetchColumn() ?: 'São Paulo';
            
            $pdf->SetX(15);
            $pdf->Cell(0, 5, $cidade . ', ' . date('d') . ' de ' . 
                       date('F') . ' de ' . date('Y'), 0, 1, 'L');
            
            $pdf->Ln(20);
            
            // ===== ASSINATURA =====
            $pdf->Line(60, $pdf->GetY(), 150, $pdf->GetY());
            $pdf->Ln(1);
            $pdf->SetX(60);
            
            $stmt = $pdo->query("SELECT valor FROM configuracoes_sistema WHERE chave = 'empresa_nome'");
            $nomeEmpresa = $stmt->fetchColumn() ?: 'EventProDJ';
            
            $pdf->Cell(90, 5, $nomeEmpresa, 0, 1, 'C');
            
            $stmt = $pdo->query("SELECT valor FROM configuracoes_sistema WHERE chave = 'empresa_cnpj'");
            $cnpj = $stmt->fetchColumn();
            
            if ($cnpj) {
                $pdf->SetX(60);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(90, 5, 'CNPJ: ' . $cnpj, 0, 1, 'C');
            }
            
            // Gerar PDF
            $nomeArquivo = 'Recibo_' . $eventoId . '_' . date('Y-m-d') . '.pdf';
            $pdf->Output($nomeArquivo, 'I');
            break;
        
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    // Em caso de erro, retornar JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
