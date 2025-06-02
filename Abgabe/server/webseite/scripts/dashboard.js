let user

async function get_user() {
    fetch("https://zeitbuchung.it-lutz.com/api.php",{
        method:"POST",
        credentials:"include",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            "function":"get_current_user"
        })
    })
    .then(respons => {
        if (!respons.ok) throw new Error("respons error")
        return respons.json()
    })
    .then(data => {
        user = data
    })
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
            "function":"user_kommen"
        })
    })
}
document.getElementById("btn_kommen").addEventListener("click",button_kommen_even)

function button_gehen_even() {
    console.log("gehen");
    post('index.php', 'aktion', 'Gehen')
}
document.getElementById("btn_gehen").addEventListener("click",button_gehen_even)

get_user()
console.log(user);
