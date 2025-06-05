<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Recepção - 8ª DRP Rio Verde</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #003366;
            --secondary: #0066cc;
            --accent: #ff9900;
            --light: #f5f5f5;
            --dark: #333333;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo {
            height: 60px;
            margin-right: 15px;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .header-info {
            text-align: right;
            font-size: 0.9rem;
        }
        
        .header-info .date {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.2);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: var(--dark);
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .rai-fields {
            display: none;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid var(--accent);
            margin: 10px 0;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }
        
        table th {
            background-color: #f5f5f5;
            color: var(--dark);
            font-weight: 600;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .status-completed {
            background-color: rgba(0, 102, 204, 0.1);
            color: var(--secondary);
        }
        
        .tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }
        
        .tab.active {
            border-bottom-color: var(--secondary);
            color: var(--secondary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .timer {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: rgba(255, 153, 0, 0.1);
            color: var(--accent);
            font-weight: 500;
        }
        
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .search-bar input {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .logo-container {
                margin-bottom: 10px;
            }
            
            .header-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="https://gestao.seg.br/cotas/assets/images/logopcgo.png" alt="Logo Polícia Civil" class="logo">
                <div class="header-title">
                    8ª Delegacia Regional de Polícia<br>
                    <small>Rio Verde - Goiás</small>
                </div>
            </div>
            <div class="header-info">
                <div class="date" id="current-date">16/04/2025</div>
                <div id="current-time">14:30:25</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-plus"></i> Registro de Visita</h2>
            </div>
            <form id="visitor-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome do Solicitante*</label>
                        <input type="text" id="nome" name="nome" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone*</label>
                        <input type="tel" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                    </div>
                    <div class="form-group">
                        <label for="unidade">Unidade Policial de Destino*</label>
                        <select id="unidade" name="unidade" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="RAI">RAI - Recepção de Atendimento Integrado</option>
                            <option value="GRP">GRP - Gabinete Regional de Polícia</option>
                            <option value="DENARC">DENARC - Delegacia de Repressão a Narcóticos</option>
                            <option value="DEAM">DEAM - Delegacia Especializada no Atendimento à Mulher</option>
                            <option value="DEA">DEA - Delegacia Estadual de Atendimento Especializado</option>
                            <option value="DPI">DPI - Delegacia de Polícia de Investigações</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="servidor">Servidor da Recepção*</label>
                        <select id="servidor" name="servidor" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="João Silva">João Silva</option>
                            <option value="Maria Oliveira">Maria Oliveira</option>
                            <option value="Pedro Santos">Pedro Santos</option>
                            <option value="Ana Costa">Ana Costa</option>
                        </select>
                    </div>
                </div>
                
                <div id="rai-fields" class="rai-fields">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="senha">Senha de Atendimento*</label>
                            <input type="text" id="senha" name="senha" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo de Atendimento*</label>
                            <select id="tipo" name="tipo" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="Normal">Normal</option>
                                <option value="Preferencial">Preferencial</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar Entrada</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-users"></i> Visitantes</h2>
                <div class="search-bar">
                    <input type="text" id="search" class="form-control" placeholder="Buscar visitante...">
                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
            
            <div class="tabs">
                <div class="tab active" data-tab="current">Em Atendimento</div>
                <div class="tab" data-tab="completed">Finalizados Hoje</div>
            </div>
            
            <div class="tab-content active" id="current-tab">
                <div class="table-responsive">
                    <table id="current-visitors">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Unidade</th>
                                <th>Entrada</th>
                                <th>Permanência</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Carlos Alberto Ferreira</td>
                                <td>(64) 99988-7766</td>
                                <td>DEAM</td>
                                <td>13:45</td>
                                <td><span class="timer">00:45:13</span></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Finalizar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Juliana Mendes</td>
                                <td>(64) 98765-4321</td>
                                <td>RAI</td>
                                <td>14:10</td>
                                <td><span class="timer">00:20:25</span></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Finalizar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-content" id="completed-tab">
                <div class="table-responsive">
                    <table id="completed-visitors">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Unidade</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Permanência</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Roberto Almeida</td>
                                <td>(64) 99123-4567</td>
                                <td>GRP</td>
                                <td>09:15</td>
                                <td>10:30</td>
                                <td>01:15:00</td>
                            </tr>
                            <tr>
                                <td>Fernanda Costa</td>
                                <td>(64) 98888-5555</td>
                                <td>DENARC</td>
                                <td>10:30</td>
                                <td>11:45</td>
                                <td>01:15:00</td>
                            </tr>
                            <tr>
                                <td>Marcos Paulo Silva</td>
                                <td>(64) 99456-7890</td>
                                <td>RAI</td>
                                <td>11:00</td>
                                <td>12:20</td>
                                <td>01:20:00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Atualizar data e hora
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { day: '2-digit', month: '2-digit', year: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('pt-BR', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('pt-BR', timeOptions);
        }
        
        // Inicializar atualização de data e hora
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Lógica para mostrar/esconder campos RAI
        document.getElementById('unidade').addEventListener('change', function() {
            const raiFields = document.getElementById('rai-fields');
            if (this.value === 'RAI') {
                raiFields.style.display = 'block';
                document.getElementById('senha').setAttribute('required', 'required');
                document.getElementById('tipo').setAttribute('required', 'required');
            } else {
                raiFields.style.display = 'none';
                document.getElementById('senha').removeAttribute('required');
                document.getElementById('tipo').removeAttribute('required');
            }
        });
        
        // Manipular troca de abas
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remover classe active de todas as abas
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Adicionar classe active à aba clicada
                this.classList.add('active');
                document.getElementById(this.dataset.tab + '-tab').classList.add('active');
            });
        });
        
        // Formatar telefone
        document.getElementById('telefone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 2) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
            }
            if (value.length > 10) {
                value = value.substring(0, 10) + '-' + value.substring(10);
            }
            
            this.value = value;
        });
        
        // Mock de atualização da permanência
        function updateTimers() {
            const timers = document.querySelectorAll('.timer');
            timers.forEach(timer => {
                const time = timer.textContent.split(':');
                let hours = parseInt(time[0]);
                let minutes = parseInt(time[1]);
                let seconds = parseInt(time[2]);
                
                seconds++;
                if (seconds >= 60) {
                    seconds = 0;
                    minutes++;
                    if (minutes >= 60) {
                        minutes = 0;
                        hours++;
                    }
                }
                
                timer.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            });
        }
        
        // Inicializar atualização dos timers
        setInterval(updateTimers, 1000);
        
        // Lógica de envio do formulário (simulação)
        document.getElementById('visitor-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Aqui você adicionaria a lógica para enviar os dados ao backend
            alert('Entrada registrada com sucesso!');
            
            // Simular adição à tabela
            const nome = document.getElementById('nome').value;
            const telefone = document.getElementById('telefone').value;
            const unidade = document.getElementById('unidade').options[document.getElementById('unidade').selectedIndex].value;
            
            const now = new Date();
            const hora = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            
            const tbody = document.querySelector('#current-visitors tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${nome}</td>
                <td>${telefone}</td>
                <td>${unidade}</td>
                <td>${hora}</td>
                <td><span class="timer">00:00:00</span></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Finalizar</button>
                    </div>
                </td>
            `;
            
            tbody.appendChild(newRow);
            
            // Limpar formulário
            this.reset();
            document.getElementById('rai-fields').style.display = 'none';
        });
        
        // Adicionar lógica para finalizar visita
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-success')) {
                const row = e.target.closest('tr');
                if (confirm('Deseja finalizar este atendimento?')) {
                    // Aqui você adicionaria a lógica para enviar os dados de saída ao backend
                    
                    // Mover para a aba de finalizados
                    const nome = row.cells[0].textContent;
                    const telefone = row.cells[1].textContent;
                    const unidade = row.cells[2].textContent;
                    const entrada = row.cells[3].textContent;
                    const permanencia = row.cells[4].querySelector('.timer').textContent;
                    
                    const now = new Date();
                    const saida = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                    
                    const tbody = document.querySelector('#completed-visitors tbody');
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${nome}</td>
                        <td>${telefone}</td>
                        <td>${unidade}</td>
                        <td>${entrada}</td>
                        <td>${saida}</td>
                        <td>${permanencia}</td>
                    `;
                    
                    tbody.appendChild(newRow);
                    row.remove();
                }
            }
        });
        
        // Lógica de pesquisa
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tables = document.querySelectorAll('table');
            
            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>