let user

async function get_user() {
    fetch("https://zeitbuchung.it-lutz.com/api.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:{
            "function":"get_current_user"
        }
    })
    .then(respons => {
        return respons.text()
    })
    .then(data => {
        console.log(data);
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
        body:{
            function:"user_kommen",

        }
    })
}
document.getElementById("btn_kommen").addEventListener("click",button_kommen_even)

function button_gehen_even() {
    console.log("gehen");
    post('index.php', 'aktion', 'Gehen')
}
document.getElementById("btn_gehen").addEventListener("click",button_gehen_even)

get_user()