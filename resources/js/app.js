import Alpine from 'alpinejs';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

window.Alpine = Alpine;

Alpine.start();

/**
 * Inicializa o editor Quill em qualquer elemento com [data-quill-editor],
 * sincronizando o HTML gerado com o campo oculto indicado em
 * data-quill-target antes do envio do formulário. O conteúdo ainda é
 * sanitizado no backend (mews/purifier) — o Quill cuida só da experiência
 * de edição, nunca é a única camada de proteção contra HTML malicioso.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-quill-editor]').forEach((elementoEditor) => {
        const campoOculto = document.getElementById(elementoEditor.dataset.quillTarget);

        if (!campoOculto) {
            return;
        }

        const conteudoInicial = campoOculto.value;

        // O conteúdo inicial precisa ser carregado ANTES de o Quill inicializar
        // a partir do próprio elemento, para não conflitar com o parsing.
        elementoEditor.innerHTML = '';

        const quill = new Quill(elementoEditor, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ header: [1, 2, 3, false] }],
                    ['blockquote'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link'],
                    ['clean'],
                ],
            },
        });

        // Importante: usar a API do Quill (dangerouslyPasteHTML) para carregar
        // o conteúdo inicial, nunca "quill.root.innerHTML = ..." diretamente.
        // Setar o innerHTML manualmente deixa o modelo interno do Quill (o
        // Delta) dessincronizado do DOM visível — o editor parece funcionar,
        // mas ao editar e salvar, o conteúdo real digitado pelo usuário não é
        // capturado corretamente e a página volta ao texto original.
        if (conteudoInicial) {
            quill.clipboard.dangerouslyPasteHTML(conteudoInicial);
        }

        campoOculto.value = quill.root.innerHTML;

        quill.on('text-change', () => {
            campoOculto.value = quill.root.innerHTML;
        });

        elementoEditor.closest('form')?.addEventListener('submit', () => {
            campoOculto.value = quill.root.innerHTML;
        });
    });
});

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
