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
            // 1. Zabezpieczony wygląd eventów na siatce z ikonkami priorytetów
            allday: function(event) {
                const start = new Date(event.start);
                const hours = String(start.getHours()).padStart(2, '0');
                const minutes = String(start.getMinutes()).padStart(2, '0');

                const priority = (event.raw && event.raw.priority) ? event.raw.priority : '';

                let priorityIcon = '';
                if (priority === 'high') priorityIcon = '<i class="bx bxs-flame" style="color: #ff9603ff;"></i>';
                if (priority === 'medium') priorityIcon = '<i class="bx bx-radio-circle-marked" style="color: #ffd166;"></i>';
                if (priority === 'low') priorityIcon = '<i class="bx bx-down-arrow-circle" style="color: #06d6a0;"></i>';

                return `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 0 4px; box-sizing: border-box; color: #ffffff; text-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="font-weight: 700; font-size: 14px; margin-right: 4px; background: rgba(0,0,0,0.15); padding: 1px 4px; border-radius: 3px;">
                            ${hours}:${minutes}
                        </span>
                        <span style="font-size: 15px;">${event.title}</span>
                    </div>
                    <div style="font-size: 18px;">
                        ${priorityIcon}
                    </div>
                </div>
            `;
            },

            time: function(event) {
                const start = new Date(event.start);
                const hours = String(start.getHours()).padStart(2, '0');
                const minutes = String(start.getMinutes()).padStart(2, '0');

                const priority = (event.raw && event.raw.priority) ? event.raw.priority : '';

                let priorityIcon = '';
                if (priority === 'high') priorityIcon = '<i class="bx bxs-flame" style="color: #ff9603ff;"></i>';
                if (priority === 'medium') priorityIcon = '<i class="bx bx-radio-circle-marked" style="color: #ffd166;"></i>';
                if (priority === 'low') priorityIcon = '<i class="bx bx-down-arrow-circle" style="color: #06d6a0;"></i>';

                return `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 0 4px; box-sizing: border-box; color: #000000ff; text-shadow: 0px 1px 2px rgba(0,0,0,0.2);">
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="font-weight: 700; font-size: 14px; margin-right: 4px; background: rgba(150, 150, 150, 0.15); padding: 3px 4px; border-radius: 3px;">
                            ${hours}:${minutes}
                        </span>
                        <span style="font-size: 15px;">${event.title}</span>
                    </div>
                    <div style="font-size: 18px;">
                        ${priorityIcon}
                    </div>
                </div>
            `;
            },

            monthGridHeaderExceed: function(event) {
                return `+${event.count}`;
            },

            // 2. Czyścimy zbędne elementy domyślnego popupa
            popupDetailLocation: () => '',
            popupDetailAttendees: () => '',
            popupDetailState: () => '',

            // 3. Ładna data w popupie + Status i Priorytet (zawsze widoczne, nawet bez opisu)
            popupDetailDate: function(event) {
                const data = event.raw || {};
                const rawStatus = (data.status || '');
                const rawPriority = (data.priority || '');

                const statusLabels = {
                    'todo': 'Do zrobienia',
                    'in_progress': 'W trakcie',
                    'completed': 'Zrobione'
                };
                const priorityLabels = {
                    'low': 'Niski',
                    'medium': 'Średni',
                    'high': 'Wysoki'
                };

                const statusText = statusLabels[rawStatus] || data.status || 'Brak statusu';
                const priorityText = priorityLabels[rawPriority] || data.priority || 'Brak priorytetu';

                const dateHtml = `<div style="margin-bottom: 8px;"><i class='bx bx-calendar'></i> ${formatDateLocal(new Date(event.start))} - ${formatDateLocal(new Date(event.end))}</div>`;

                const tagsHtml = `
                    <div style="display: flex; gap: 8px; margin-top: 8px; margin-bottom: 4px;">
                        <div style="background: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #374151;">
                            Status: <b>${statusText}</b>
                        </div>
                        <div style="background: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #374151;">
                            Priorytet: <b>${priorityText}</b>
                        </div>
                    </div>
                `;

                return dateHtml + tagsHtml;
            },

            // 4. Naprawione Body popupa (tylko opis)
            popupDetailBody: function(event) {
                if (!event.body || event.body.trim() === '') {
                    return '';
                }

                return `
                <div style="padding: 10px 0; margin-top: 3.5em;">
                    <div style="margin-bottom: 12px;">
                        <strong style="color: #41464fff; font-size: 12px; text-transform: uppercase;">OPIS</strong><br>
                        <div style="margin-top: 4px; color: #1f2937; white-space: pre-wrap;">${event.body}</div>
                    </div>
                </div>
            `;
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
        const {
            id,
            calendarId
        } = eventObj;

        const confirmed = await showConfirmModal(
            'Usuwanie zadania',
            'Czy na pewno chcesz bezpowrotnie usunąć to zadanie? Tej operacji nie da się cofnąć.'
        );

        if (confirmed) {
            try {
                const res = await fetch('delete_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
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
        const {
            event,
            changes
        } = updateData;

        if (changes && Object.keys(changes).length > 0) {
            let newStart = changes.start || event.start;
            let newEnd = changes.end || event.end;

            const updatedTask = {
                id: event.id,
                title: changes.title || event.title,
                description: event.body || '',
                status: (event.raw && event.raw.status) ? event.raw.status : 'todo',
                priority: (event.raw && event.raw.priority) ? event.raw.priority : 'medium',
                color: changes.backgroundColor || event.backgroundColor,
                start: formatDateLocal(new Date(newStart)),
                end: formatDateLocal(new Date(newEnd))
            };

            fetch('update_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updatedTask)
                })
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'ok') {
                        calendar.updateEvent(event.id, event.calendarId, changes);
                    } else {
                        alert('Błąd: ' + result.message);
                        loadEvents();
                    }
                })
                .catch(error => {
                    console.error('Błąd podczas aktualizacji:', error);
                    loadEvents();
                });
        } else {
            openTaskModal(event.start, event.end, event);
        }
    });

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
            const day = start.getDay() === 0 ? 7 : start.getDay(); // Poniedziałek to 1, Niedziela to 7
            start.setDate(start.getDate() - day + 1);

            const end = new Date(start);
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

    function showInfoModal(title, message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';

            modal.innerHTML = `
            <div class="confirm-modal">
                <i class='bx bx-error-circle' style="font-size: 3rem; color: #f41010ff;"></i>
                <h3>${title}</h3>
                <p>${message}</p>
                <div class="confirm-actions">
                    <button class="btn btn-ok" id="confirmBtn">Rozumiem</button>
                </div>
            </div>
        `;

            document.body.appendChild(modal);

            modal.querySelector('#confirmBtn').onclick = () => {
                modal.remove();
                resolve(true);
            };
        });
    }

    async function loadEvents() {
        const res = await fetch('events.php');
        const data = await res.json();

        if (data && data.status === 'error') {
            console.error('Błąd pobierania zadań:', data.message);
            return;
        }

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
                body: e.description || '',
                start: e.start,
                end: e.end,
                category: isAllday ? 'allday' : 'time',
                isAllday: isAllday,
                raw: {
                    status: (e.status || 'todo').toString().trim().toLowerCase(),
                    priority: (e.priority || 'medium').toString().trim().toLowerCase()
                },
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

        let displayStart = new Date(start);
        let displayEnd = new Date(end);

        if (displayEnd.getHours() === 0 && displayEnd.getMinutes() === 0) {
            displayEnd.setHours(23, 59, 0);
        }

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

        modal.querySelector('#start').value = formatForInput(displayStart);
        modal.querySelector('#end').value = formatForInput(displayEnd);

        modal.querySelector('#close').onclick = () => modal.remove();

        if (existingEvent) {
            modal.querySelector('h3').innerText = 'Edytuj zadanie';
            modal.querySelector('#title').value = existingEvent.title;
            modal.querySelector('#desc').value = existingEvent.body || '';
            modal.querySelector('#color').value = existingEvent.backgroundColor;
            if (existingEvent.raw) {
                if (existingEvent.raw.status) modal.querySelector('#status').value = existingEvent.raw.status;
                if (existingEvent.raw.priority) modal.querySelector('#priority').value = existingEvent.raw.priority;
            }
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
                await showInfoModal("Błąd", "Podaj nazwę zadania");
                return;
            }

            const url = existingEvent ? 'update_event.php' : 'add_event.php';

            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
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