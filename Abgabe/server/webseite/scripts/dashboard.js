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
    user = data.json()
    console.log(user);
    
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
    console.log("kommen");
    post('index.php', 'aktion', 'Kommen')

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
}

function button_gehen_even() {
    console.log("gehen");
    post('index.php', 'aktion', 'Gehen')
}
