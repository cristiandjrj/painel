/**
 * Funções Auxiliares para Eventos - EventProDJ
 * Busca de CEP e Cálculo de Duração
 */

// ============================================
// BUSCA DE CEP (ViaCEP)
// ============================================

function formatarCep(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length > 5) {
        valor = valor.substring(0, 5) + '-' + valor.substring(5, 8);
    }
    
    input.value = valor;
}

async function buscarCep(cepInput, callback) {
    const cep = cepInput.value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        return;
    }
    
    // Mostrar loading
    if (document.getElementById('cepLoading')) {
        document.getElementById('cepLoading').style.display = 'block';
    }
    
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (data.erro) {
            // CEP não encontrado
            if (document.getElementById('cepError')) {
                document.getElementById('cepError').style.display = 'block';
            }
            if (document.getElementById('cepSuccess')) {
                document.getElementById('cepSuccess').style.display = 'none';
            }
        } else {
            // CEP encontrado - preencher campos
            if (callback) {
                callback(data);
            }
            
            // Mostrar sucesso
            if (document.getElementById('cepSuccess')) {
                document.getElementById('cepSuccess').style.display = 'block';
            }
            if (document.getElementById('cepError')) {
                document.getElementById('cepError').style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
    } finally {
        // Esconder loading
        if (document.getElementById('cepLoading')) {
            document.getElementById('cepLoading').style.display = 'none';
        }
    }
}

// Buscar CEP do evento
function buscarCepEvento() {
    const cepInput = document.getElementById('eventoCep');
    
    buscarCep(cepInput, function(data) {
        document.getElementById('eventoEndereco').value = data.logradouro || '';
        document.getElementById('eventoBairro').value = data.bairro || '';
        document.getElementById('eventoCidade').value = data.localidade || '';
        document.getElementById('eventoEstado').value = data.uf || '';
    });
}

// Buscar CEP do cliente
function buscarCepCliente() {
    const cepInput = document.getElementById('clienteCep');
    
    buscarCep(cepInput, function(data) {
        document.getElementById('clienteRua').value = data.logradouro || '';
        document.getElementById('clienteBairro').value = data.bairro || '';
        document.getElementById('clienteCidade').value = data.localidade || '';
        document.getElementById('clienteEstado').value = data.uf || '';
    });
}

// ============================================
// CÁLCULO DE DURAÇÃO
// ============================================

function calcularDuracao(horaInicio, horaFim) {
    if (!horaInicio || !horaFim) {
        return '';
    }
    
    // Converter para minutos
    const [h1, m1] = horaInicio.split(':').map(Number);
    const [h2, m2] = horaFim.split(':').map(Number);
    
    let minutos1 = h1 * 60 + m1;
    let minutos2 = h2 * 60 + m2;
    
    // Se hora fim é menor, passou da meia-noite
    if (minutos2 < minutos1) {
        minutos2 += 24 * 60; // Adicionar 24 horas
    }
    
    const duracaoMinutos = minutos2 - minutos1;
    const horas = Math.floor(duracaoMinutos / 60);
    const minutos = duracaoMinutos % 60;
    
    if (minutos > 0) {
        return `${horas}h ${minutos}min`;
    } else {
        return `${horas}h`;
    }
}

function calcularDuracaoEvento() {
    const horaInicio = document.getElementById('eventoHoraInicio');
    const horaFim = document.getElementById('eventoHoraFim');
    const duracao = document.getElementById('eventoDuracao');
    
    if (horaInicio && horaFim && duracao) {
        duracao.value = calcularDuracao(horaInicio.value, horaFim.value);
    }
}

function calcularDuracaoOrcamento() {
    const horaInicio = document.getElementById('orcHoraInicio');
    const horaFim = document.getElementById('orcHoraFim');
    const duracao = document.getElementById('orcDuracao');
    
    if (horaInicio && horaFim && duracao) {
        duracao.value = calcularDuracao(horaInicio.value, horaFim.value);
    }
}

