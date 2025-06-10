const url = "https://zeitbuchung.it-lutz.com/api.php"
//const url = "http://localhost/api.php"
let user

async function get_user() {
    let respons = await fetch(url, {
        method: "POST",
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "get_current_user"
        })
    })

    console.log(await respons.text());
    

    if (!respons.ok) throw new Error("respons error")
    let data = await respons.json()
    user = data.user

}

window.addEventListener("DOMContentLoaded", async () => {
    try {
        await get_user(); // wartet auf Abschluss
        main()
    } catch (error) {
        console.error("Fehler beim Laden des Benutzers:", error);
    }
});

function main() {

    document.getElementById("btn_gehen").addEventListener("click", button_gehen_even)
    document.getElementById("btn_kommen").addEventListener("click", button_kommen_even)
    document.getElementById("notification-btn").addEventListener("click", load_notifications)
    document.getElementById("submitNewPassword").addEventListener("click", button_new_password)
}



function button_kommen_even() {
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "user_kommen",
            "user_id": user.id
        })
    })
        .then(respons => {
            if (!respons.ok) throw new Error("failed to kommen")
            return respons.json()
        })
        .then(data => {
            console.log(data);
        })
    get_times()
}

function button_gehen_even() {
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "user_gehen",
            "user_id": user.id
        })
    })
        .then(respons => {
            if (!respons.ok) throw new Error("failed to kommen")
            return respons.json()
        })
        .then(data => {
            console.log(data);
        })
    get_times()
}

function button_new_password() {
    const pw_input = document.getElementById("newPasswordInput")
    const new_pw = pw_input.value

    if (new_pw.length < 4 || new_pw.length > 35) {
        password_alert("password_error", "danger")
        return
    }

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "change_password",
            "user_id": user.id,
            "new_password": new_pw
        })
    })
        .then(result => {
            if (!result.ok) throw new Error("result Error")
            pw_input.value = ""
        password_alert("password_success","success")
            return result.json()
        })
        .then(data => { console.log(data); })
}


function password_alert(message, type) {
    const alertPlaceholder = document.getElementById("alert-placeholder");

    const appendAlert = (message, type) => {
        const wrapper = document.createElement('div');
        const translatedMessage = translations[message] || message;

        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <div>${translatedMessage}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('');

        alertPlaceholder.append(wrapper);
    }

    appendAlert(message, type);
}

function load_notifications() {
    const note_body = document.getElementById("notification-body");
    note_body.innerHTML = ""; // Vorherige Inhalte entfernen

    if (!user?.id) {
        console.error("Benutzer-ID fehlt – kann keine Benachrichtigungen laden.");
        return;
    }

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "get_notifications",
            "user_id": user.id
        })
    })
    .then(response => {
        if (!response.ok) throw new Error("result error");
        return response.json();
    })
    .then(data => {
        console.log("Antwort von get_notifications:", data);

        if (!Array.isArray(data.notifications)) {
            console.warn("Keine gültigen Benachrichtigungen erhalten.");
            return;
        }

        data.notifications.forEach(notification => {
            const wrapper = document.createElement("div");
            wrapper.className = "d-flex justify-content-between align-items-center border-bottom py-2";

            // kommentare für mich zum besseren erständis

            // container nachricht
            const notificationItem = document.createElement("div");
            notificationItem.className = "d-flex flex-column";

            // zeit / buttons
            const timeRow = document.createElement("div");
            timeRow.className = "d-flex justify-content-between align-items-start";

            // zeit element
            const time = document.createElement("time");
            time.className = "text-muted";
            time.style.fontSize = "smaller";
            time.innerText = notification.erzeugt_am;

            // nachticht
            const message = document.createElement("p");
            message.className = "mb-0 mt-1";
            const translatedText = (translations[notification.text] || notification.text);
            message.innerText = translatedText;

            // gesehen button
            const seenButton = document.createElement("button");
            seenButton.className = "btn btn-sm btn-outline-secondary";
            seenButton.title = "Als gesehen markieren";
            seenButton.innerHTML = '<i class="bi bi-eye"></i>';

            // wenn gesehen, dann nich anzeigen
            if (notification.gesehen == 1) {
                seenButton.disabled = true;
                wrapper.classList.add("text-muted");
                message.classList.add("text-muted");
            } else {
                seenButton.addEventListener("click", () => {
                    mark_as_seen(notification.id, wrapper);
                });
            }

            // alles drawen
            timeRow.appendChild(time);
            notificationItem.appendChild(timeRow);
            notificationItem.appendChild(message);
            
            wrapper.appendChild(notificationItem);
            wrapper.appendChild(seenButton);
            
            note_body.appendChild(wrapper);
        });

    })
    .catch(error => {
        console.error("Fehler beim Laden der Benachrichtigungen:", error);
    });
}




function get_times() {
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "get_times",
            "user_id": user.id
        })
    })
        .then(respons => {
            if (!respons.ok) throw new Error("respons error")
            return respons.json()
        })
        .then(data => {
            console.log(data);
            location.reload();
        })
}


function mark_as_seen(notificationId, wrapperElement) {
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "mark_as_seen",
            "notification_id": notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Antwort auf mark_as_seen:", data);

        if (data.success) {
            wrapperElement.classList.add("text-muted");
            const button = wrapperElement.querySelector("button");
            if (button) button.disabled = true;
        } else {
            console.error("Fehler beim Aktualisieren:", data.error);
        }
    })
    .catch(error => {
        console.error("Fehler bei mark_as_seen:", error);
    });
}
