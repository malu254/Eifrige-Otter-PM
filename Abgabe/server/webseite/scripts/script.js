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

  vorherBtn.addEventListener('click', () => verschiebeDatum(-5));
  naechstesBtn.addEventListener('click', () => verschiebeDatum(5));

  datumInput.addEventListener('change', function () {
    form.submit();
  });
});