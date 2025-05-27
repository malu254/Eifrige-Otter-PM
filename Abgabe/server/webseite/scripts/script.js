function post(destination, action, value) {
    fetch(destination, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: action + '=' + encodeURIComponent(value)
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        }
    })
}

document.addEventListener("DOMContentLoaded", function() {
    const datumInput = document.getElementById('datumInput');
  const form = document.getElementById('datumForm');
  const vorherBtn = document.getElementById('vorheriges');
  const naechstesBtn = document.getElementById('naechstes');

  function verschiebeDatum(tage) {
    const aktuellesDatum = new Date(datumInput.value);
    aktuellesDatum.setDate(aktuellesDatum.getDate() + tage);
    datumInput.value = aktuellesDatum.toISOString().split('T')[0];
    form.submit();
  }

  vorherBtn.addEventListener('click', () => verschiebeDatum(-1));
  naechstesBtn.addEventListener('click', () => verschiebeDatum(1));

  datumInput.addEventListener('change', function () {
    form.submit();
  });

  const notificationModal = document.getElementById("exampleModal");

  // Benachrichtigungen laden bei Modal-Öffnung
  if (notificationModal) {
    notificationModal.addEventListener("shown.bs.modal", function () {
      getnotification('load');
    });
  }

  // Funktion zum Abrufen der Benachrichtigungen
  function getnotification(action) {
    console.log("Lade Benachrichtigungen...");

    fetch('index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'get-notification=' + encodeURIComponent(action)
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Netzwerkfehler beim Abrufen der Benachrichtigungen');
      }
      return response.json();
    })
    .then(data => {
      console.log(data); // Zum Debuggen
      const notificationBody = document.getElementById("notification-body");
      notificationBody.innerHTML = ""; // Optional: vorher leeren
      data.forEach(entry => {
        notificationBody.innerHTML += `<p>${entry.text}</p>`;
      });
    })
    .catch(error => {
      console.error("Fehler beim Laden der Benachrichtigungen:", error);
    });
  }
});
