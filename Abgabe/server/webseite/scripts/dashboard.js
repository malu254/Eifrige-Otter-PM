const url = "https://zeitbuchung.it-lutz.com/api.php"
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
    const note_body = document.getElementById("notification-body")

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
        .then(respons => {
            if (!respons.ok) throw new Error("result error")

            return respons.json()
        })
        .then(data => {
            data.notifications.forEach(element => {
                const new_p = document.createElement("p")
                new_p.innerText = element.text
                note_body.appendChild(new_p)
            });
        })
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

