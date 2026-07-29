import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Máscara de telefone no formato (00) 00000-0000 (15 caracteres), aplicada a
 * qualquer campo com o atributo data-mascara="telefone". Formata
 * progressivamente enquanto o usuário digita, para reduzir erros de cadastro.
 */
function aplicarMascaraTelefone(valor) {
    const digitos = valor.replace(/\D/g, '').slice(0, 11);

    if (digitos.length > 7) {
        return digitos.replace(/^(\d{2})(\d{5})(\d{0,4}).*$/, '($1) $2-$3');
    }

    if (digitos.length > 2) {
        return digitos.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
    }

    if (digitos.length > 0) {
        return digitos.replace(/^(\d*)$/, '($1');
    }

    return '';
}

/**
 * Máscara de CEP no formato 00000-000, apenas números.
 */
function aplicarMascaraCep(valor) {
    const digitos = valor.replace(/\D/g, '').slice(0, 8);

    if (digitos.length > 5) {
        return digitos.replace(/^(\d{5})(\d{0,3})$/, '$1-$2');
    }

    return digitos;
}

document.addEventListener('input', (evento) => {
    if (evento.target.matches('[data-mascara="telefone"]')) {
        evento.target.value = aplicarMascaraTelefone(evento.target.value);
    }

    if (evento.target.matches('[data-mascara="cep"]')) {
        evento.target.value = aplicarMascaraCep(evento.target.value);
    }
});

/**
 * Ao completar os 8 dígitos do CEP em um campo marcado com
 * data-busca-cep, consulta o ViaCEP (serviço público, sem necessidade de
 * chave de acesso) e preenche automaticamente endereço, bairro, cidade e
 * estado, para agilizar o cadastro e reduzir erros de digitação.
 */
document.addEventListener('input', async (evento) => {
    if (!evento.target.matches('[data-busca-cep]')) {
        return;
    }

    const digitos = evento.target.value.replace(/\D/g, '');
    const statusCep = document.getElementById('status-cep');

    if (digitos.length !== 8) {
        if (statusCep) {
            statusCep.textContent = '';
        }

        return;
    }

    if (statusCep) {
        statusCep.textContent = 'Buscando endereço...';
        statusCep.className = 'mt-1 text-sm text-gray-500';
    }

    try {
        const resposta = await fetch(`https://viacep.com.br/ws/${digitos}/json/`);
        const dados = await resposta.json();

        if (dados.erro) {
            if (statusCep) {
                statusCep.textContent = 'CEP não encontrado. Preencha o endereço manualmente.';
                statusCep.className = 'mt-1 text-sm text-amber-600';
            }

            return;
        }

        const campos = {
            endereco: dados.logradouro,
            bairro: dados.bairro,
            cidade: dados.localidade,
            estado: dados.uf,
        };

        Object.entries(campos).forEach(([id, valorCampo]) => {
            const campo = document.getElementById(id);

            if (campo && valorCampo) {
                campo.value = valorCampo;
            }
        });

        if (statusCep) {
            statusCep.textContent = 'Endereço preenchido automaticamente.';
            statusCep.className = 'mt-1 text-sm text-green-600';
        }
    } catch (erro) {
        if (statusCep) {
            statusCep.textContent = 'Não foi possível consultar o CEP agora. Preencha o endereço manualmente.';
            statusCep.className = 'mt-1 text-sm text-amber-600';
        }
    }
});
