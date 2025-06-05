<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>




    <!-- jQuery (para interações, se necessário) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="calendar"></div>

    <script>
        
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Visão inicial do calendário
        locale: 'pt-br', // Define o idioma para português
        headerToolbar: {
            left: 'prev,next today', // Botões de navegação
            center: 'title', // Título central
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Vistas disponíveis
        },
        events: {
            url: 'get_movimentacoes.php', // Endpoint que retorna os dados
            failure: function () {
                alert('Erro ao carregar os eventos!');
            }
        },
        eventClick: function (info) {
            // Exibe detalhes do evento ao clicar
            alert(`Assunto: ${info.event.title}\nData: ${info.event.start.toLocaleDateString()}`);
        }
    });

    calendar.render(); // Renderiza o calendário
});
        
    </script>
</body>
</html>
