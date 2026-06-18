<?php
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/lang.php";

$lang = loadLang();
requireAdmin();

$employees = $pdo->query("SELECT * FROM employees ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= $lang["calendar"] ?? "Calendar" ?></title>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<link rel="stylesheet" href="/assets/css/style.css">

<style>
#calendar {
    max-width: 1200px;
    margin: 20px auto;
    background: white;
    padding: 10px;
    border-radius: 10px;
}

.employee-container {
    max-width: 400px;
    margin: 20px auto;
}

#editModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

#editModal .box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 320px;
}

#editModal button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
}
</style>
</head>

<body>

<div class="header"><?= $lang["calendar"] ?? "Calendar" ?></div>

<div class="employee-container">
    <select id="employee" style="width:100%;">
        <option value=""><?= $lang["select_employee"] ?? "-- Select Employee --" ?></option>
        <?php foreach($employees as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div id="calendar"></div>

<!-- EDIT MODAL -->
<div id="editModal">
    <div class="box">
        <h3><?= $lang["update"] ?? "Edit Entry" ?></h3>

        <input type="datetime-local" id="editTime" style="width:100%;padding:10px;margin-bottom:10px;">

        <select id="editAction" style="width:100%;padding:10px;margin-bottom:10px;">
            <option value="come"><?= $lang["come"] ?? "COME" ?></option>
            <option value="go"><?= $lang["go"] ?? "GO" ?></option>
        </select>

        <button class="btn-come" onclick="saveEdit()">
            <?= $lang["update"] ?? "Save" ?>
        </button>

        <button class="btn-go" onclick="deleteEntry()">
            <?= $lang["delete"] ?? "Delete" ?>
        </button>

        <button class="btn-cancel" onclick="closeEdit()">
            <?= $lang["cancel"] ?? "Cancel" ?>
        </button>
    </div>
</div>

<script>
const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

let calendar;
let selectedEventId = null;

function loadEvents(employeeId) {
    if(!employeeId) return;

    fetch("/admin/calendar_events.php?employee_id=" + employeeId)
        .then(r => r.json())
        .then(events => {
            calendar.removeAllEvents();
            calendar.addEventSource(events);
        });
}

function openEdit(info) {
    selectedEventId = info.event.id;

    const date = new Date(info.event.start);
    const tzOffset = date.getTimezoneOffset() * 60000;
    const localISO = new Date(date - tzOffset).toISOString().slice(0,16);

    document.getElementById("editTime").value = localISO;
    document.getElementById("editAction").value = info.event.extendedProps.action;

    document.getElementById("editModal").style.display = "flex";
}

function closeEdit() {
    document.getElementById("editModal").style.display = "none";
}

function saveEdit() {
    const empId = document.getElementById("employee").value;

    const data = new URLSearchParams();
    data.append("id", selectedEventId);
    data.append("action", document.getElementById("editAction").value);
    data.append("time", document.getElementById("editTime").value.replace("T"," "));

    fetch("/admin/update_entry.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: data.toString()
    })
    .then(r => r.json())
    .then(() => {
        closeEdit();
        loadEvents(empId);
    });
}

function deleteEntry() {
    if (!confirm("<?= $lang["delete_entry"] ?? "Delete entry?" ?>")) {
        return;
    }

    const empId = document.getElementById("employee").value;

    const data = new URLSearchParams();
    data.append("id", selectedEventId);

    fetch("/admin/delete_entry.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: data.toString()
    })
    .then(r => r.json())
    .then(() => {
        closeEdit();
        loadEvents(empId);
    });
}

document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        eventClick: function(info) {
            openEdit(info);
        }
    });

    calendar.render();

    const emp = document.getElementById("employee");

    if(emp.value) {
        loadEvents(emp.value);
    }

    emp.addEventListener("change", function () {
        loadEvents(this.value);
    });
});
</script>

</body>
</html>
