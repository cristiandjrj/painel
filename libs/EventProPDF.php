<?php
/**
 * Classe Base para Geração de PDFs - EventProDJ
 * Usando TCPDF para gerar orçamentos, contratos e relatórios
 */

// Incluir TCPDF
require_once(__DIR__ . '/../vendor/autoload.php'); // Se usar Composer
// OU
// require_once(__DIR__ . '/../libs/tcpdf/tcpdf.php'); // Se baixar manualmente

use TCPDF;

class EventProPDF extends TCPDF {
    private $config;
    
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        
        // Carregar configurações da empresa
        $this->carregarConfig();
        
        // Configurações padrão
        $this->SetCreator('EventProDJ');
        $this->SetAuthor($this->config['empresa_nome'] ?? 'EventProDJ');
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(TRUE, 15);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->SetFont('helvetica', '', 10);
    }
    
    private function carregarConfig() {
        try {
            require_once(__DIR__ . '/../config/database.php');
            $database = new Database();
            $conn = $database->getConnection();

            // Buscar da tabela correta (configuracoes_eventpro)
            $stmt = $conn->query("SELECT * FROM configuracoes_eventpro ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Mapear colunas da tabela para as chaves usadas no Header
                $this->config = [
                    'empresa_nome'        => $row['nome_empresa'] ?? '',
                    'empresa_razao_social'=> $row['nome_corporativo'] ?? '',
                    'empresa_cnpj'        => $row['cnpj'] ?? '',
                    'empresa_telefone'    => $row['telefone'] ?? '',
                    'empresa_whatsapp'    => $row['whatsapp'] ?? '',
                    'empresa_email'       => $row['email'] ?? '',
                    'empresa_cidade'      => $row['cidade'] ?? '',
                    'empresa_estado'      => $row['estado'] ?? '',
                    'empresa_endereco'    => $row['endereco'] ?? '',
                    'empresa_instagram'   => $row['instagram'] ?? '',
                    'empresa_facebook'    => $row['facebook'] ?? '',
                    'empresa_youtube'     => $row['youtube'] ?? '',
                    'empresa_website'     => $row['site'] ?? '',
                    'empresa_logo_base64' => $row['logo_base64'] ?? '',
                    'empresa_assinatura_base64' => $row['assinatura_base64'] ?? '',
                ];
            } else {
                $this->config = ['empresa_nome' => 'EventProDJ'];
            }
        } catch (Exception $e) {
            // Valores padrão se der erro
            $this->config = [
                'empresa_nome'        => 'EventProDJ',
                'empresa_telefone'    => '',
                'empresa_email'       => '',
                'empresa_cnpj'        => '',
                'empresa_logo_base64' => ''
            ];
        }
    }
    
    // Cabeçalho personalizado
    public function Header() {
        $margemEsquerda = 15;
        $margemDireita = $this->getPageWidth() - 15;
        $larguraPagina = $margemDireita - $margemEsquerda;

        $xDados = $margemEsquerda;

        // Logo (se existir)
        if (!empty($this->config['empresa_logo_base64'])) {
            try {
                $logo = $this->config['empresa_logo_base64'];
                if (strpos($logo, 'data:image') === 0) {
                    $logo = explode(',', $logo)[1];
                }
                $logoDecoded = base64_decode($logo);
                $this->Image('@' . $logoDecoded, $margemEsquerda, 8, 28, 28, '', '', '', false, 300, '', false, false, 0);
                $xDados = $margemEsquerda + 32;
            } catch (Exception $e) {
                // Se der erro, ignora logo
            }
        }

        $larguraDados = $margemDireita - $xDados;

        // Nome da empresa
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY($xDados, 10);
        $this->Cell($larguraDados, 5, $this->config['empresa_nome'] ?? 'EventProDJ', 0, 1, 'L');

        $this->SetFont('helvetica', '', 9);
        $y = 16;

        if (!empty($this->config['empresa_cnpj'])) {
            $this->SetXY($xDados, $y);
            $this->Cell($larguraDados, 4, 'CNPJ: ' . $this->config['empresa_cnpj'], 0, 1, 'L');
            $y += 4;
        }

        if (!empty($this->config['empresa_email'])) {
            $this->SetXY($xDados, $y);
            $this->Cell($larguraDados, 4, 'Email: ' . $this->config['empresa_email'], 0, 1, 'L');
            $y += 4;
        }

        if (!empty($this->config['empresa_telefone'])) {
            $this->SetXY($xDados, $y);
            $this->Cell($larguraDados, 4, 'Tel: ' . $this->config['empresa_telefone'], 0, 1, 'L');
            $y += 4;
        }

        if (!empty($this->config['empresa_whatsapp'])) {
            $this->SetXY($xDados, $y);
            $this->Cell($larguraDados, 4, 'WhatsApp: ' . $this->config['empresa_whatsapp'], 0, 1, 'L');
            $y += 4;
        }

        // Cidade/Estado
        $cidade = $this->config['empresa_cidade'] ?? '';
        $estado  = $this->config['empresa_estado']  ?? '';
        $localidade = trim($cidade . ($estado ? '/' . $estado : ''));
        if (!empty($localidade)) {
            $this->SetXY($xDados, $y);
            $this->Cell($larguraDados, 4, $localidade, 0, 1, 'L');
            $y += 4;
        }

        // Primeira linha separadora (acima das redes sociais)
        $yLinha1 = max(38, $y + 2);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->Line($margemEsquerda, $yLinha1, $margemDireita, $yLinha1);

        // Redes sociais com ícones desenhados
        $redesSociais = [];
        if (!empty($this->config['empresa_instagram'])) {
            $redesSociais[] = ['tipo' => 'instagram', 'texto' => $this->config['empresa_instagram']];
        }
        if (!empty($this->config['empresa_facebook'])) {
            $redesSociais[] = ['tipo' => 'facebook', 'texto' => $this->config['empresa_facebook']];
        }
        if (!empty($this->config['empresa_youtube'])) {
            $redesSociais[] = ['tipo' => 'youtube', 'texto' => $this->config['empresa_youtube']];
        }
        if (!empty($this->config['empresa_website'])) {
            $redesSociais[] = ['tipo' => 'website', 'texto' => $this->config['empresa_website']];
        }

        $iconSize = 4;
        $yRedes   = $yLinha1 + 2;

        if (!empty($redesSociais)) {
            $numRedes = count($redesSociais);
            $espaco   = $larguraPagina / $numRedes;

            foreach ($redesSociais as $i => $rede) {
                $xRede = $margemEsquerda + ($i * $espaco);
                $this->drawSocialIcon($rede['tipo'], $xRede, $yRedes, $iconSize);
                $this->SetFont('helvetica', '', 7.5);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY($xRede + $iconSize + 1.5, $yRedes + ($iconSize / 2) - 2);
                $this->Cell($espaco - $iconSize - 2, 4, $rede['texto'], 0, 0, 'L');
            }
        }

        // Segunda linha separadora (abaixo das redes sociais)
        $yLinha2 = $yRedes + $iconSize + 2;
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->Line($margemEsquerda, $yLinha2, $margemDireita, $yLinha2);
    }

    // Desenha ícone de rede social usando funções gráficas do TCPDF
    private function drawSocialIcon($tipo, $x, $y, $size = 4) {
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(0, 0, 0);
        $this->SetLineWidth(0.3);

        switch ($tipo) {
            case 'instagram':
                // Quadrado arredondado
                $this->RoundedRect($x, $y, $size, $size, $size * 0.25, '1111', 'D');
                // Círculo interno
                $this->Circle($x + $size / 2, $y + $size / 2, $size * 0.28, 0, 360, 'D');
                // Ponto superior direito
                $this->Circle($x + $size * 0.72, $y + $size * 0.28, $size * 0.08, 0, 360, 'DF');
                break;

            case 'facebook':
                // Círculo externo
                $this->Circle($x + $size / 2, $y + $size / 2, $size / 2, 0, 360, 'D');
                // Letra f centralizada
                $this->SetFont('helvetica', 'B', $size * 2.0);
                $this->SetXY($x, $y);
                $this->Cell($size, $size, 'f', 0, 0, 'C');
                break;

            case 'youtube':
                // Retângulo arredondado
                $this->RoundedRect($x, $y + $size * 0.1, $size, $size * 0.8, $size * 0.2, '1111', 'D');
                // Triângulo play
                $this->SetFillColor(0, 0, 0);
                $this->Polygon([
                    $x + $size * 0.33, $y + $size * 0.28,
                    $x + $size * 0.33, $y + $size * 0.72,
                    $x + $size * 0.72, $y + $size * 0.50,
                ], 'DF');
                break;

            case 'website':
                // Círculo (globo)
                $this->Circle($x + $size / 2, $y + $size / 2, $size / 2, 0, 360, 'D');
                // Linha horizontal
                $this->Line($x, $y + $size / 2, $x + $size, $y + $size / 2);
                // Linha vertical
                $this->Line($x + $size / 2, $y, $x + $size / 2, $y + $size);
                // Elipse meridiano
                $this->Ellipse($x + $size / 2, $y + $size / 2, $size * 0.22, $size / 2 - 0.1, 0, 0, 360, 'D');
                break;
        }

        // Restaurar cores e espessura padrão
        $this->SetFillColor(0, 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
    }
    
    // Rodapé personalizado
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
    
    // Método auxiliar para formatação de valor
    public static function formatarValor($valor) {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
    
    // Método auxiliar para formatação de data
    public static function formatarData($data) {
        if (empty($data)) return '';
        $timestamp = strtotime($data);
        return date('d/m/Y', $timestamp);
    }
    
    // Método para adicionar seção com título
    public function adicionarSecao($titulo, $y = null) {
        if ($y !== null) {
            $this->SetY($y);
        }
        
        $margemEsquerda = 15;
        $margemDireita = $this->getPageWidth() - 15;
        $larguraPagina = $margemDireita - $margemEsquerda;
        
        $currentY = $this->GetY();
        
        // Fundo cinza claro
        $this->SetFillColor(240, 240, 240);
        $this->Rect($margemEsquerda, $currentY, $larguraPagina, 7, 'F');
        
        // Texto do título
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(50, 50, 50);
        $this->SetXY($margemEsquerda + 2, $currentY + 1.5);
        $this->Cell($larguraPagina - 4, 5, $titulo, 0, 1, 'L');
        
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', '', 10);
        $this->Ln(2);
    }
    
    // Método para adicionar campo chave-valor
    public function adicionarCampo($chave, $valor, $larguraChave = 50) {
        $currentY = $this->GetY();
        
        // Chave em negrito
        $this->SetFont('helvetica', 'B', 10);
        $this->SetX(15);
        $this->Cell($larguraChave, 5, $chave . ':', 0, 0, 'L');
        
        // Valor normal
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, $valor, 0, 1, 'L');
    }
    
    // Método para adicionar tabela de serviços
    public function adicionarTabelaServicos($servicos, $taxas = [], $desconto = 0) {
        $margemEsquerda = 15;
        $margemDireita = $this->getPageWidth() - 15;
        $larguraPagina = $margemDireita - $margemEsquerda;
        
        // Cabeçalho da tabela
        $this->SetFillColor(70, 130, 180);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 10);
        
        $this->Cell(100, 7, 'Descrição', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Valor', 1, 1, 'C', true);
        
        // Corpo da tabela
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', '', 10);
        $this->SetFillColor(245, 245, 245);
        
        $fill = false;
        $subtotal = 0;
        
        // Serviços
        foreach ($servicos as $servico) {
            $valor = floatval($servico['valor'] ?? 0);
            $subtotal += $valor;
            
            $this->Cell(100, 6, $servico['nome'], 1, 0, 'L', $fill);
            $this->Cell(40, 6, self::formatarValor($valor), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }
        
        // Taxas adicionais
        $totalTaxas = 0;
        if (!empty($taxas)) {
            foreach ($taxas as $taxa) {
                $valor = floatval($taxa['valor'] ?? 0);
                $totalTaxas += $valor;
                
                $this->Cell(100, 6, $taxa['descricao'] ?? 'Taxa', 1, 0, 'L', $fill);
                $this->Cell(40, 6, self::formatarValor($valor), 1, 1, 'R', $fill);
                
                $fill = !$fill;
            }
        }
        
        // Subtotal
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(100, 6, 'Subtotal:', 1, 0, 'R');
        $this->Cell(40, 6, self::formatarValor($subtotal), 1, 1, 'R');
        
        // Outras taxas
        if ($totalTaxas > 0) {
            $this->Cell(100, 6, 'Outras Taxas:', 1, 0, 'R');
            $this->Cell(40, 6, self::formatarValor($totalTaxas), 1, 1, 'R');
        }
        
        // Desconto
        if ($desconto > 0) {
            $this->SetTextColor(0, 128, 0);
            $this->Cell(100, 6, 'Desconto:', 1, 0, 'R');
            $this->Cell(40, 6, '- ' . self::formatarValor($desconto), 1, 1, 'R');
            $this->SetTextColor(0, 0, 0);
        }
        
        // Total
        $total = $subtotal + $totalTaxas - $desconto;
        $this->SetFillColor(70, 130, 180);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(100, 8, 'VALOR TOTAL:', 1, 0, 'R', true);
        $this->Cell(40, 8, self::formatarValor($total), 1, 1, 'R', true);
        
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', '', 10);
        
        return $total;
    }
    
    // Método para adicionar campo de assinatura
    public function adicionarAssinatura($titulo = 'Assinatura do Cliente', $y = null) {
        if ($y !== null) {
            $this->SetY($y);
        }
        
        $this->Ln(10);
        
        $this->Line(15, $this->GetY(), 100, $this->GetY());
        $this->Ln(1);
        $this->SetX(15);
        $this->Cell(85, 5, $titulo, 0, 1, 'C');
    }
}

/**
 * Classe para Orçamento PDF
 */
class OrcamentoPDF extends EventProPDF {
    
    public function gerar($eventoId) {
        try {
            // Buscar dados do evento
            require_once(__DIR__ . '/../config/database.php');
            $database = new Database();
            $conn = $database->getConnection();
            
            $stmt = $conn->prepare("
                SELECT 
                    e.*,
                    c.nome as cliente_nome,
                    c.telefone as cliente_telefone,
                    c.email as cliente_email,
                    c.cpf as cliente_cpf
                FROM eventos_eventpro e
                LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
                WHERE e.id = :id
            ");
            $stmt->execute(['id' => $eventoId]);
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$evento) {
                throw new Exception('Evento não encontrado');
            }
            
            // Buscar serviços do evento
            $stmt = $conn->prepare("
                SELECT
                    COALESCE(s.servico_nome, cs.nome, s.nome) as nome,
                    COALESCE(s.valor_total, s.valor_unitario, s.valor, 0) as valor
                FROM evento_servicos s
                LEFT JOIN catalogo_servicos cs ON s.servico_id = cs.id
                WHERE s.evento_id = :evento_id
            ");
            $stmt->execute(['evento_id' => $eventoId]);
            $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar taxas (deslocamento, etc)
            $taxas = [];
            if (!empty($evento['deslocamento']) && $evento['deslocamento'] > 0) {
                $taxas[] = [
                    'descricao' => 'Deslocamento',
                    'valor' => $evento['deslocamento']
                ];
            }
            
            // Adicionar página
            $this->AddPage();
            $this->SetY(50);
            
            // ===== TÍTULO =====
            $this->adicionarSecao('ORÇAMENTO Nº ' . str_pad($eventoId, 6, '0', STR_PAD_LEFT) . '/' . date('Y'));
            
            // ===== DADOS DO CLIENTE =====
            $this->adicionarSecao('DADOS DO CLIENTE');
            $this->adicionarCampo('Nome', $evento['cliente_nome']);
            if (!empty($evento['cliente_telefone'])) {
                $this->adicionarCampo('Telefone', $evento['cliente_telefone']);
            }
            if (!empty($evento['cliente_email'])) {
                $this->adicionarCampo('E-mail', $evento['cliente_email']);
            }
            if (!empty($evento['cliente_cpf'])) {
                $this->adicionarCampo('CPF', $evento['cliente_cpf']);
            }
            
            $this->Ln(3);
            
            // ===== DADOS DO EVENTO =====
            $this->adicionarSecao('DADOS DO EVENTO');
            $this->adicionarCampo('Tipo de Evento', $evento['tipo']);
            $this->adicionarCampo('Data do Evento', self::formatarData($evento['data_evento']));
            
            if (!empty($evento['hora_inicio']) && !empty($evento['hora_fim'])) {
                $this->adicionarCampo('Horário', $evento['hora_inicio'] . ' às ' . $evento['hora_fim']);
            }
            
            // Local do Evento - exibir apenas o Complemento (nome do salão/espaço)
            $localEvento = !empty($evento['complemento']) ? $evento['complemento'] : (!empty($evento['local']) ? $evento['local'] : '');
            if (!empty($localEvento)) {
                $this->SetFont('helvetica', 'B', 10);
                $this->SetX(15);
                $this->Cell(50, 5, 'Local do Evento:', 0, 0, 'L');
                $this->SetFont('helvetica', '', 10);
                $this->MultiCell(0, 5, $localEvento, 0, 'L');
            }
            
            $this->Ln(3);
            
            // ===== SERVIÇOS E VALORES =====
            $this->adicionarSecao('SERVIÇOS E VALORES');
            $this->Ln(2);
            
            $desconto = floatval($evento['desconto'] ?? 0);
            $total = $this->adicionarTabelaServicos($servicos, $taxas, $desconto);
            
            $this->Ln(5);
            
            // ===== FORMA DE PAGAMENTO =====
            if (!empty($evento['sinal']) && $evento['sinal'] > 0) {
                $this->adicionarSecao('FORMA DE PAGAMENTO');
                
                $sinal = floatval($evento['sinal']);
                $restante = $total - $sinal;
                
                $this->adicionarCampo('Sinal/Entrada', self::formatarValor($sinal));
                if (!empty($evento['forma_pagamento_sinal'])) {
                    $this->adicionarCampo('Forma de Pagamento', $evento['forma_pagamento_sinal']);
                }
                $this->adicionarCampo('Restante', self::formatarValor($restante) . ' (no dia do evento)');
                
                $this->Ln(3);
            }
            
            // ===== OBSERVAÇÕES =====
            if (!empty($evento['observacoes'])) {
                $this->adicionarSecao('OBSERVAÇÕES');
                $this->SetX(15);
                $this->MultiCell(0, 5, $evento['observacoes'], 0, 'J');
                $this->Ln(3);
            }
            
            // ===== VALIDADE =====
            $this->adicionarSecao('VALIDADE DO ORÇAMENTO');
            $dataValidade = !empty($evento['data_validade']) ? 
                self::formatarData($evento['data_validade']) : 
                self::formatarData(date('Y-m-d', strtotime('+30 days')));
            
            $this->SetX(15);
            $this->Cell(0, 5, 'Este orçamento é válido até ' . $dataValidade, 0, 1, 'L');
            
            $this->Ln(5);
            
            // ===== ASSINATURA =====
            $this->adicionarAssinatura();
            
            // Gerar PDF
            $nomeArquivo = 'Orcamento_' . $eventoId . '_' . date('Y-m-d') . '.pdf';
            return $this->Output($nomeArquivo, 'I'); // I = inline browser
            
        } catch (Exception $e) {
            throw new Exception('Erro ao gerar orçamento: ' . $e->getMessage());
        }
    }
}
