<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<div id="calendar"></div>
<div id="eventModal" class="modal">
  <div class="modal-content">
    <h3>Nowe zadanie</h3>

    <input type="text" id="eventTitle" placeholder="Nazwa zadania">
    <br/>
    <br/>
    <h3>Opis zadania (nieobowiązkowy)</h3>
    <textarea id="eventDescription" placeholder="Dodaj opis zadania"></textarea>

    <div class="modal-actions">
      <button id="saveEvent">Zapisz</button>
      <button id="closeModal">Anuluj</button>
    </div>
  </div>
</div>

<script>
    let calendar;
    document.addEventListener('DOMContentLoaded', function() {
        calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        buttonText: {
            today: 'Dziś',
            month: 'Miesiąc',
            week: 'Tydzień',
            day: 'Dzień'
        },

        events: 'events.php', 

        /* kliknięcie w kwadracik z datą
        dateClick: function(info) {
        let title = prompt("Nazwa zadania:");
        if (title) {
            fetch('add_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: title,
                date: info.dateStr
            })
            }).then(() => calendar.refetchEvents());
        }
        },*/

        eventContent: function(arg) {
        return {
            html: `
            <div style="padding:2px 4px;">
                <div style="font-size:11px; opacity:0.8;">
                ${arg.timeText || ''}
                </div>
                <div style="font-weight:600;">
                ${arg.event.title}
                </div>
            </div>
            `
        };
        }, 

        selectable: true, 
        dateClick: function(info) {
            openModal(info.dateStr);
        },


        // kliknięcie w plusik
        dayCellDidMount: function(info) {
            const plus = document.createElement("div");
            plus.innerHTML = "+";
            plus.className = "add-event-btn";

            plus.onclick = function(e) {
                e.stopPropagation();
                openModal(info.dateStr);
            };

            info.el.appendChild(plus);
            }
    });

  calendar.render();
  
});

let selectedDate = null;

const modal = document.getElementById("eventModal");
const input = document.getElementById("eventTitle");
const textarea = document.getElementById("eventDescription");

function openModal(date) {
    selectedDate = date;
    modal.classList.add("active");
    input.value = "";
    input.focus();
}

function closeModal() {
    modal.classList.remove("active");
}

document.getElementById("closeModal").onclick = closeModal;

document.getElementById("saveEvent").onclick = function() {
    const title = input.value;
    const description = textarea.value;

    if (!title) return;

    fetch('add_event.php', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json'
        },
        body: JSON.stringify({
        title: title,
        description: description,
        date: selectedDate
        })
    })
    .then(res => res.text())
    .then(data => {
        console.log(data);
        closeModal();
        calendar.refetchEvents();
    })
    .catch(err => console.error(err));
};

</script>