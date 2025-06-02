let user

async function get_user() {
    let respons = await fetch("https://zeitbuchung.it-lutz.com/api.php",{
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
}

function button_kommen_even() {
    fetch("https://zeitbuchung.it-lutz.com/api.php",{
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
    fetch("https://zeitbuchung.it-lutz.com/api.php",{
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

function get_times() {
    fetch("https://zeitbuchung.it-lutz.com/api.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify({
            "function":"get_times",
            "user_id":user.id
        })
        .then(respons => {
            if (!respons.ok) throw new Error("respons error")
            return respons.json()
        })
        .then(data => {
            console.log(data);
            
        })
    })
}
