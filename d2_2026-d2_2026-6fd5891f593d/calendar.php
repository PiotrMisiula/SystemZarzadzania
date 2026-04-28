<div class="nav-calendar">
    <button class="view-btn" onclick="setView('day')">Dzień</button>
    <button class="view-btn" onclick="setView('week')">Tydzień</button>
    <button class="view-btn active" onclick="setView('month')">Miesiąc</button>
    <div class="nav">
        <button id="prev"><i class='bx bx-left-arrow-alt bx-sm'></i></button>
        <button id="today">Today</button>
        <button id="next"><i class='bx bx-right-arrow-alt bx-sm'></i></button>
    </div>
    <div id="current-date"></div>
</div>

<div id="calendar" style="height: 800px;"></div>

<script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.js"></script>

<script>

const Calendar = tui.Calendar;

const calendar = new Calendar('#calendar', {
    defaultView: 'month',
    useCreationPopup: false,
    useDetailPopup: true,
    template: {
        // To kontroluje wygląd paska w sekcji całodniowej i widoku miesiąca
        allday: function(event) {
            const start = new Date(event.start);
            const hours = String(start.getHours()).padStart(2, '0');
            const minutes = String(start.getMinutes()).padStart(2, '0');
            
            // Zwracamy HTML: godzina + tytuł
            return `<span style="font-weight: bold; font-size: 16px;">${hours}:${minutes}</span> ${event.title}`;
        },
        // Opcjonalnie dla widoku miesiąca, jeśli chcesz inny format
        monthGridHeaderExceed: function(event) {
            return `+${event.count}`;
        }
    },
});

document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));

        btn.classList.add('active');
    });
});

document.getElementById('prev').onclick = () => {
    calendar.prev();
    updateDateLabel();
};

document.getElementById('today').onclick = () => {
    calendar.today();
    updateDateLabel();
};

document.getElementById('next').onclick = () => {
    calendar.next();
    updateDateLabel();
};

calendar.on('beforeDeleteEvent', async (eventObj) => {
    const { id, calendarId } = eventObj;

    const confirmed = await showConfirmModal(
        'Usuwanie zadania', 
        'Czy na pewno chcesz bezpowrotnie usunąć to zadanie? Tej operacji nie da się cofnąć.'
    );

    if (confirmed) {
        try {
            const res = await fetch('delete_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });

            const result = await res.json();

            if (result.status === 'ok') {
                calendar.deleteEvent(id, calendarId);
            } else {
                alert('Błąd: ' + result.message);
            }
        } catch (error) {
            console.error('Błąd podczas usuwania:', error);
        }
    }
});

calendar.on('beforeUpdateEvent', (updateData) => {
    const { event } = updateData;

    openTaskModal(event.start, event.end, event);
})

function updateDateLabel() {
    const date = calendar.getDate();
    const view = calendar.getViewName();

    const pad = (n) => n.toString().padStart(2, '0');

    let text = '';

    if (view === 'month') {
    text = pad(date.getMonth() + 1) + ' - ' + date.getFullYear();
    }

    if (view === 'week') {
    const start = new Date(date);
    const end = new Date(date);

    start.setDate(start.getDate() - start.getDay() + 1); // poniedziałek
    end.setDate(start.getDate() + 6);

    text = `${pad(start.getDate())}.${pad(start.getMonth()+1)} - ${pad(end.getDate())}.${pad(end.getMonth()+1)}.${end.getFullYear()}`;
    }

    if (view === 'day') {
    text = `${pad(date.getDate())}.${pad(date.getMonth()+1)}.${date.getFullYear()}`;
    }

    document.getElementById('current-date').innerText = text;
}

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

calendar.on('selectDateTime', function(ev) {
    openTaskModal(ev.start, ev.end);
});

function setView(view) {
    calendar.changeView(view);
    updateDateLabel();
}

function showConfirmModal(title, message) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        
        modal.innerHTML = `
            <div class="confirm-modal">
                <i class='bx bx-error-circle' style="font-size: 3rem; color: #ef4444;"></i>
                <h3>${title}</h3>
                <p>${message}</p>
                <div class="confirm-actions">
                    <button class="btn btn-secondary" id="cancelBtn">Anuluj</button>
                    <button class="btn btn-danger" id="confirmBtn">Usuń mimo to</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector('#cancelBtn').onclick = () => {
            modal.remove();
            resolve(false);
        };

        modal.querySelector('#confirmBtn').onclick = () => {
            modal.remove();
            resolve(true);
        };
    });
}

async function loadEvents() {
    const res = await fetch('events.php');
    const data = await res.json();

    calendar.clear();

    const formattedEvents = data.map(e => {
        const startDate = new Date(e.start);
        const endDate = new Date(e.end);

        const isMultiDay = startDate.toDateString() !== endDate.toDateString();
        
        const isAllday = e.isAllday === true || e.isAllday === "1" || isMultiDay;

        return {
            id: e.id,
            calendarId: '1',
            title: e.title,
            start: e.start,
            end: e.end,
            category: isAllday ? 'allday' : 'time',
            isAllday: isAllday,
            backgroundColor: e.backgroundColor
        };
    });

    calendar.createEvents(formattedEvents);
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

function openTaskModal(start, end, existingEvent = null) {
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

    modal.querySelector('#close').onclick = () => modal.remove();

    if (existingEvent) {
        modal.querySelector('h3').innerText = 'Edytuj zadanie';
        modal.querySelector('#title').value = existingEvent.title;
        modal.querySelector('#color').value =existingEvent.backgroundColor;
    }

    modal.querySelector('#save').onclick = async () => {
        const task = {
        id: existingEvent ? existingEvent.id : null,
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

        const url = existingEvent ? 'update_event.php' : 'add_event.php';

        await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(task)
        });

        calendar.clearGridSelections();

        modal.remove();
        loadEvents();
    };

}

updateDateLabel();
loadEvents();

</script>