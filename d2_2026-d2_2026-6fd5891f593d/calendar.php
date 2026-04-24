<div class="nav-calendar">
    <button class="view-btn" onclick="setView('day')">Dzień</button>
    <button class="view-btn" onclick="setView('week')">Tydzień</button>
    <button class="view-btn active" onclick="setView('month')">Miesiąc</button>
</div>

<div id="calendar" style="height: 800px;"></div>

<script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.js"></script>

<script>

const Calendar = tui.Calendar;

const calendar = new Calendar('#calendar', {
    defaultView: 'month',
    useCreationPopup: true,
    useDetailPopup: true,
});

document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));

        btn.classList.add('active');
    });
});


function formatDateLocal(date) {
    const pad = (n) => n.toString().padStart(2, '0');

    return (
    date.getFullYear() + '-' +
    pad(date.getMonth() + 1) + '-' +
    pad(date.getDate()) + ' ' +
    pad(date.getHours()) + ':' +
    pad(date.getMinutes()) + ':00'
    );
}

// 🔥 kliknięcie → dodaj task
calendar.on('selectDateTime', function(ev) {
    openTaskModal(ev.start, ev.end);
});

function setView(view) {
    calendar.changeView(view);
}

// 🔄 pobierz z bazy
async function loadEvents() {
    const res = await fetch('events.php');
    const data = await res.json();

    calendar.clear();

    calendar.createEvents(data.map(e => ({
        id: e.id,
        calendarId: '1',
        title: e.title,
        start: e.start,
        end: e.end,
        backgroundColor: e.backgroundColor
    })));
}

function formatForInput(date) {
    const d = new Date(date);
    const pad = (n) => n.toString().padStart(2, '0');

    return (
    d.getFullYear() + '-' +
    pad(d.getMonth() + 1) + '-' +
    pad(d.getDate()) + 'T' +
    pad(d.getHours()) + ':' +
    pad(d.getMinutes())
    );
}

function openTaskModal(start, end) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';

    modal.innerHTML = `
        <div class="modal">
        <h3>Nowe zadanie</h3>

        <label>Nazwa</label>
        <input id="title" placeholder="Np. Spotkanie z klientem">

        <label>Opis</label>
        <textarea id="desc" placeholder="Szczegóły zadania..."></textarea>

        <label>Data od</label>
        <input type="datetime-local" id="start">

        <label>Data do</label>
        <input type="datetime-local" id="end">

        <label>Status</label>
        <select id="status">
            <option value="todo">Do zrobienia</option>
            <option value="in_progress">W trakcie</option>
            <option value="completed">Zrobione</option>
        </select>

        <label>Priorytet</label>
        <select id="priority">
            <option value="low">Niski</option>
            <option value="medium">Średni</option>
            <option value="high">Wysoki</option>
        </select>

        <label>Kolor</label>
        <input type="color" id="color" value="#3b82f6">

        <div class="modal-actions">
            <button class="btn btn-secondary" id="close">Anuluj</button>
            <button class="btn btn-primary" id="save">Zapisz</button>
        </div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelector('#start').value = formatForInput(start);
    modal.querySelector('#end').value = formatForInput(end);

    // zamknięcie
    modal.querySelector('#close').onclick = () => modal.remove();

    // zapis
    modal.querySelector('#save').onclick = async () => {
        const task = {
        title: modal.querySelector('#title').value,
        description: modal.querySelector('#desc').value,
        status: modal.querySelector('#status').value,
        priority: modal.querySelector('#priority').value,
        color: modal.querySelector('#color').value,
        start: formatDateLocal(new Date(modal.querySelector('#start').value)),
        end: formatDateLocal(new Date(modal.querySelector('#end').value))
        };

        if (!task.title) {
        alert('Podaj nazwę');
        return;
        }

        await fetch('add_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(task)
        });

        calendar.clearGridSelections();

        modal.remove();
        loadEvents();
    };
}


loadEvents();
</script>