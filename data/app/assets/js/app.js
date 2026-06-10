let currentEmployeeId = null;
let lock = false;

function openModal(id, name) {
    currentEmployeeId = id;
    document.getElementById("modalName").innerText = name;
    document.getElementById("modal").style.display = "flex";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

function book(action) {
    if (lock) return;
    lock = true;

    fetch("/lampboard/book.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "employee_id=" + currentEmployeeId + "&action=" + action
    })
    .then(res => res.json())
    .then(() => {
        closeModal();
        loadLampboard();
    })
    .finally(() => {
        setTimeout(() => lock = false, 800);
    });
}

/* LIVE CLOCK */
function updateClock() {
    const now = new Date();
    document.getElementById("clock").innerText =
        now.toLocaleDateString() + " " + now.toLocaleTimeString();
}

setInterval(updateClock, 1000);
updateClock();

/* AUTO REFRESH LAMPBOARD */
function loadLampboard() {

    let url = "/lampboard/status.php";

    if (typeof LANG_CODE !== "undefined" && LANG_CODE) {
        url += "?lang=" + encodeURIComponent(LANG_CODE);
    }

    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById("lampboard").innerHTML = html;
        });
}

setInterval(loadLampboard, 5000);
