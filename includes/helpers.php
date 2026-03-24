<?php
/**
 * Helper Functions - EventProDJ
 * Funções auxiliares para cálculos e validações
 */

class EventProHelper {
    
    /**
     * Extrair valor do desconto (suporta objeto JSON ou número)
     * 
     * @param mixed $desconto Pode ser: número, JSON string, ou array
     * @return float Valor do desconto
     */
    public static function extrairValorDesconto($desconto) {
        // Se for NULL ou vazio
        if (empty($desconto)) {
            return 0.00;
        }
        
        // Se já for número
        if (is_numeric($desconto)) {
            return floatval($desconto);
        }
        
        // Se for array (PHP já decodificou)
        if (is_array($desconto)) {
            return isset($desconto['valor']) ? floatval($desconto['valor']) : 0.00;
        }
        
        // Se for string JSON
        if (is_string($desconto)) {
            $decoded = json_decode($desconto, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return isset($decoded['valor']) ? floatval($decoded['valor']) : 0.00;
            }
            // Tentar como número direto
            return floatval($desconto);
        }
        
        return 0.00;
    }
    
    /**
     * Validar valor (converter NULL/NaN para 0)
     * 
     * @param mixed $valor
     * @return float
     */
    public static function validarValor($valor) {
        if (is_null($valor) || !is_numeric($valor) || is_nan($valor)) {
            return 0.00;
        }
        
        $valorFloat = floatval($valor);
        
        // Valores negativos inválidos
        if ($valorFloat < 0) {
            return 0.00;
        }
        
        return $valorFloat;
    }
    
    /**
     * Calcular valor líquido considerando desconto
     * 
     * @param float $valorBruto
     * @param mixed $desconto
     * @return float
     */
    public static function calcularValorLiquido($valorBruto, $desconto) {
        $valorBruto = self::validarValor($valorBruto);
        $valorDesconto = self::extrairValorDesconto($desconto);
        
        $valorLiquido = $valorBruto - $valorDesconto;
        
        // Garantir que não seja negativo
        return max(0, $valorLiquido);
    }
    
    /**
     * Calcular saldo devedor
     * 
     * @param float $valorTotal
     * @param mixed $desconto
     * @param float $totalPago
     * @return float
     */
    public static function calcularSaldoDevedor($valorTotal, $desconto, $totalPago) {
        $valorLiquido = self::calcularValorLiquido($valorTotal, $desconto);
        $totalPago = self::validarValor($totalPago);
        
        $saldo = $valorLiquido - $totalPago;
        
        return max(0, $saldo);
    }
    
    /**
     * Formatar desconto para JSON
     * 
     * @param mixed $desconto
     * @param string $tipo 'real' ou 'percentual'
     * @return string JSON
     */
    public static function formatarDescontoJSON($desconto, $tipo = 'real') {
        $valor = self::validarValor($desconto);
        
        return json_encode([
            'valor' => $valor,
            'tipo' => $tipo
        ]);
    }
    
    /**
     * Calcular duração entre horários
     * 
     * @param string $horaInicio (HH:MM)
     * @param string $horaFim (HH:MM)
     * @return string (ex: "3h30min")
     */
    public static function calcularDuracao($horaInicio, $horaFim) {
        if (empty($horaInicio) || empty($horaFim)) {
            return null;
        }
        
        try {
            $inicio = new DateTime($horaInicio);
            $fim = new DateTime($horaFim);
            
            // Se fim é menor que início, passou da meia-noite
            if ($fim < $inicio) {
                $fim->modify('+1 day');
            }
            
            $diff = $inicio->diff($fim);
            
            $duracao = $diff->h . 'h';
            if ($diff->i > 0) {
                $duracao .= $diff->i . 'min';
            }
            
            return $duracao;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Limpar dados para evitar NaN/NULL no JSON
     * 
     * @param array $dados
     * @return array
     */
    public static function limparDados($dados) {
        if (!is_array($dados)) {
            if (is_numeric($dados) && is_nan($dados)) {
                return 0;
            }
            return $dados;
        }
        
        $dadosLimpos = [];
        
        foreach ($dados as $key => $value) {
            if (is_array($value)) {
                $dadosLimpos[$key] = self::limparDados($value);
            } elseif (is_numeric($value) && is_nan($value)) {
                $dadosLimpos[$key] = 0;
            } elseif (!is_null($value)) {
                $dadosLimpos[$key] = $value;
            }
        }
        
        return $dadosLimpos;
    }
    
    /**
     * Formatar valor monetário
     * 
     * @param float $valor
     * @return string
     */
    public static function formatarValor($valor) {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
    
    /**
     * Validar status de evento
     * 
     * @param string $status
     * @return bool
     */
    public static function statusValido($status) {
        $statusValidos = ['pendente', 'confirmado', 'andamento', 'concluido', 'cancelado'];
        return in_array($status, $statusValidos);
    }
    
    /**
     * Status conta para receitas (apenas status que geram receita)
     * 
     * @param string $status
     * @return bool
     */
    public static function statusContaReceita($status) {
        return in_array($status, ['confirmado', 'andamento', 'concluido']);
    }
}
