<?php
/**
 * Classe para converter números em extenso
 * Usado para recibos e contratos
 */

class NumeroExtenso {
    
    private static $unidades = [
        0 => '',
        1 => 'um',
        2 => 'dois',
        3 => 'três',
        4 => 'quatro',
        5 => 'cinco',
        6 => 'seis',
        7 => 'sete',
        8 => 'oito',
        9 => 'nove'
    ];
    
    private static $dezenas = [
        10 => 'dez',
        11 => 'onze',
        12 => 'doze',
        13 => 'treze',
        14 => 'quatorze',
        15 => 'quinze',
        16 => 'dezesseis',
        17 => 'dezessete',
        18 => 'dezoito',
        19 => 'dezenove',
        20 => 'vinte',
        30 => 'trinta',
        40 => 'quarenta',
        50 => 'cinquenta',
        60 => 'sessenta',
        70 => 'setenta',
        80 => 'oitenta',
        90 => 'noventa'
    ];
    
    private static $centenas = [
        100 => 'cento',
        200 => 'duzentos',
        300 => 'trezentos',
        400 => 'quatrocentos',
        500 => 'quinhentos',
        600 => 'seiscentos',
        700 => 'setecentos',
        800 => 'oitocentos',
        900 => 'novecentos'
    ];
    
    public static function converter($valor) {
        $valor = number_format($valor, 2, '.', '');
        list($inteiro, $decimal) = explode('.', $valor);
        
        $inteiro = (int)$inteiro;
        $centavos = (int)$decimal;
        
        $extenso = '';
        
        // Parte inteira (reais)
        if ($inteiro == 0) {
            $extenso = 'zero reais';
        } else {
            $extenso = self::converterInteiro($inteiro);
            $extenso .= $inteiro == 1 ? ' real' : ' reais';
        }
        
        // Parte decimal (centavos)
        if ($centavos > 0) {
            $extenso .= ' e ' . self::converterInteiro($centavos);
            $extenso .= $centavos == 1 ? ' centavo' : ' centavos';
        }
        
        return $extenso;
    }
    
    private static function converterInteiro($numero) {
        if ($numero == 0) {
            return '';
        }
        
        if ($numero < 10) {
            return self::$unidades[$numero];
        }
        
        if ($numero < 20) {
            return self::$dezenas[$numero];
        }
        
        if ($numero < 100) {
            $dezena = floor($numero / 10) * 10;
            $unidade = $numero % 10;
            
            $extenso = self::$dezenas[$dezena];
            if ($unidade > 0) {
                $extenso .= ' e ' . self::$unidades[$unidade];
            }
            
            return $extenso;
        }
        
        if ($numero < 1000) {
            $centena = floor($numero / 100) * 100;
            $resto = $numero % 100;
            
            if ($numero == 100) {
                return 'cem';
            }
            
            $extenso = self::$centenas[$centena];
            if ($resto > 0) {
                $extenso .= ' e ' . self::converterInteiro($resto);
            }
            
            return $extenso;
        }
        
        if ($numero < 1000000) {
            $milhar = floor($numero / 1000);
            $resto = $numero % 1000;
            
            if ($milhar == 1) {
                $extenso = 'mil';
            } else {
                $extenso = self::converterInteiro($milhar) . ' mil';
            }
            
            if ($resto > 0) {
                if ($resto < 100) {
                    $extenso .= ' e ';
                } else {
                    $extenso .= ', ';
                }
                $extenso .= self::converterInteiro($resto);
            }
            
            return $extenso;
        }
        
        if ($numero < 1000000000) {
            $milhao = floor($numero / 1000000);
            $resto = $numero % 1000000;
            
            if ($milhao == 1) {
                $extenso = 'um milhão';
            } else {
                $extenso = self::converterInteiro($milhao) . ' milhões';
            }
            
            if ($resto > 0) {
                if ($resto < 100) {
                    $extenso .= ' e ';
                } else {
                    $extenso .= ', ';
                }
                $extenso .= self::converterInteiro($resto);
            }
            
            return $extenso;
        }
        
        return '';
    }
}
