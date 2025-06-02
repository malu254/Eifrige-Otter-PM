let user

async function get_user() {
  const response = await fetch("https://zeitbuchung.it-lutz.com/api.php",{
    method:"POST",
    headers:{
        "Content-Type":"application/json"
    },
    body:{
        function:"get_current_user"
    }
  });
  if (!response.ok) {
    console.error('Fehler beim Laden der API:', response.status);
    return;
  }

  user = await response.json();
  console.log('User geladen:', user);

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