
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luna de Facturat - Date Picker</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header-section {
            background: #041945;
            color: white;
            padding: 30px 40px;
            margin-bottom: 30px;
        }

        .section-title {
            color: #041945;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #041945;
        }

        .form-section {
            padding: 30px;
            border-bottom: 1px solid #eee;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-left: 5px solid #041945;
            border-radius: 8px;
        }

        .input-group-custom .input-group-text {
            background-color: #041945;
            color: white;
            border-color: #041945;
        }

        input.form-control:focus {
            border-color: #041945;
            box-shadow: 0 0 0 0.25rem rgba(4, 25, 69, 0.25);
        }

        .btn-custom {
            background: #041945;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-custom:hover {
            background: #00bcd4;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 188, 212, 0.4);
        }

        .btn-custom:disabled {
            background: #6c757d;
            transform: none;
            box-shadow: none;
        }

        .result-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border-left: 4px solid #041945;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-box {
            border-radius: 8px;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        .form-label {
            font-weight: 600;
            color: #041945;
            margin-bottom: 8px;
        }

        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .char-count {
            font-size: 0.75rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-9">
                <div class="form-container">
                    <!-- Header -->
                    <div class="header-section">
                        <h1 class="display-6 mb-3">Formular Pontaj și Facturare</h1>
                        <p class="lead mb-0">Completează datele necesare pentru procesul de facturare și pontaj lunar
                        </p>
                    </div>

                    <div class="p-4">
                        <!-- Formular Luna de Facturat -->
                        <div class="form-section">
                            <h3 class="section-title">1. Luna de Facturat</h3>

                            <div class="info-box p-3 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                                    <div>
                                        <p class="mb-1"><strong>Format:</strong> M/d/yyyy (exemplu: 1/31/2025)</p>
                                        <p class="mb-0"><strong>Validare:</strong> Data trebuie să fie ultima zi a unei
                                            luni</p>
                                    </div>
                                </div>
                            </div>

                            <form id="billingForm" method="post" enctype="multipart/form-data"
                                action="salveaza_pontaj.php">
                                <div class="mb-4">
                                    <label for="billingMonth" class="form-label">
                                        Luna de facturat (ultima zi a lunii)
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                        <input type="text" class="form-control" id="billingMonth" name="billingMonth"
                                            placeholder="M/d/yyyy (ex: 1/31/2025)" required autocomplete="off">
                                    </div>

                                    <div class="help-text">Introduceți ultima zi a lunii în format M/d/yyyy</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="billingError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="billingSuccess"></div>
                                </div>


                                <!-- </form> -->

                                <div class="result-box p-4 mt-4 d-none" id="billingResult">
                                    <h4 class="mb-3 text-primary">Detalii Data Selectată:</h4>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Data:</strong></p>
                                            <h5 id="resultDate" class="text-dark">-</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Luna:</strong></p>
                                            <h5 id="resultMonth" class="text-dark">-</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>An:</strong></p>
                                            <h5 id="resultYear" class="text-dark">-</h5>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <!-- Formular Date Personale -->
                        <div class="form-section">
                            <h3 class="section-title">2. Date Personale</h3>

                            <div id="nameForm">
                                <div class="mb-4">
                                    <label for="fullName" class="form-label">
                                        Nume și Prenume (Cu Majuscule)
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" class="form-control" id="fullName" name="fullName"
                                            placeholder="Introduceți numele complet" required autocomplete="name">
                                    </div>

                                    <div class="help-text">Introduceți minim 2 cuvinte (nume + prenume)</div>
                                    <div class="char-count" id="charCount">0 caractere</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="nameError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="nameSuccess"></div>
                                </div>


                            </div>

                            <div class="result-box p-4 mt-4 d-none" id="nameResult">
                                <h4 class="mb-3 text-primary">Date Validate:</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Nume complet:</strong></p>
                                        <h5 id="resultName" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Număr cuvinte:</strong></p>
                                        <h5 id="resultWords" class="text-dark">-</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formular Zile Rezervate -->
                        <div class="form-section">
                            <h3 class="section-title">Zile Rezervate</h3>

                            <div id="daysForm">
                                <div class="mb-4">
                                    <label for="daysReserved" class="form-label">
                                        Număr de zile rezervate (punct pentru zecimale)
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-calendar-week"></i></span>
                                        <input type="text" class="form-control" id="daysReserved" name="daysReserved"
                                            placeholder="Ex: 20.50" required autocomplete="off" inputmode="decimal">
                                    </div>

                                    <div class="help-text">Introduceți numărul de zile (minim 0.01, maxim 2 zecimale)
                                    </div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="daysError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="daysSuccess"></div>
                                </div>


                            </div>

                            <div class="result-box p-4 mt-4 d-none" id="daysResult">
                                <h4 class="mb-3 text-primary">Rezervare Confirmată:</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Număr zile:</strong></p>
                                        <h5 id="resultDays" class="text-dark">- zile</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Zile întregi:</strong></p>
                                        <h5 id="resultWholeDays" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Zile parțiale:</strong></p>
                                        <h5 id="resultPartialDays" class="text-dark">-</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">Zile Facturate</h3>

                            <div id="invoiceDaysForm">
                                <div class="mb-4">
                                    <label for="zile-facturate" class="form-label">
                                        Număr de zile facturate (punct pentru zecimale)
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-calendar-check-fill"></i></span>
                                        <input type="text" class="form-control" id="zile_facturate"
                                            name="zile_facturate" placeholder="Ex: 20.50" required autocomplete="off"
                                            inputmode="decimal">
                                    </div>

                                    <div class="help-text">Introduceți o valoare pozitivă cu maximum 2 zecimale (ex:
                                        20.50)</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="zileFacturateError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="zileFacturateSuccess">
                                    </div>
                                </div>


                            </div>

                            <div class="result-box p-4 mt-4 d-none" id="invoiceDaysResult">
                                <h4 class="mb-3 text-primary">Facturare Confirmată:</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Zile Facturate:</strong></p>
                                        <h5 id="resultFacturateDays" class="text-dark">- zile</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Zile Disponibile:</strong></p>
                                        <h5 id="resultAvailableDays" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Procent Utilizare:</strong></p>
                                        <h5 id="resultUsagePercentage" class="text-dark">-</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <h3 class="section-title">Selectare Client</h3>

                            <div id="clientForm">
                                <div class="mb-4">
                                    <label for="nume-client" class="form-label">
                                        Nume Client
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                        <select class="form-select" id="nume-client" name="nume_client" required>
                                            <option value="" selected disabled>Selectați un client</option>
                                            <!-- Opțiunile vor fi populate dinamic din baza de date -->
                                        </select>
                                    </div>

                                    <div class="help-text">Selectați un client din lista predefinită configurată în
                                        admin</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="clientError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="clientSuccess"></div>
                                </div>


                            </div>

                            <div class="result-box p-4 mt-4 d-none" id="clientResult">
                                <h4 class="mb-3 text-primary">Client Selectat:</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Nume Client:</strong></p>
                                        <h5 id="resultClientName" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Cod Client:</strong></p>
                                        <h5 id="resultClientCode" class="text-dark">-</h5>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Data selecției:</strong></p>
                                        <h6 id="resultSelectionDate" class="text-dark">-</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Status:</strong></p>
                                        <span class="badge bg-success" id="resultClientStatus">Activ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <h3 class="section-title">Seria Facturii</h3>

                            <div id="serieFacturaForm">
                                <div class="mb-4">
                                    <label for="serie-factura" class="form-label">
                                        Serie Factura
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-type"></i></span>
                                        <input type="text" class="form-control" id="serie-factura" name="serie_factura"
                                            placeholder="Ex: INV, FACT, FT" required autocomplete="off">
                                    </div>

                                    <div class="help-text">Introduceți seria facturii (maxim 10 caractere, ex: INV,
                                        FACT, FT2024)</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="serieFacturaError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="serieFacturaSuccess">
                                    </div>
                                </div>


                            </div>

                            <div class="result-box p-4 mt-4 d-none" id="serieFacturaResult">
                                <h4 class="mb-3 text-primary">Seria Facturii Confirmată:</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Serie Factura:</strong></p>
                                        <h5 id="resultSerieFactura" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Lungime Serie:</strong></p>
                                        <h5 id="resultSerieLength" class="text-dark">
                                            <span class="badge bg-info">- caractere</span>
                                        </h5>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Format Recomandat:</strong></p>
                                        <h6 id="resultFormatRecomandat" class="text-dark">-</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Data validării:</strong></p>
                                        <h6 id="resultSerieDate" class="text-dark">-</h6>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <p class="mb-1"><strong>Sugestii Serii:</strong></p>
                                    <div class="d-flex flex-wrap gap-2" id="serieSuggestions">
                                        <!-- Sugestiile vor fi generate dinamic -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <h3 class="section-title">Numărul Facturii</h3>

                            <div id="numarFacturaForm">
                                <div class="mb-4">
                                    <label for="numar-factura" class="form-label">
                                        Numar Factura
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-123"></i></span>
                                        <input type="text" class="form-control" id="numar_factura" name="numar_factura"
                                            placeholder="Ex: 12345, 1001, 999" required autocomplete="off"
                                            inputmode="numeric">
                                    </div>

                                    <div class="help-text">Introduceți un număr întreg pozitiv (minimum 1, recomandat să
                                        nu depășească 999999)</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="numarFacturaError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="numarFacturaSuccess">
                                    </div>
                                </div>


                            </div>

                            <div class="result-box p-4 mt-4 d-none" id="numarFacturaResult">
                                <h4 class="mb-3 text-primary">Număr Factură Confirmat:</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Număr Factură:</strong></p>
                                        <h5 id="resultNumarFactura" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Formatat:</strong></p>
                                        <h5 id="resultNumarFormatat" class="text-dark">-</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Par/Impar:</strong></p>
                                        <h5 id="resultParitate" class="text-dark">
                                            <span class="badge bg-secondary">-</span>
                                        </h5>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Următorul număr disponibil:</strong></p>
                                        <h6 id="resultUrmatorulNumar" class="text-dark">-</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Data validării:</strong></p>
                                        <h6 id="resultNumarDate" class="text-dark">-</h6>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <p class="mb-2"><strong>Sugestii numere:</strong></p>
                                    <div class="d-flex flex-wrap gap-2" id="numarSuggestions">
                                        <!-- Sugestiile vor fi generate dinamic -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="timesheet-upload" class="form-label">
                                Incarcare Timesheet <span class="text-danger">*</span>
                            </label>

                            <div class="border rounded p-4 text-center upload-area"
                                style="border-style: dashed !important; cursor: pointer;"
                                onclick="document.getElementById('timesheet-upload').click()">
                                <input type="file" class="form-control d-none" id="timesheet-upload"
                                    name="timesheet_file" accept=".pdf, application/pdf" required>

                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="mb-2">Trageți și plasați fișierul aici</h5>
                                    <p class="text-muted mb-2">sau</p>
                                    <button type="button" class="btn btn-primary">
                                        <i class="fas fa-folder-open me-2"></i> Selectați fișier
                                    </button>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Format: PDF Only | Max: 10MB |
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div id="file-preview" class="mt-3"></div>
                            <div id="file-error" class="text-danger small mt-2"></div>
                        </div>
                        <!-- Buton Final -->
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-lg btn-success w-100" id="finalSubmitBtn">
                                <i class="bi bi-send-check me-2"></i>Trimite Toate Datele
                            </button>
                            <div class="help-text mt-2">Acest buton va fi activat după validarea tuturor formarelor
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // =============================================
        // Funcții utilitare comune
        // =============================================
        console.log("Script încărcat: Formular Pontaj");

        function showAlert(elementId, message, type = 'error') {
            const element = document.getElementById(elementId);
            element.innerHTML = message;
            element.classList.remove('d-none');
            element.classList.remove('alert-success', 'alert-danger');
            element.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
        }

        function hideAlert(elementId) {
            const element = document.getElementById(elementId);
            element.classList.add('d-none');
        }

        function showResult(elementId) {
            document.getElementById(elementId).classList.remove('d-none');
        }

        function hideResult(elementId) {
            document.getElementById(elementId).classList.add('d-none');
        }

        function updateFinalSubmitButton() {
            const billingValid = document.getElementById('billingMonth').classList.contains('is-valid');
            const nameValid = document.getElementById('fullName').classList.contains('is-valid');
            const daysValid = document.getElementById('daysReserved').classList.contains('is-valid');

            const finalBtn = document.getElementById('finalSubmitBtn');
            if (billingValid && nameValid && daysValid) {
                finalBtn.disabled = false;
                finalBtn.innerHTML = '<i class="bi bi-send-check me-2"></i>Trimite Toate Datele';
            } else {
                finalBtn.disabled = true;
                finalBtn.innerHTML = '<i class="bi bi-lock me-2"></i>Completează toate câmpurile';
            }
        }

        // =============================================
        // 1. Validare Luna de Facturat
        // =============================================

        const billingForm = document.getElementById('billingForm');
        const billingInput = document.getElementById('billingMonth');
        const submitBillingBtn = document.getElementById('submitBillingBtn');

        // Funcție pentru a verifica dacă o dată este ultima zi a lunii
        function isLastDayOfMonth(date) {
            const testDate = new Date(date);
            const nextDay = new Date(testDate);
            nextDay.setDate(testDate.getDate() + 1);
            return nextDay.getMonth() !== testDate.getMonth();
        }

        // Funcție pentru a valida formatul M/d/yyyy
        function validateDateFormat(dateString) {
            const regex = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
            return regex.test(dateString);
        }

        // Funcție pentru a parsare data în format M/d/yyyy
        function parseDate(dateString) {
            const parts = dateString.split('/');
            if (parts.length !== 3) return null;

            const month = parseInt(parts[0], 10);
            const day = parseInt(parts[1], 10);
            const year = parseInt(parts[2], 10);

            if (month < 1 || month > 12 || day < 1 || day > 31 || year < 1900) {
                return null;
            }

            const date = new Date(year, month - 1, day);

            if (date.getMonth() !== month - 1 || date.getDate() !== day) {
                return null;
            }

            return date;
        }

        // Validare input
        billingInput.addEventListener('blur', function () {
            validateBillingInput(this.value);
        });

        billingInput.addEventListener('input', function () {
            this.classList.remove('is-invalid', 'is-valid');
            hideAlert('billingError');
            hideAlert('billingSuccess');
        });

        // Auto-formatare
        billingInput.addEventListener('keypress', function (e) {
            const value = this.value;

            if ((value.length === 1 || value.length === 2) && e.key !== '/' && !isNaN(e.key)) {
                const month = parseInt(value + e.key);
                if (month > 12 && value.length === 1) {
                    this.value = value + '/';
                } else if (value.length === 2) {
                    this.value = value + '/';
                }
            }

            if ((value.length === 4 || value.length === 5) && e.key !== '/' && !isNaN(e.key)) {
                const parts = value.split('/');
                if (parts.length === 2) {
                    const day = parseInt(parts[1] + e.key);
                    if (day > 31 && parts[1].length === 1) {
                        this.value = value + '/';
                    } else if (parts[1].length === 2) {
                        this.value = value + '/';
                    }
                }
            }
        });

        function validateBillingInput(value) {
            billingInput.classList.remove('is-invalid', 'is-valid');
            hideAlert('billingError');
            hideAlert('billingSuccess');

            if (!value) {
                billingInput.classList.add('is-invalid');
                showAlert('billingError', 'Câmpul este obligatoriu', 'error');
                return false;
            }

            if (!validateDateFormat(value)) {
                billingInput.classList.add('is-invalid');
                showAlert('billingError', 'Format invalid! Folosiți formatul M/d/yyyy (ex: 1/31/2025)', 'error');
                return false;
            }

            const date = parseDate(value);

            if (!date) {
                billingInput.classList.add('is-invalid');
                showAlert('billingError', 'Data introdusă nu este validă', 'error');
                return false;
            }

            if (!isLastDayOfMonth(date)) {
                billingInput.classList.add('is-invalid');
                showAlert('billingError', 'Data trebuie să fie ultima zi a unei luni', 'error');
                return false;
            }

            billingInput.classList.add('is-valid');
            showAlert('billingSuccess', '✓ Data este validă!', 'success');
            return true;
        }

        billingForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const isValid = validateBillingInput(billingInput.value);

            if (isValid) {
                const date = parseDate(billingInput.value);
                const monthNames = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie',
                    'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'
                ];

                document.getElementById('resultDate').textContent = billingInput.value;
                document.getElementById('resultMonth').textContent = monthNames[date.getMonth()];
                document.getElementById('resultYear').textContent = date.getFullYear();

                showResult('billingResult');
                updateFinalSubmitButton();

                // Simulare trimitere
                console.log('Data validată:', {
                    rawValue: billingInput.value,
                    date: date,
                    month: date.getMonth() + 1,
                    year: date.getFullYear()
                });
            } else {
                hideResult('billingResult');
            }
        });

        // =============================================
        // 2. Validare Nume și Prenume
        // =============================================

        const nameForm = document.getElementById('nameForm');
        const nameInput = document.getElementById('fullName');
        const charCount = document.getElementById('charCount');

        // Funcție pentru capitalizare automată
        function capitalizeWords(str) {
            return str
                .toLowerCase()
                .split(' ')
                .map(word => {
                    if (word.length === 0) return '';
                    return word.charAt(0).toUpperCase() + word.slice(1);
                })
                .join(' ');
        }

        // Funcție pentru validare doar litere și spații
        function containsOnlyLettersAndSpaces(str) {
            const regex = /^[a-zA-ZăâîșțĂÂÎȘȚ\s\-]+$/;
            return regex.test(str);
        }

        // Funcție pentru validare minim 2 cuvinte
        function hasMinimumWords(str) {
            const words = str.trim().split(/\s+/).filter(word => word.length > 0);
            return words.length >= 2;
        }

        // Funcție pentru validare că fiecare cuvânt are minim 2 litere
        function allWordsValid(str) {
            const words = str.trim().split(/\s+/).filter(word => word.length > 0);
            return words.every(word => word.length >= 2);
        }

        // Event listener pentru input live
        nameInput.addEventListener('input', function (e) {
            const cursorPosition = this.selectionStart;
            const originalLength = this.value.length;

            const capitalizedValue = capitalizeWords(this.value);
            this.value = capitalizedValue;

            const lengthDifference = capitalizedValue.length - originalLength;
            this.setSelectionRange(cursorPosition + lengthDifference, cursorPosition + lengthDifference);

            const charLength = this.value.length;
            charCount.textContent = `${charLength} caractere`;

            this.classList.remove('is-invalid', 'is-valid');
            hideAlert('nameError');
            hideAlert('nameSuccess');
        });

        function validateNameInput(value) {
            nameInput.classList.remove('is-invalid', 'is-valid');
            hideAlert('nameError');
            hideAlert('nameSuccess');

            if (!value || value.trim() === '') {
                nameInput.classList.add('is-invalid');
                showAlert('nameError', 'Câmpul este obligatoriu', 'error');
                return false;
            }

            if (!containsOnlyLettersAndSpaces(value)) {
                nameInput.classList.add('is-invalid');
                showAlert('nameError', 'Folosiți doar litere și spații (nu sunt permise cifre sau caractere speciale)', 'error');
                return false;
            }

            if (!hasMinimumWords(value)) {
                nameInput.classList.add('is-invalid');
                showAlert('nameError', 'Introduceți minim 2 cuvinte (nume și prenume)', 'error');
                return false;
            }

            if (!allWordsValid(value)) {
                nameInput.classList.add('is-invalid');
                showAlert('nameError', 'Fiecare cuvânt trebuie să aibă minim 2 litere', 'error');
                return false;
            }

            if (/\s{2,}/.test(value)) {
                nameInput.classList.add('is-invalid');
                showAlert('nameError', 'Nu folosiți spații multiple consecutive', 'error');
                return false;
            }

            if (value !== value.trim()) {
                nameInput.value = value.trim();
                return validateNameInput(nameInput.value);
            }

            nameInput.classList.add('is-valid');
            showAlert('nameSuccess', '✓ Numele este valid!', 'success');
            return true;
        }

        nameInput.addEventListener('blur', function () {
            if (this.value) {
                validateNameInput(this.value);
            }
        });

        nameForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const isValid = validateNameInput(nameInput.value);

            if (isValid) {
                const words = nameInput.value.trim().split(/\s+/).filter(word => word.length > 0);

                document.getElementById('resultName').textContent = nameInput.value;
                document.getElementById('resultWords').textContent = words.length;

                showResult('nameResult');
                updateFinalSubmitButton();

                console.log('Nume validat:', {
                    fullName: nameInput.value,
                    words: words,
                    wordCount: words.length
                });
            } else {
                hideResult('nameResult');
            }
        });

        // =============================================
        // 3. Validare Zile Rezervate
        // =============================================

        const daysForm = document.getElementById('daysForm');
        const daysInput = document.getElementById('daysReserved');

        // Funcție pentru a permite doar cifre și punct
        function sanitizeInput(value) {
            value = value.replace(',', '.');
            value = value.replace(/[^\d.]/g, '');

            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            return value;
        }

        // Funcție pentru validare format zecimal
        function isValidDecimal(value) {
            const regex = /^\d+(\.\d{1,2})?$/;
            return regex.test(value);
        }

        // Funcție pentru a număra zecimalele
        function countDecimals(value) {
            if (value.includes('.')) {
                return value.split('.')[1].length;
            }
            return 0;
        }

        // Event listener pentru input live
        daysInput.addEventListener('input', function (e) {
            const cursorPosition = this.selectionStart;
            const originalValue = this.value;

            const sanitizedValue = sanitizeInput(this.value);

            if (sanitizedValue !== originalValue) {
                this.value = sanitizedValue;
                const diff = originalValue.length - sanitizedValue.length;
                this.setSelectionRange(cursorPosition - diff, cursorPosition - diff);
            }

            this.classList.remove('is-invalid', 'is-valid');
            hideAlert('daysError');
            hideAlert('daysSuccess');
        });

        function validateDaysInput(value) {
            daysInput.classList.remove('is-invalid', 'is-valid');
            hideAlert('daysError');
            hideAlert('daysSuccess');

            if (!value || value.trim() === '') {
                daysInput.classList.add('is-invalid');
                showAlert('daysError', 'Câmpul este obligatoriu', 'error');
                return false;
            }

            if (value.endsWith('.')) {
                daysInput.classList.add('is-invalid');
                showAlert('daysError', 'Numărul nu poate să se termine cu punct', 'error');
                return false;
            }

            if (!isValidDecimal(value)) {
                daysInput.classList.add('is-invalid');
                showAlert('daysError', 'Format invalid. Folosiți punct pentru zecimale (ex: 20.50)', 'error');
                return false;
            }

            if (countDecimals(value) > 2) {
                daysInput.classList.add('is-invalid');
                showAlert('daysError', 'Maxim 2 zecimale sunt permise', 'error');
                return false;
            }

            const numValue = parseFloat(value);

            if (numValue <= 0) {
                daysInput.classList.add('is-invalid');
                showAlert('daysError', 'Valoarea trebuie să fie pozitivă (mai mare ca 0)', 'error');
                return false;
            }

            if (numValue > 365) {
                daysInput.classList.add('is-invalid');
                showAlert('daysError', 'Numărul de zile nu poate depăși 365', 'error');
                return false;
            }

            daysInput.classList.add('is-valid');
            showAlert('daysSuccess', `✓ Valoare validă: ${value} zile`, 'success');
            return true;
        }

        daysInput.addEventListener('blur', function () {
            if (this.value) {
                validateDaysInput(this.value);
            }
        });

        daysInput.addEventListener('keypress', function (e) {
            const char = e.key;
            const currentValue = this.value;

            if (e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 13 ||
                e.keyCode === 46 || (e.keyCode >= 37 && e.keyCode <= 40)) {
                return;
            }

            if (char >= '0' && char <= '9') {
                if (currentValue.includes('.')) {
                    const parts = currentValue.split('.');
                    const cursorPos = this.selectionStart;
                    const beforeCursor = currentValue.substring(0, cursorPos);

                    if (beforeCursor.includes('.') && parts[1].length >= 2) {
                        e.preventDefault();
                        return;
                    }
                }
                return;
            }

            if ((char === '.' || char === ',') && !currentValue.includes('.')) {
                if (char === ',') {
                    e.preventDefault();
                    this.value += '.';
                }
                return;
            }

            e.preventDefault();
        });

        daysForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const isValid = validateDaysInput(daysInput.value);

            if (isValid) {
                const daysValue = parseFloat(daysInput.value);
                const wholeDays = Math.floor(daysValue);
                const partialDays = daysValue - wholeDays;

                document.getElementById('resultDays').textContent = `${daysInput.value} zile`;
                document.getElementById('resultWholeDays').textContent = wholeDays;
                document.getElementById('resultPartialDays').textContent = partialDays > 0 ? partialDays.toFixed(2) : '0';

                showResult('daysResult');
                updateFinalSubmitButton();

                console.log('Zile rezervate:', {
                    totalDays: daysValue,
                    wholeDays: wholeDays,
                    partialDays: partialDays,
                    rawValue: daysInput.value
                });
            } else {
                hideResult('daysResult');
            }
        });

        // =============================================
        // Final Submit Button
        // =============================================

        document.getElementById('finalSubmitBtn').addEventListener('click', function () {
            // Validare finală
            const billingValid = document.getElementById('billingMonth').classList.contains('is-valid');
            const nameValid = document.getElementById('fullName').classList.contains('is-valid');
            const daysValid = document.getElementById('daysReserved').classList.contains('is-valid');

            if (billingValid && nameValid && daysValid) {
                // Colectare date
                const formData = {
                    billingMonth: document.getElementById('billingMonth').value,
                    fullName: document.getElementById('fullName').value,
                    daysReserved: document.getElementById('daysReserved').value,
                    timestamp: new Date().toISOString()
                };

                console.log('Date trimise:', formData);

                // Afișare mesaj de succes
                const finalBtn = document.getElementById('finalSubmitBtn');
                finalBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Date trimise cu succes!';
                finalBtn.classList.remove('btn-success');
                finalBtn.classList.add('btn-primary');
                finalBtn.disabled = true;

                // Alert Bootstrap
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-4';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Succes!</strong> Toate datele au fost trimise cu succes.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                document.querySelector('.p-4').prepend(alertDiv);

                // Reset după 5 secunde
                setTimeout(() => {
                    finalBtn.innerHTML = '<i class="bi bi-send-check me-2"></i>Trimite Toate Datele';
                    finalBtn.classList.remove('btn-primary');
                    finalBtn.classList.add('btn-success');
                    finalBtn.disabled = false;
                }, 5000);
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Presupunem că avem valoarea zilelor rezervate într-o variabilă globală
            // Sau într-un atribut data-* pe input
            const zileRezervate = 25; // Exemplu: înlocuiți cu valoarea din cod

            const inputZile = document.getElementById('zile-facturate');
            inputZile.max = zileRezervate; // Setează maximul dinamic

            inputZile.addEventListener('input', function () {
                const value = parseFloat(this.value);
                const regex = /^\d+(\.\d{1,2})?$/;

                // Validare pentru 2 zecimale
                if (!regex.test(this.value) && this.value !== '') {
                    this.setCustomValidity('Maximum 2 zecimale permise');
                    this.classList.add('is-invalid');
                }
                // Validare pentru valoare pozitivă
                else if (value <= 0) {
                    this.setCustomValidity('Introduceți o valoare pozitivă');
                    this.classList.add('is-invalid');
                }
                // Validare pentru maxim zile rezervate
                else if (value > zileRezervate) {
                    this.setCustomValidity(`Valoarea nu poate depăși ${zileRezervate} zile`);
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });

            // Validare la submit formular
            const form = inputZile.closest('form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!inputZile.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    inputZile.classList.add('was-validated');
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const serieInput = document.getElementById('serie-factura');

            // Validare la input
            serieInput.addEventListener('input', function () {
                const value = this.value.trim();

                // Validare pentru maxim 10 caractere
                if (value.length > 10) {
                    this.value = value.substring(0, 10);
                }

                // Validare pentru caractere permise (opțional)
                // Puteți adăuga restricții specifice dacă este necesar
                const regex = /^[A-Z0-9\-\/]*$/; // Exemplu: doar litere mari, cifre, '-', '/'

                if (value !== '' && !regex.test(value)) {
                    this.setCustomValidity('Folosiți doar litere mari, cifre și caracterele "-", "/"');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    if (value.length > 0) {
                        this.classList.add('is-valid');
                    }
                }
            });

            // Validare la submit
            const form = serieInput.closest('form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!serieInput.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    serieInput.classList.add('was-validated');
                });
            }
        });

        // Exemplu de funcție care se execută după trimiterea cu succes a pontajului
        function onPontajSuccess(pontajData) {
            // Extrage datele necesare
            const serieFactura = document.getElementById('serie-factura').value;
            const numeColaborator = pontajData.nume_colaborator; // Sau din alt input
            const luna = pontajData.luna; // Format: Ianuarie 2024

            // Trimite email-ul către administratori
            sendEmailToAdmins(serieFactura, numeColaborator, luna);
        }

        // Funcție pentru trimiterea email-ului
        async function sendEmailToAdmins(serieFactura, numeColaborator, luna) {
            const emailData = {
                to: getAdminEmails(), // Funcție care returnează lista de email-uri configurate
                subject: `[PONTAJ NOU] ${numeColaborator} - ${luna}`,
                content: generateEmailContent(serieFactura, numeColaborator, luna)
            };

            try {
                const response = await fetch('/send-mail.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(emailData)
                });

                if (response.ok) {
                    console.log('Email trimis cu succes către administratori');
                }
            } catch (error) {
                console.error('Eroare la trimiterea email-ului:', error);
            }
        }

        // Funcție pentru generarea conținutului email-ului
        function generateEmailContent(serieFactura, numeColaborator, luna) {
            return `
    <h2>Notificare Pontaj Nou</h2>
    <p>Un nou pontaj a fost trimis cu succes.</p>
    <ul>
        <li><strong>Serie Factura:</strong> ${serieFactura}</li>
        <li><strong>Colaborator:</strong> ${numeColaborator}</li>
        <li><strong>Luna:</strong> ${luna}</li>
        <li><strong>Data trimiterii:</strong> ${new Date().toLocaleDateString('ro-RO')}</li>
    </ul>
    <p>Accesați panoul de administrare pentru a verifica și procesa pontajul.</p>
    <hr>
    <p><em>Acest email a fost generat automat.</em></p>
    `;
        }

        // Funcție pentru obținerea email-urilor administratorilor (exemplu)
        function getAdminEmails() {
            // Într-o aplicație reală, această listă ar fi din baza de date sau config
            return ['admin@companie.ro', 'manager@companie.ro'];
        }
        document.addEventListener('DOMContentLoaded', function () {
            const numarFacturaInput = document.getElementById('numar-factura');

            // Previne introducerea numerelor zecimale
            numarFacturaInput.addEventListener('keydown', function (e) {
                // Blochează tastele care nu sunt permise
                const invalidKeys = ['-', '+', 'e', 'E', '.', ','];
                if (invalidKeys.includes(e.key)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Validare la input
            numarFacturaInput.addEventListener('input', function () {
                let value = this.value;

                // Asigură că valoarea este un întreg pozitiv
                if (value < 1) {
                    this.value = '';
                    this.setCustomValidity('Introduceți un număr pozitiv (mai mare decât 0)');
                    this.classList.add('is-invalid');
                    return;
                }

                // Elimină partea zecimală dacă există (din paste)
                if (value.includes('.')) {
                    value = Math.floor(value);
                    this.value = value;
                }

                // Validare pentru număr întreg
                if (!Number.isInteger(parseFloat(value)) && value !== '') {
                    this.setCustomValidity('Introduceți un număr întreg');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');

                    if (value !== '') {
                        this.classList.add('is-valid');
                    }
                }
            });

            // Validare la blur (când părăsește câmpul)
            numarFacturaInput.addEventListener('blur', function () {
                if (this.value && parseInt(this.value) < 1) {
                    this.setCustomValidity('Numărul facturii trebuie să fie mai mare decât 0');
                    this.classList.add('is-invalid');
                }
            });

            // Validare la submit formular
            const form = numarFacturaInput.closest('form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!numarFacturaInput.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    numarFacturaInput.classList.add('was-validated');
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('timesheet-upload');
            const filePreview = document.getElementById('file-preview');
            const fileError = document.getElementById('file-error');
            const maxSize = 10 * 1024 * 1024; // 10MB în bytes
            const fileNameRegex = /^(0[1-9]|1[0-2])_(19|20)\d{2}\.pdf$/i;

            // Resetare la schimbarea paginii
            fileInput.value = '';

            // Validare la selectarea fișierului
            fileInput.addEventListener('change', function () {
                fileError.textContent = '';
                filePreview.innerHTML = '';

                if (this.files.length > 0) {
                    const file = this.files[0];
                    validateAndPreviewFile(file);
                }
            });

            // Funcție pentru validare și previzualizare
            function validateAndPreviewFile(file) {
                let isValid = true;
                let errorMessages = [];

                // 1. Verificare tip fișier
                if (file.type !== 'application/pdf') {
                    isValid = false;
                    errorMessages.push('Fișierul trebuie să fie de tip PDF');
                }

                // 2. Verificare dimensiune
                if (file.size > maxSize) {
                    isValid = false;
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    errorMessages.push(`Fișierul este prea mare (${sizeMB}MB). Maxim permis: 10MB`);
                }

                // 3. Verificare nume fișier
                const fileName = file.name;
                if (!fileNameRegex.test(fileName)) {
                    isValid = false;
                    errorMessages.push(`Denumire incorectă. Folosiți formatul: &lt;MM&gt;_&lt;YYYY&gt;.PDF (ex: 06_2020.pdf)`);

                    // Sugestie de nume corect
                    const currentDate = new Date();
                    const suggestedMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const suggestedYear = currentDate.getFullYear();
                    errorMessages.push(`Sugestie nume corect: ${suggestedMonth}_${suggestedYear}.pdf`);
                }

                // 4. Verificare data din nume (opțional)
                if (fileNameRegex.test(fileName)) {
                    const [month, yearExt] = fileName.split('_');
                    const year = yearExt.split('.')[0];

                    if (parseInt(month) < 1 || parseInt(month) > 12) {
                        errorMessages.push('Luna din nume trebuie să fie între 01 și 12');
                        isValid = false;
                    }

                    if (parseInt(year) < 2000 || parseInt(year) > 2100) {
                        errorMessages.push('Anul din nume trebuie să fie realist');
                        isValid = false;
                    }
                }

                // Afișare erori sau previzualizare
                if (!isValid) {
                    fileError.innerHTML = errorMessages.join('<br>');
                    fileInput.setCustomValidity('Fișier invalid');
                    fileInput.classList.add('is-invalid');
                    fileInput.value = ''; // Resetează input-ul
                } else {
                    fileInput.setCustomValidity('');
                    fileInput.classList.remove('is-invalid');
                    fileInput.classList.add('is-valid');

                    // Afișare previzualizare
                    showFilePreview(file);
                }
            }

            // Funcție pentru afișarea previzualizării fișierului
            function showFilePreview(file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    filePreview.innerHTML = `
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                            </div>
                            <div>
                                <h6 class="card-title mb-1">${file.name}</h6>
                                <p class="card-text small mb-1">
                                    <strong>Dimensiune:</strong> ${formatFileSize(file.size)}<br>
                                    <strong>Data încărcării:</strong> ${new Date().toLocaleString('ro-RO')}
                                </p>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFile()">
                                    <i class="fas fa-trash-alt"></i> Șterge
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                };

                reader.readAsDataURL(file);
            }

            // Funcție pentru formatarea dimensiunii fișierului
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Funcție pentru ștergerea fișierului
            window.removeFile = function () {
                fileInput.value = '';
                filePreview.innerHTML = '';
                fileError.textContent = '';
                fileInput.classList.remove('is-valid', 'is-invalid');
            };

            // Validare la submit formular
            const form = fileInput.closest('form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (fileInput.files.length === 0) {
                        fileError.textContent = 'Vă rugăm selectați un fișier PDF';
                        fileInput.classList.add('is-invalid');
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }

                    if (!fileInput.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });
            }

            // Drag and drop (opțional)
            const dropArea = document.querySelector('.mb-3');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropArea.classList.add('border-primary', 'border-2');
            }

            function unhighlight() {
                dropArea.classList.remove('border-primary', 'border-2');
            }

            dropArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files.length > 0) {
                    fileInput.files = files;
                    validateAndPreviewFile(files[0]);
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
            // Validare zile facturate
            const zileFacturateInput = document.getElementById('zile-facturate');
            const zileFacturateError = document.getElementById('zileFacturateError');
            const zileFacturateSuccess = document.getElementById('zileFacturateSuccess');
            const submitInvoiceDaysBtn = document.getElementById('submitInvoiceDaysBtn');
            const invoiceDaysResult = document.getElementById('invoiceDaysResult');

            // Eveniment pentru input live validation
            zileFacturateInput.addEventListener('input', function () {
                validateZileFacturateInput();
            });

            // Submit formular zile facturate
            document.getElementById('invoiceDaysForm').addEventListener('submit', function (e) {
                e.preventDefault();

                if (validateZileFacturateForm()) {
                    showInvoiceDaysResult();
                }
            });

            // Funcție pentru validare input în timp real
            function validateZileFacturateInput() {
                const value = zileFacturateInput.value.trim();
                zileFacturateError.classList.add('d-none');
                zileFacturateSuccess.classList.add('d-none');

                if (value === '') {
                    zileFacturateInput.classList.remove('is-valid', 'is-invalid');
                    return false;
                }

                // Validare format numeric cu punct și maxim 2 zecimale
                const regex = /^\d+(\.\d{1,2})?$/;

                if (!regex.test(value)) {
                    zileFacturateError.textContent = 'Format invalid. Folosiți punct pentru zecimale cu maxim 2 cifre (ex: 20.50)';
                    zileFacturateError.classList.remove('d-none');
                    zileFacturateInput.classList.add('is-invalid');
                    zileFacturateInput.classList.remove('is-valid');
                    return false;
                }

                const numValue = parseFloat(value);

                // Validare valoare pozitivă
                if (numValue <= 0) {
                    zileFacturateError.textContent = 'Introduceți o valoare pozitivă (mai mare decât 0)';
                    zileFacturateError.classList.remove('d-none');
                    zileFacturateInput.classList.add('is-invalid');
                    zileFacturateInput.classList.remove('is-valid');
                    return false;
                }

                // Validare față de zilele rezervate (dacă există)
                const daysReserved = parseFloat(document.getElementById('daysReserved')?.value) || 0;

                if (daysReserved > 0 && numValue > daysReserved) {
                    zileFacturateError.textContent = `Valoarea (${numValue}) depășește zilele rezervate (${daysReserved})`;
                    zileFacturateError.classList.remove('d-none');
                    zileFacturateInput.classList.add('is-invalid');
                    zileFacturateInput.classList.remove('is-valid');
                    return false;
                }

                // Validare reușită
                zileFacturateSuccess.textContent = 'Format corect!';
                zileFacturateSuccess.classList.remove('d-none');
                zileFacturateInput.classList.remove('is-invalid');
                zileFacturateInput.classList.add('is-valid');
                return true;
            }

            // Funcție pentru validare la submit
            function validateZileFacturateForm() {
                const value = zileFacturateInput.value.trim();

                if (value === '') {
                    zileFacturateError.textContent = 'Acest câmp este obligatoriu';
                    zileFacturateError.classList.remove('d-none');
                    zileFacturateInput.classList.add('is-invalid');
                    return false;
                }

                return validateZileFacturateInput();
            }

            // Funcție pentru afișarea rezultatului
            function showInvoiceDaysResult() {
                const value = parseFloat(zileFacturateInput.value);
                const daysReserved = parseFloat(document.getElementById('daysReserved')?.value) || 0;

                // Calcule
                const availableDays = Math.max(0, daysReserved - value);
                const usagePercentage = daysReserved > 0 ? ((value / daysReserved) * 100).toFixed(1) : 0;

                // Actualizare rezultate
                document.getElementById('resultFacturateDays').textContent = `${value} zile`;
                document.getElementById('resultAvailableDays').textContent = `${availableDays} zile`;
                document.getElementById('resultUsagePercentage').textContent = `${usagePercentage}%`;

                // Colorare în funcție de procentul de utilizare
                const percentageElement = document.getElementById('resultUsagePercentage');
                percentageElement.classList.remove('text-success', 'text-warning', 'text-danger');

                if (usagePercentage <= 70) {
                    percentageElement.classList.add('text-success');
                } else if (usagePercentage <= 90) {
                    percentageElement.classList.add('text-warning');
                } else {
                    percentageElement.classList.add('text-danger');
                }

                // Afișare rezultat
                invoiceDaysResult.classList.remove('d-none');

                // Scroll la rezultat
                invoiceDaysResult.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Animație buton
                submitInvoiceDaysBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Validat!';
                submitInvoiceDaysBtn.classList.add('btn-success');

                setTimeout(() => {
                    submitInvoiceDaysBtn.innerHTML = '<i class="bi bi-calculator me-2"></i>Validează Zilele Facturate';
                    submitInvoiceDaysBtn.classList.remove('btn-success');
                }, 2000);
            }

            // Sincronizare cu zilele rezervate (dacă există)
            const daysReservedInput = document.getElementById('daysReserved');
            if (daysReservedInput) {
                daysReservedInput.addEventListener('input', function () {
                    updateZileFacturateMaxLimit();
                });
            }

            // Funcție pentru actualizarea limitelor
            function updateZileFacturateMaxLimit() {
                const daysReserved = parseFloat(daysReservedInput?.value) || 0;
                const currentValue = parseFloat(zileFacturateInput.value) || 0;

                if (daysReserved > 0 && currentValue > daysReserved) {
                    zileFacturateError.textContent = `Valoarea curentă (${currentValue}) depășește noile zile rezervate (${daysReserved})`;
                    zileFacturateError.classList.remove('d-none');
                    zileFacturateInput.classList.add('is-invalid');
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
            const clientSelect = document.getElementById('nume-client');
            const clientError = document.getElementById('clientError');
            const clientSuccess = document.getElementById('clientSuccess');
            const submitClientBtn = document.getElementById('submitClientBtn');
            const clientResult = document.getElementById('clientResult');

            // Încărcare clienți din baza de date
            loadClients();

            // Validare la schimbarea selecției
            clientSelect.addEventListener('change', function () {
                validateClientSelection();
            });

            // Submit formular client
            document.getElementById('clientForm').addEventListener('submit', function (e) {
                e.preventDefault();

                if (validateClientForm()) {
                    showClientResult();
                }
            });

            // Funcție pentru încărcarea clienților
            async function loadClients() {
                try {
                    // Afișează indicator de încărcare
                    clientSelect.disabled = true;
                    const loadingOption = document.createElement('option');
                    loadingOption.value = '';
                    loadingOption.textContent = 'Se încarcă clienții...';
                    loadingOption.disabled = true;
                    clientSelect.appendChild(loadingOption);

                    // Simulare API call (înlocuiește cu endpoint-ul real)
                    // const response = await fetch('/api/clients');
                    // const clients = await response.json();

                    // Pentru demo - date simulate
                    setTimeout(() => {
                        // Șterge opțiunea de loading
                        clientSelect.innerHTML = '<option value="" selected disabled>Selectați un client</option>';

                        // Date simulate
                        const clients = [{
                            id: 1,
                            nume: 'SC Client Exemplu SRL',
                            cod: 'CL001',
                            status: 'activ'
                        },
                        {
                            id: 2,
                            nume: 'SC Alt Client SRL',
                            cod: 'CL002',
                            status: 'activ'
                        },
                        {
                            id: 3,
                            nume: 'SC Companie Test SRL',
                            cod: 'CL003',
                            status: 'inactiv'
                        },
                        {
                            id: 4,
                            nume: 'SC Business Solutions SRL',
                            cod: 'CL004',
                            status: 'activ'
                        },
                        {
                            id: 5,
                            nume: 'SC Tech Innovators SRL',
                            cod: 'CL005',
                            status: 'activ'
                        },
                        {
                            id: 6,
                            nume: 'SC Digital Solutions SRL',
                            cod: 'CL006',
                            status: 'activ'
                        }
                        ];

                        // Populează dropdown-ul
                        clients.forEach(client => {
                            const option = document.createElement('option');
                            option.value = client.id;
                            option.textContent = client.nume;
                            option.dataset.cod = client.cod;
                            option.dataset.status = client.status;

                            // Dacă clientul este inactiv, adaugă o indicație
                            if (client.status === 'inactiv') {
                                option.textContent += ' (Inactiv)';
                                option.disabled = true;
                            }

                            clientSelect.appendChild(option);
                        });

                        clientSelect.disabled = false;

                    }, 1000); // Simulează delay de încărcare

                } catch (error) {
                    console.error('Eroare la încărcarea clienților:', error);
                    clientError.textContent = 'Eroare la încărcarea listei de clienți. Vă rugăm reîncercați.';
                    clientError.classList.remove('d-none');
                    clientSelect.innerHTML = '<option value="" selected disabled>Eroare la încărcare</option>';
                }
            }

            // Funcție pentru validare în timp real
            function validateClientSelection() {
                const selectedValue = clientSelect.value;
                clientError.classList.add('d-none');
                clientSuccess.classList.add('d-none');

                if (selectedValue === '') {
                    clientSelect.classList.remove('is-valid', 'is-invalid');
                    return false;
                }

                const selectedOption = clientSelect.options[clientSelect.selectedIndex];

                if (selectedOption.disabled) {
                    clientError.textContent = 'Acest client este momentan inactiv și nu poate fi selectat';
                    clientError.classList.remove('d-none');
                    clientSelect.classList.add('is-invalid');
                    clientSelect.classList.remove('is-valid');
                    return false;
                }

                // Validare reușită
                clientSuccess.textContent = 'Client selectat cu succes!';
                clientSuccess.classList.remove('d-none');
                clientSelect.classList.remove('is-invalid');
                clientSelect.classList.add('is-valid');
                return true;
            }

            // Funcție pentru validare la submit
            function validateClientForm() {
                const selectedValue = clientSelect.value;

                if (selectedValue === '') {
                    clientError.textContent = 'Vă rugăm selectați un client din listă';
                    clientError.classList.remove('d-none');
                    clientSelect.classList.add('is-invalid');
                    return false;
                }

                return validateClientSelection();
            }

            // Funcție pentru afișarea rezultatului
            function showClientResult() {
                const selectedOption = clientSelect.options[clientSelect.selectedIndex];
                const clientName = selectedOption.textContent;
                const clientCode = selectedOption.dataset.cod || 'N/A';
                const clientStatus = selectedOption.dataset.status || 'activ';

                // Actualizare rezultate
                document.getElementById('resultClientName').textContent = clientName;
                document.getElementById('resultClientCode').textContent = clientCode;
                document.getElementById('resultSelectionDate').textContent = new Date().toLocaleString('ro-RO');

                // Actualizare status badge
                const statusBadge = document.getElementById('resultClientStatus');
                statusBadge.textContent = clientStatus === 'activ' ? 'Activ' : 'Inactiv';
                statusBadge.className = `badge bg-${clientStatus === 'activ' ? 'success' : 'secondary'}`;

                // Afișare rezultat
                clientResult.classList.remove('d-none');

                // Scroll la rezultat
                clientResult.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Animație buton
                submitClientBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Client Confirmat!';
                submitClientBtn.classList.add('btn-success');

                setTimeout(() => {
                    submitClientBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmă Selecția Clientului';
                    submitClientBtn.classList.remove('btn-success');
                }, 2000);
            }

            // Funcție pentru căutare rapidă în dropdown (opțional)
            function setupClientSearch() {
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.className = 'form-control mb-2';
                searchInput.placeholder = 'Căutați client...';
                searchInput.id = 'clientSearch';

                // Inserează căutarea deasupra dropdown-ului
                clientSelect.parentNode.insertBefore(searchInput, clientSelect);

                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();
                    const options = clientSelect.options;

                    for (let i = 0; i < options.length; i++) {
                        const option = options[i];
                        const text = option.textContent.toLowerCase();

                        if (text.includes(searchTerm)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    }
                });
            }

            // Activează căutarea (opțional)
            // setupClientSearch();
        });
        document.addEventListener('DOMContentLoaded', function () {
            const serieFacturaInput = document.getElementById('serie-factura');
            const serieFacturaError = document.getElementById('serieFacturaError');
            const serieFacturaSuccess = document.getElementById('serieFacturaSuccess');
            const submitSerieFacturaBtn = document.getElementById('submitSerieFacturaBtn');
            const serieFacturaResult = document.getElementById('serieFacturaResult');

            // Exemple de serii comune (pot fi preluate din baza de date)
            const serieExamples = ['INV', 'FACT', 'FT', 'FIS', 'NOTA', 'AVIZ', 'REC', 'CH'];
            const usedSeries = []; // Listă cu serii deja folosite (ar trebui preluată din DB)

            // Configurare input pentru uppercase automat
            serieFacturaInput.addEventListener('input', function () {
                this.value = this.value.toUpperCase();
                validateSerieFacturaInput();

                // Actualizează contorul de caractere în timp real
                updateCharacterCounter();
            });

            // Limitare la 10 caractere
            serieFacturaInput.addEventListener('keydown', function (e) {
                if (this.value.length >= 10 && e.key !== 'Backspace' && e.key !== 'Delete') {
                    e.preventDefault();
                    showMaxLengthWarning();
                }
            });

            // Validare la blur
            serieFacturaInput.addEventListener('blur', function () {
                if (this.value.trim() !== '') {
                    validateSerieFacturaInput();
                }
            });

            // Submit formular
            document.getElementById('serieFacturaForm').addEventListener('submit', function (e) {
                e.preventDefault();

                if (validateSerieFacturaForm()) {
                    showSerieFacturaResult();
                }
            });

            // Funcție pentru validare în timp real
            function validateSerieFacturaInput() {
                const value = serieFacturaInput.value.trim();
                serieFacturaError.classList.add('d-none');
                serieFacturaSuccess.classList.add('d-none');

                if (value === '') {
                    serieFacturaInput.classList.remove('is-valid', 'is-invalid');
                    return false;
                }

                // Validare lungime
                if (value.length > 10) {
                    serieFacturaError.textContent = 'Maximum 10 caractere permise';
                    serieFacturaError.classList.remove('d-none');
                    serieFacturaInput.classList.add('is-invalid');
                    serieFacturaInput.classList.remove('is-valid');
                    return false;
                }

                // Validare caractere permise (doar litere, cifre și cratime/underscore)
                const regex = /^[A-Z0-9\-_]+$/;
                if (!regex.test(value)) {
                    serieFacturaError.textContent = 'Folosiți doar litere mari, cifre, cratime (-) sau underscore (_)';
                    serieFacturaError.classList.remove('d-none');
                    serieFacturaInput.classList.add('is-invalid');
                    serieFacturaInput.classList.remove('is-valid');
                    return false;
                }

                // Validare că începe cu literă (recomandat)
                if (!/^[A-Z]/.test(value)) {
                    serieFacturaError.textContent = 'Recomandat: seria să înceapă cu o literă';
                    serieFacturaError.classList.remove('d-none');
                }

                // Verificare dacă seria este deja folosită (opțional)
                if (usedSeries.includes(value)) {
                    serieFacturaError.textContent = 'Atenție: Această serie a fost deja folosită';
                    serieFacturaError.classList.remove('d-none');
                    serieFacturaInput.classList.add('is-invalid');
                    serieFacturaInput.classList.remove('is-valid');
                    return false;
                }

                // Validare reușită
                serieFacturaSuccess.textContent = 'Serie validă!';
                serieFacturaSuccess.classList.remove('d-none');
                serieFacturaInput.classList.remove('is-invalid');
                serieFacturaInput.classList.add('is-valid');
                return true;
            }

            // Funcție pentru afișarea avertismentului de lungime maximă
            function showMaxLengthWarning() {
                const warning = document.createElement('div');
                warning.className = 'alert alert-warning alert-dismissible fade show mt-2';
                warning.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Atenție!</strong> Maxim 10 caractere permise pentru seria facturii.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                // Adaugă doar dacă nu există deja
                if (!document.querySelector('.max-length-warning')) {
                    warning.classList.add('max-length-warning');
                    serieFacturaInput.parentNode.parentNode.appendChild(warning);
                }
            }

            // Funcție pentru actualizarea contorului de caractere
            function updateCharacterCounter() {
                const value = serieFacturaInput.value;
                const counter = document.getElementById('characterCounter') || createCharacterCounter();
                counter.textContent = `${value.length}/10 caractere`;

                // Schimbă culoarea în funcție de lungime
                if (value.length > 8) {
                    counter.className = 'character-counter text-warning';
                } else if (value.length >= 10) {
                    counter.className = 'character-counter text-danger';
                } else {
                    counter.className = 'character-counter text-muted';
                }
            }

            // Funcție pentru crearea contorului de caractere
            function createCharacterCounter() {
                const counter = document.createElement('div');
                counter.id = 'characterCounter';
                counter.className = 'character-counter text-muted small mt-1';
                counter.textContent = '0/10 caractere';
                serieFacturaInput.parentNode.parentNode.appendChild(counter);
                return counter;
            }

            // Funcție pentru validare la submit
            function validateSerieFacturaForm() {
                const value = serieFacturaInput.value.trim();

                if (value === '') {
                    serieFacturaError.textContent = 'Acest câmp este obligatoriu';
                    serieFacturaError.classList.remove('d-none');
                    serieFacturaInput.classList.add('is-invalid');
                    return false;
                }

                return validateSerieFacturaInput();
            }

            // Funcție pentru afișarea rezultatului
            function showSerieFacturaResult() {
                const value = serieFacturaInput.value.trim();

                // Actualizare rezultate
                document.getElementById('resultSerieFactura').textContent = value;
                document.getElementById('resultSerieLength').innerHTML = `
            <span class="badge ${value.length > 8 ? 'bg-warning' : 'bg-info'}">
                ${value.length} caractere
            </span>
        `;

                // Determină formatul recomandat
                const formatRecomandat = determineRecommendedFormat(value);
                document.getElementById('resultFormatRecomandat').textContent = formatRecomandat;

                document.getElementById('resultSerieDate').textContent = new Date().toLocaleString('ro-RO');

                // Generează sugestii
                generateSerieSuggestions(value);

                // Afișare rezultat
                serieFacturaResult.classList.remove('d-none');

                // Scroll la rezultat
                serieFacturaResult.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Animație buton
                submitSerieFacturaBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Serie Validată!';
                submitSerieFacturaBtn.classList.add('btn-success');

                setTimeout(() => {
                    submitSerieFacturaBtn.innerHTML = '<i class="bi bi-file-earmark-text me-2"></i>Validează Seria Facturii';
                    submitSerieFacturaBtn.classList.remove('btn-success');
                }, 2000);
            }

            // Funcție pentru determinarea formatului recomandat
            function determineRecommendedFormat(serie) {
                if (/^\d+$/.test(serie)) {
                    return 'Doar cifre - recomandat să adăugați și litere';
                } else if (/^[A-Z]+$/.test(serie)) {
                    return 'Doar litere - format bun';
                } else if (/^[A-Z]+\d+$/.test(serie)) {
                    return 'Litere + cifre - format excelent';
                } else if (serie.includes('-')) {
                    return 'Cu separator - format standard';
                } else {
                    return 'Format personalizat';
                }
            }

            // Funcție pentru generarea sugestiilor
            function generateSerieSuggestions(currentSerie) {
                const suggestionsContainer = document.getElementById('serieSuggestions');
                suggestionsContainer.innerHTML = '';

                // Adaugă seriile comune ca sugestii
                serieExamples.forEach(serie => {
                    if (serie !== currentSerie && !usedSeries.includes(serie)) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-light text-dark border cursor-pointer';
                        badge.textContent = serie;
                        badge.style.cursor = 'pointer';

                        badge.addEventListener('click', function () {
                            serieFacturaInput.value = serie;
                            validateSerieFacturaInput();
                            updateCharacterCounter();
                        });

                        suggestionsContainer.appendChild(badge);
                    }
                });

                // Sugestie cu anul curent
                const currentYear = new Date().getFullYear().toString().slice(-2);
                const yearSuggestion = `FT${currentYear}`;

                if (!usedSeries.includes(yearSuggestion) && yearSuggestion !== currentSerie) {
                    const yearBadge = document.createElement('span');
                    yearBadge.className = 'badge bg-primary cursor-pointer';
                    yearBadge.textContent = yearSuggestion;
                    yearBadge.style.cursor = 'pointer';

                    yearBadge.addEventListener('click', function () {
                        serieFacturaInput.value = yearSuggestion;
                        validateSerieFacturaInput();
                        updateCharacterCounter();
                    });

                    suggestionsContainer.appendChild(yearBadge);
                }
            }

            // Inițializează contorul de caractere
            updateCharacterCounter();
        });


        document.getElementById('finalSubmitBtn').addEventListener('click', function () {
            const data = new FormData();

            // Verificăm dacă elementele există și luăm textul/valoarea lor
            const client = document.getElementById('fullName')?.value.trim();
            const serie = document.getElementById('numar_factura')?.value.trim();
            const zileFact = document.getElementById('zile_facturate')?.value;
            const zileRez = document.getElementById('daysReserved')?.value;
            console.log("Client:", client);
            console.log("Serie:", serie);
            console.log("Zile facturate:", zileFact);
            console.log("Zile rezervate:", zileRez);

            data.append('client', client || 'Client Necunoscut');
            data.append('serie_factura', serie || 'Fără Serie');
            data.append('zile_facturate', zileFact || '0');
            data.append('zile_rezervate', zileRez || '0');

            // Adăugăm și un ID pentru test (ulterior îl vei lua din DB)
            data.append('pontaj_id', '1');

            fetch('send-mail.php', {
                method: 'POST',
                body: data
            })
                .then(r => r.json())
                .then(response => {
                    console.log("Răspuns server:", response);
                    if (response.status === "success") {
                        window.location.href = response.redirect || "success.php";
                    } else {
                        alert((response.message || "Greșeală necunoscută"));
                    }
                })
                .catch(err => {
                    console.error('Eroare rețea:', err);
                    alert('Eroare rețea: ' + err);
                });
        });
    </script>
</body>

</html>