// ============================================
// MÁSCARAS DE INPUT
// ============================================

function mascaraTelefone(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length <= 10) {
        // (00) 0000-0000
        valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
    } else {
        // (00) 00000-0000
        valor = valor.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
    }
    
    input.value = valor;
}

function mascaraCPF(input) {
    let valor = input.value.replace(/\D/g, '');
    valor = valor.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*/, '$1.$2.$3-$4');
    input.value = valor;
}

function mascaraCNPJ(input) {
    let valor = input.value.replace(/\D/g, '');
    valor = valor.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2}).*/, '$1.$2.$3/$4-$5');
    input.value = valor;
}

function mascaraValor(input) {
    let valor = input.value.replace(/\D/g, '');
    valor = (valor / 100).toFixed(2);
    input.value = valor;
}

// ============================================
// USAR ENDEREÇO DO CLIENTE
// ============================================

function usarEnderecoCliente(cliente) {
    if (!cliente || !cliente.endereco) {
        return;
    }
    
    // Preencher campos do evento com dados do cliente
    if (document.getElementById('eventoCep')) {
        document.getElementById('eventoCep').value = cliente.cep || '';
    }
    if (document.getElementById('eventoEndereco')) {
        document.getElementById('eventoEndereco').value = cliente.endereco || '';
    }
    if (document.getElementById('eventoNumero')) {
        document.getElementById('eventoNumero').value = cliente.numero || '';
    }
    if (document.getElementById('eventoComplemento')) {
        document.getElementById('eventoComplemento').value = cliente.complemento || '';
    }
    if (document.getElementById('eventoBairro')) {
        document.getElementById('eventoBairro').value = cliente.bairro || '';
    }
    if (document.getElementById('eventoCidade')) {
        document.getElementById('eventoCidade').value = cliente.cidade || '';
    }
    if (document.getElementById('eventoEstado')) {
        document.getElementById('eventoEstado').value = cliente.estado || '';
    }
}

// ============================================
// VALIDAÇÕES
// ============================================

function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    
    if (cpf.length !== 11) return false;
    
    // Verificar se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    
    // Validar dígitos verificadores
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let digito1 = 11 - (soma % 11);
    if (digito1 > 9) digito1 = 0;
    
    if (parseInt(cpf.charAt(9)) !== digito1) return false;
    
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    let digito2 = 11 - (soma % 11);
    if (digito2 > 9) digito2 = 0;
    
    if (parseInt(cpf.charAt(10)) !== digito2) return false;
    
    return true;
}

function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    
    if (cnpj.length !== 14) return false;
    
    // Verificar se todos os dígitos são iguais
    if (/^(\d)\1{13}$/.test(cnpj)) return false;
    
    // Validação dos dígitos verificadores
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;
    
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0)) return false;
    
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;
    
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1)) return false;
    
    return true;
}

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Adicionar listeners para cálculo de duração
    const horaInicioEvento = document.getElementById('eventoHoraInicio');
    const horaFimEvento = document.getElementById('eventoHoraFim');
    
    if (horaInicioEvento && horaFimEvento) {
        horaInicioEvento.addEventListener('input', calcularDuracaoEvento);
        horaFimEvento.addEventListener('input', calcularDuracaoEvento);
    }
    
    const horaInicioOrc = document.getElementById('orcHoraInicio');
    const horaFimOrc = document.getElementById('orcHoraFim');
    
    if (horaInicioOrc && horaFimOrc) {
        horaInicioOrc.addEventListener('input', calcularDuracaoOrcamento);
        horaFimOrc.addEventListener('input', calcularDuracaoOrcamento);
    }
    
    // Adicionar listeners para máscaras
    const inputsTelefone = document.querySelectorAll('input[type="tel"]');
    inputsTelefone.forEach(input => {
        input.addEventListener('input', function() {
            mascaraTelefone(this);
        });
    });
});
