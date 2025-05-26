function setLanguage(lang) {
    fetch('../../uebersicht/index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'sprache=' + encodeURIComponent(lang)
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        }
    })
}


function kommenGehen(aktion) {
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'aktion=' + encodeURIComponent(aktion)
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        }
    })
}