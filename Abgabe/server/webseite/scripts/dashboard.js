const url = "http://localhost/api.php"
let user

async function get_user() {
    let respons = await fetch(url,{
        method:"POST",
        credentials:"include",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            "function":"get_current_user"
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

document.getElementById("btn_gehen").addEventListener("click",button_gehen_even)
document.getElementById("btn_kommen").addEventListener("click",button_kommen_even)
document.getElementById("notification-btn").addEventListener("click",load_notifications)

document.getElementById("submitNewPassword").addEventListener("submit", function(e) {
    e.preventDefault(); // verhindert das Absenden des Formulars
    alert("Das ist ein Test"); // zeigt die Alertbox an
});

const langEn = document.getElementById("langEn");
if (langEn) {
    langEn.addEventListener("click", () => language_change("en"));
}

const langDe = document.getElementById("langDe");
if (langDe) {
    langDe.addEventListener("click", () => language_change("de"));
}
}



function button_kommen_even() {
    fetch(url,{
        method:"POST",
        headers: {
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            "function":"user_kommen",
            "user_id":user.id
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
    fetch(url,{
        method:"POST",
        headers: {
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            "function":"user_gehen",
            "user_id":user.id
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

function load_notifications() {
    const note_body = document.getElementById("notification-body")

    fetch(url,{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            "function":"get_notifications",
            "user_id":user.id
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
    fetch(url,{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify({
            "function":"get_times",
            "user_id":user.id
        })
    })
    .then(respons => {
        if (!respons.ok) throw new Error("respons error")
        return respons.json()
    })
    .then(data => {
        console.log(data);
        location.reload(); // quickfix
    })
}

function language_change(lang) {
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            "function": "change_lang",
            "user_id": user.id,
            "lang": lang
        })
    })
    .then(response => {
        if (!response.ok) throw new Error("failed to load language");
        return response.json();
    })
    .then(data => {
        console.log(data);
        setTimeout(() => {
            location.reload();
        }); 
    });
}