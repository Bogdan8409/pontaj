<html>

<head>
  <title>Formular Pontaj</title>
</head>

<body>
  
  <h1>Formular Pontaj</h1>
  <form action="submit_pontaj.php" method="post" id="myForm" enctype="multipart/form-data">
    <label for="luna_de_facturat">Luna de facturat (ultima zi a lunii):</label><br>
    <input type="date" id="luna_de_facturat" name="luna_de_facturat" placeholder="M/d/yyyy" required><br><br>

    <label for="nume_prenume">Nume si Prenume Cu Majuscule:</label><br>
    <input type="text" id="nume_prenume" name="nume_prenume" required><br><br>

    <label for="numar_zile_rezervate">Numar de zile rezervate (punct pentru zecimale):</label><br>
    <input type="number" id="numar_zile_rezervate" name="numar_zile_rezervate" step="0.01" min="0.01" required><br><br>

    <label for="numar_zile_facturate">Numar de zile facturate (punct pentru zecimale):</label><br>
    <input type="number" id="numar_zile_facturate" name="numar_zile_facturate" step="0.01" min="0.01" required><br><br>

    <label for="nume_client">Nume Client:</label><br>
    <select id="nume_client" name="nume_client" required>
      <option value="" disabled selected>Selectați un client</option>
      <?php echo $options; ?>
    </select><br><br>

    <label for="serie_factura">Serie Factura:</label><br>
    <input type="text" id="serie_factura" name="serie_factura" placeholder="Exemplu: INV, FACT" required><br><br>

    <label for="numar_factura">Numar Factura:</label><br>
    <input type="number" id="numar_factura" name="numar_factura" required><br><br>

    <label for="incarcare_timesheet">Incarcare Timesheet:</label><br>
    <input type="file" id="incarcare_timesheet" name="incarcare_timesheet" accept="application/pdf" required><br><br>

    <input type="submit" value="Trimite">
  </form>

  <div id="uploadProgress" style="display:none;">
    <progress id="progressBar" value="0" max="100"></progress>
    <span id="progressText">0%</span>
  </div>

  <div id="successMessage" style="display:none; color:green; font-weight:bold;">
    Formular trimis cu succes ✔
  </div>

  <script>
    document.getElementById('luna_de_facturat').addEventListener('change', function () {
      const selectedDate = new Date(this.value);

      // ultima zi a lunii selectate
      const lastDay = new Date(
        selectedDate.getFullYear(),
        selectedDate.getMonth() + 1,
        0
      ).getDate();

      if (selectedDate.getDate() !== lastDay) {
        alert('Data trebuie să fie ultima zi a lunii.');
        this.value = '';
      }
    });



    function validateTwoDecimals(value) {
      value = value.trim();

      const number = Number(value);
      if (isNaN(number) || number <= 0) return false;

      const decimalPart = value.split('.')[1];
      if (decimalPart && decimalPart.length > 2) return false;

      return true;
    }

    function TwoDecimals(value) {
      value = value.trim();

      const number = Number(value);
      if (isNaN(number) || number <= 0) return false;
    }


    document.getElementById("myForm").addEventListener("submit", function (e) {

      const nameInput = document.getElementById("nume_prenume");


      nameInput.value = capitalize(nameInput.value);

      if (!validateName(nameInput.value)) {
        e.preventDefault();
        alert("Introduceți minim 2 cuvinte, doar litere și spații.");
        return;
      }
      const inputNume = document.getElementById("nume_prenume");

      inputNume.addEventListener("blur", function () {
        this.value = capitalize(this.value);
      });
      function capitalize(str) {
        return str
          .trim()
          .split(/\s+/)
          .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
          .join(" ");
      }
      function validateName(name) {
        // eliminăm spațiile multiple
        const words = name.trim().split(/\s+/);


        if (words.length < 2) return false;


        for (let word of words) {
          if (!/^[A-Za-zĂÂÎȘȚăâîșț]+$/.test(word)) {
            return false;
          }
        }

        return true;
      }


      const maximZile = Number(document.getElementById("numar_zile_facturate").value);
      if (zileValue > maximZile) {
        e.preventDefault();
        alert(`Numărul introdus nu poate depăși ${maximZile} zile rezervate.`);
        return;
      }

      const codInput = document.getElementById("serie_factura");
      if (codInput.value.length > 10) {
        e.preventDefault(); // oprește trimiterea formularului
        alert("Codul nu poate avea mai mult de 10 caractere.");
        return;
      }
      const number = document.getElementById("numar_factura");
      if (!TwoDecimals(number.value)) {
        e.preventDefault();
        alert("Introduceți un număr pozitiv.");
        return;
      }

      const fileInput = document.getElementById("incarcare_timesheet");
      const file = fileInput.files[0]; // doar primul fișier

      if (!file) {
        e.preventDefault();
        alert("Trebuie să selectați un fișier.");
        return;
      }


      if (file.type !== "application/pdf") {
        e.preventDefault();
        alert("Doar fișiere PDF sunt acceptate.");
        return;
      }


      const maxSize = 10 * 1024 * 1024; // 10MB în bytes
      if (file.size > maxSize) {
        e.preventDefault();
        alert("Fișierul nu poate depăși 10MB.");
        return;
      }


      const fileName = file.name;
      const regex = /^(0[1-9]|1[0-2])_\d{4}\.pdf$/i; // ex: 06_2020.pdf
      if (!regex.test(fileName)) {
        e.preventDefault();
        alert("Numele fișierului trebuie să respecte formatul MM_YYYY.pdf (ex: 06_2020.pdf).");
        return;
      }


    });
    // =====================
    // AUTO-SAVE (localStorage)
    // =====================
    const form = document.getElementById("myForm");
    const fields = form.querySelectorAll("input, select");

    fields.forEach(field => {
      const saved = localStorage.getItem(field.name);
      if (saved) field.value = saved;

      field.addEventListener("input", () => {
        localStorage.setItem(field.name, field.value);
      });
    });

    // =====================
    // CONFIRMARE + DOUBLE SUBMIT + UPLOAD PROGRESS
    // =====================
    let alreadySubmitted = false;

    form.addEventListener("submit", function (e) {
      if (alreadySubmitted) {
        e.preventDefault();
        return;
      }

      if (!confirm("Sigur doriți să trimiteți formularul?")) {
        e.preventDefault();
        return;
      }

      e.preventDefault(); // prevenim submitul normal
      alreadySubmitted = true;

      const submitBtn = form.querySelector('input[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.value = "Se trimite...";

      const progressBox = document.getElementById("uploadProgress");
      const progressBar = document.getElementById("progressBar");
      const progressText = document.getElementById("progressText");

      progressBox.style.display = "block";

      const xhr = new XMLHttpRequest();
      const formData = new FormData(form);

      xhr.upload.addEventListener("progress", function (e) {
        if (e.lengthComputable) {
          const percent = Math.round((e.loaded / e.total) * 100);
          progressBar.value = percent;
          progressText.textContent = percent + "%";
        }
      });

      xhr.onload = function () {
        if (xhr.status === 200) {
          document.getElementById("successMessage").style.display = "block";
          localStorage.clear();
          form.reset();
        } else {
          alert("Eroare la trimitere.");
          submitBtn.disabled = false;
          submitBtn.value = "Trimite";
          alreadySubmitted = false;
        }
      };

      xhr.open("POST", form.action);
      xhr.send(formData);
    });



  </script>
</body>

</html>