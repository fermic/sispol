document.addEventListener('DOMContentLoaded', function () {
    const situacaoField = document.getElementById('situacao');
    const tipoMovimentacaoField = document.getElementById('tipo_id');
    const dataConclusaoRow = document.getElementById('data-conclusao-nova-situacao-row');
    const novaSituacaoRow = document.getElementById('nova-situacao-row');
    const novaSituacaoSelect = document.getElementById('nova_situacao_procedimento');
    const processoFields = document.getElementById('processo-judicial-fields');
    const oficiosFields = document.getElementById('oficios-fields');

    function toggleFields() {
        const situacao = situacaoField.value;
        const tipoMovimentacao = tipoMovimentacaoField.value;

        // Exibir ou ocultar Data de Conclusão e Nova Situação
        if (situacao === 'Finalizado') {
            dataConclusaoRow.style.display = 'flex';
            document.getElementById('data_conclusao').setAttribute('required', 'required');

            if (tipoMovimentacao == 5 || tipoMovimentacao == 7) {
                novaSituacaoRow.style.display = 'block';
                novaSituacaoSelect.setAttribute('required', 'required');

                // Recarregar opções de Nova Situação
                let categoria = tipoMovimentacao == 7 ? 'Desaparecimento' : 'IP';
                fetch(`situacoes.php?categoria=${categoria}`)
                    .then(response => response.json())
                    .then(situacoes => {
                        novaSituacaoSelect.innerHTML = '<option value="" disabled selected>Selecione a Nova Situação</option>';
                        situacoes.forEach(situacao => {
                            const option = document.createElement('option');
                            option.value = situacao.ID;
                            option.textContent = situacao.Nome;
                            novaSituacaoSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erro ao carregar situações:', error));
            } else {
                novaSituacaoRow.style.display = 'none';
                novaSituacaoSelect.removeAttribute('required');
            }
        } else {
            dataConclusaoRow.style.display = 'none';
            novaSituacaoRow.style.display = 'none';
            novaSituacaoSelect.removeAttribute('required');
            document.getElementById('data_conclusao').removeAttribute('required');
        }

        // Mostrar ou ocultar campos de Processo Judicial
        if (tipoMovimentacao == 5) {
            processoFields.style.display = 'block';
        } else {
            processoFields.style.display = 'none';
            document.getElementById('numero_processo').value = '';
        }

        // Mostrar ou ocultar campos de Ofício
        if (tipoMovimentacao == 9) {
            oficiosFields.style.display = 'block';
        } else {
            oficiosFields.style.display = 'none';
            // Limpar os campos de Ofício
            document.getElementById('numero_oficio').value = '';
            document.getElementById('data_oficio').value = '';
            document.getElementById('destino').value = '';
            document.getElementById('sei').value = '';
        }
    }

    // Adicionar Listeners
    situacaoField.addEventListener('change', toggleFields);
    tipoMovimentacaoField.addEventListener('change', toggleFields);

    // Executar a lógica ao carregar a página
    toggleFields();
});

document.addEventListener('DOMContentLoaded', function () {
    // Confete ao salvar Remessa de IP finalizada
    const form = document.querySelector('form');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const tipoMovimentacao = document.getElementById('tipo_id').value;
        const situacao = document.getElementById('situacao').value;

        if (tipoMovimentacao == 5 && situacao === 'Finalizado') {
            const launchConfetti = () => {
                const duration = 3 * 1000;
                const end = Date.now() + duration;

                (function frame() {
                    confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 } });
                    confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 } });

                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                })();
            };

            launchConfetti();

            setTimeout(() => {
                form.submit();
            }, 3000);
        } else {
            form.submit();
        }
    });
});
