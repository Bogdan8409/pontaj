<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular Pontaj și Facturare</title>

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

        input.form-control:focus,
        select.form-select:focus {
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

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            transition: border-color 0.3s ease;
        }

        .upload-area:hover {
            border-color: #041945 !important;
        }

        #finalSubmitBtn:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
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
                    <?php
                    $msg = $_GET['msg'] ?? '';

                    switch ($msg) {
                        case "ok":
                            echo "Operațiune reușită!";
                            break;

                        case "error":
                            echo " Eroare!";
                            break;

                        default:
                            echo "";
                    }
                    ?>
                    <div class="p-4">
                        <form id="billingForm" method="post" enctype="multipart/form-data" action="salveaza_pontaj.php">

                            <!-- 1. Luna de Facturat -->
                            <div class="form-section">
                                <h3 class="section-title">1. Luna de Facturat</h3>

                                <div class="info-box p-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                                        <div>
                                            <p class="mb-1"><strong>Format:</strong> MM-dd-yyyy (selector de dată)</p>
                                            <p class="mb-0"><strong>Validare:</strong> Data trebuie să fie ultima zi a
                                                unei luni</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="billingMonth" class="form-label">
                                        Luna de facturat (ultima zi a lunii)
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                        <input type="date" class="form-control" id="billingMonth" name="billingMonth"
                                            required>
                                    </div>

                                    <div class="help-text">Selectați ultima zi a lunii</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="billingError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="billingSuccess"></div>
                                </div>

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

                            <!-- 2. Date Personale -->
                            <div class="form-section">
                                <h3 class="section-title">2. Date Personale</h3>

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

                            <!-- 3. Zile Rezervate -->
                            <div class="form-section">
                                <h3 class="section-title">3. Zile Rezervate</h3>

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

                            <!-- 4. Zile Facturate -->
                            <div class="form-section">
                                <h3 class="section-title">4. Zile Facturate</h3>

                                <div class="mb-4">
                                    <label for="zile_facturate" class="form-label">
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

                            <!-- 5. Selectare Client -->
                            <div class="form-section">
                                <h3 class="section-title">5. Selectare Client</h3>

                                <div class="mb-4">
                                    <label for="nume-client" class="form-label">
                                        Nume Client
                                        <span class="required"></span>
                                    </label>

                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                        <select class="form-select" id="nume-client" name="nume_client">
                                            <option value="">Selectați un client</option>
                                            <option value="1">SC Alpha Tech SRL</option>
                                        </select>
                                    </div>

                                    <div class="help-text">Selectați un client din lista predefinită configurată în
                                        admin</div>
                                    <div class="alert alert-danger mt-2 alert-box d-none" id="clientError"></div>
                                    <div class="alert alert-success mt-2 alert-box d-none" id="clientSuccess"></div>
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

                            <!-- 6. Seria Facturii -->
                            <div class="form-section">
                                <h3 class="section-title">6. Seria Facturii</h3>

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
                                        <div class="d-flex flex-wrap gap-2" id="serieSuggestions"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Numărul Facturii -->
                            <div class="form-section">
                                <h3 class="section-title">7. Numărul Facturii</h3>

                                <div class="mb-4">
                                    <label for="numar_factura" class="form-label">
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
                                        <div class="d-flex flex-wrap gap-2" id="numarSuggestions"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h3 class="section-title">8. Email</h3>
                                <input type="email" class="form-control" id="email" name="email_colaborator" placeholder="Ex: john.doe@example.com">
                            </div>    
                            <!-- 9. Upload Timesheet -->
                            <div class="form-section">
                                <h3 class="section-title">9. Încărcare Timesheet</h3>

                                <div class="mb-3">
                                    <label for="timesheet-upload" class="form-label">
                                        Incarcare Timesheet <span class="text-danger">*</span>
                                    </label>

                                    <div class="upload-area border rounded p-4 text-center" style="cursor: pointer;"
                                        onclick="document.getElementById('timesheet-upload').click()">
                                        <input type="file" class="form-control d-none" id="timesheet-upload"
                                            name="timesheet_file" accept=".pdf, application/pdf" required>

                                        <div class="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5 class="mb-2">Trageți și plasați fișierul aici</h5>
                                            <p class="text-muted mb-2">sau</p>
                                            <button type="button" class="btn btn-custom">
                                                <i class="fas fa-folder-open me-2"></i> Selectați fișier
                                            </button>
                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Format: PDF Only | Max: 10MB
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="file-preview" class="mt-3"></div>
                                    <div id="file-error" class="text-danger small mt-2"></div>
                                </div>
                            </div>

                            <!-- Buton Final -->
                            <div class="form-section">
                                <div class="mt-2 text-center">
                                    <button type="submit" class="btn btn-lg btn-success w-100" id="finalSubmitBtn"
                                        disabled>
                                        <i class="bi bi-send-check me-2"></i>Trimite Toate Datele
                                    </button>
                                    <div class="help-text mt-2">Acest buton va fi activat după validarea tuturor
                                        câmpurilor</div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const billingMonth = document.getElementById("billingMonth");
            const fullName = document.getElementById("fullName");
            const daysReserved = document.getElementById("daysReserved");
            const zileFacturate = document.getElementById("zile_facturate");
            const clientSelect = document.getElementById("nume-client");
            const serieFactura = document.getElementById("serie-factura");
            const numarFactura = document.getElementById("numar_factura");
            const fileInput = document.getElementById("timesheet-upload");
            const finalBtn = document.getElementById("finalSubmitBtn");

            finalBtn.disabled = true;

            /* ------------------ UTILS ------------------ */

            function showError(input, boxId, message) {
                input.classList.remove("is-valid");
                input.classList.add("is-invalid");
                const box = document.getElementById(boxId);
                box.innerText = message;
                box.classList.remove("d-none");
            }

            function showSuccess(input, boxId) {
                input.classList.remove("is-invalid");
                input.classList.add("is-valid");
                const box = document.getElementById(boxId);
                box.innerText = "Valid";
                box.classList.remove("d-none");
            }

            /* ------------------ BUTON FINAL ------------------ */

            function updateSubmit() {
                const valid =
                    billingMonth.classList.contains("is-valid") &&
                    fullName.classList.contains("is-valid") &&
                    daysReserved.classList.contains("is-valid") &&
                    zileFacturate.classList.contains("is-valid") &&
                    serieFactura.classList.contains("is-valid") &&
                    numarFactura.classList.contains("is-valid") &&
                    clientSelect.value !== "" &&
                    fileInput.files.length > 0;

                finalBtn.disabled = !valid;
            }

            /* ------------------ DATA LUNA ------------------ */

            billingMonth.addEventListener("change", function () {
                const value = this.value;

                if (!value) {
                    showError(this, "billingError", "Selectati o data");
                    return;
                }

                const date = new Date(value);
                const year = date.getFullYear();
                const month = date.getMonth();
                const lastDay = new Date(year, month + 1, 0).getDate();

                if (date.getDate() !== lastDay) {
                    showError(this, "billingError", "Data trebuie sa fie ultima zi din luna");
                } else {
                    showSuccess(this, "billingSuccess");
                }

                updateSubmit();
            });

            /* ------------------ NUME PRENUME ------------------ */

            fullName.addEventListener("input", function () {
                this.value = this.value
                    .toLowerCase()
                    .replace(/\b\w/g, l => l.toUpperCase());

                document.getElementById("charCount").innerText = this.value.length + " caractere";
            });

            fullName.addEventListener("blur", function () {
                const regex = /^[A-Za-z ]+$/;
                const words = this.value.trim().split(/\s+/);

                if (!regex.test(this.value) || words.length < 2) {
                    showError(this, "nameError", "Introdu nume si prenume corecte");
                } else {
                    showSuccess(this, "nameSuccess");
                }

                updateSubmit();
            });

            /* ------------------ ZILE REZERVATE ------------------ */

            daysReserved.addEventListener("blur", function () {
                const regex = /^\d+(\.\d{1,2})?$/;

                if (!regex.test(this.value) || parseFloat(this.value) <= 0) {
                    showError(this, "daysError", "Valoare invalida");
                } else {
                    showSuccess(this, "daysSuccess");
                }

                updateSubmit();
            });

            /* ------------------ ZILE FACTURATE ------------------ */

            zileFacturate.addEventListener("blur", function () {
                const rezervate = parseFloat(daysReserved.value);
                const facturate = parseFloat(this.value);
                const regex = /^\d+(\.\d{1,2})?$/;

                if (!regex.test(this.value) || facturate <= 0) {
                    showError(this, "zileFacturateError", "Valoare invalida");
                } else if (facturate > rezervate) {
                    showError(this, "zileFacturateError", "Nu poate depasi zilele rezervate");
                } else {
                    showSuccess(this, "zileFacturateSuccess");
                }

                updateSubmit();
            });

            /* ------------------ CLIENT ------------------ */

            clientSelect.addEventListener("change", function () {
                if (this.value === "") {
                    document.getElementById("clientError").innerText = "Selectati client";
                    document.getElementById("clientError").classList.remove("d-none");
                } else {
                    document.getElementById("clientSuccess").innerText = "Client selectat";
                    document.getElementById("clientSuccess").classList.remove("d-none");
                }

                updateSubmit();
            });

            /* ------------------ SERIE FACTURA ------------------ */

            serieFactura.addEventListener("blur", function () {
                if (this.value.length === 0 || this.value.length > 10) {
                    showError(this, "serieFacturaError", "Maxim 10 caractere");
                } else {
                    showSuccess(this, "serieFacturaSuccess");
                }

                updateSubmit();
            });

            /* ------------------ NUMAR FACTURA ------------------ */

            numarFactura.addEventListener("blur", function () {
                const num = parseInt(this.value);

                if (isNaN(num) || num <= 0) {
                    showError(this, "numarFacturaError", "Numar invalid");
                } else {
                    showSuccess(this, "numarFacturaSuccess");
                }

                updateSubmit();
            });

            /* ------------------ UPLOAD TIMESHEET ------------------ */

            fileInput.addEventListener("change", function () {
                const file = this.files[0];

                if (!file) return;

                if (file.type !== "application/pdf") {
                    document.getElementById("file-error").innerText = "Se accepta doar PDF";
                    this.value = "";
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    document.getElementById("file-error").innerText = "Maxim 10MB";
                    this.value = "";
                    return;
                }

                const regex = /^(0[1-9]|1[0-2])_\d{4}\.pdf$/i;

                if (!regex.test(file.name)) {
                    document.getElementById("file-error").innerText = "Format nume invalid (ex: 06_2025.pdf)";
                    this.value = "";
                    return;
                }

                document.getElementById("file-error").innerText = "";
                document.getElementById("file-preview").innerHTML =
                    "<div class='alert alert-success'>Fisier incarcat: " + file.name + "</div>";

                updateSubmit();
            });
            // Alert Bootstrap
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-4';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Succes!</strong> Toate datele au fost trimise cu succes.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                document.querySelector('.p-4').prepend(alertDiv);
        });

    </script>
</body>

</html>