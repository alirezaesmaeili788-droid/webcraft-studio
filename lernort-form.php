<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>StudySpot | Ort anmelden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="spots-body">

    <!-- Navbar -->
<?php include "navbar.php"; ?>

    <!-- End Navbar -->



    <div class="container py-5" style="max-width: 900px;">
        <h1 class="h3 fw-bold mb-3 text-center">Lernort / Café bei StudySpot anmelden</h1>
        <p class="small text-muted text-center mb-4">
            Fülle dieses Formular aus, wenn dein Café, deine Bibliothek oder ein anderer Lernort
            auf StudySpot erscheinen soll. Wir prüfen alle Angaben und melden uns bei dir.
        </p>

        <!-- Formular beginnt hier -->
        <form method="post" action="PLACEHOLDER.php">

            <!-- Art des Ortes -->
            <div class="mb-3">
                <label class="form-label">Art des Ortes *</label>
                <select name="place_type" class="form-select" required>
                    <option value="">Bitte auswählen…</option>
                    <option value="cafe">Café</option>
                    <option value="bibliothek">Bibliothek</option>
                    <option value="coworking">Coworking / Lernraum</option>
                    <option value="sonstiges">Sonstiges</option>
                </select>
            </div>

            <!-- Name + Ansprechpartner -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name des Ortes *</label>
                    <input type="text" name="place_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ansprechperson (optional)</label>
                    <input type="text" name="contact_person" class="form-control">
                </div>
            </div>

            <!-- Kontaktdaten -->
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">E-Mail-Adresse *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefonnummer (optional)</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>

            <!-- Adresse -->
            <div class="mt-3 mb-1">
                <label class="form-label">Adresse *</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="street" class="form-control" placeholder="Straße und Hausnummer" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="zip" class="form-control" placeholder="PLZ" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="city" class="form-control" placeholder="Ort" required>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <input type="number" name="district" min="1" max="23" class="form-control" placeholder="Bezirk (z.B. 6)">
                </div>
                <div class="col-md-8">
                    <input type="text" name="website" class="form-control" placeholder="Website / Instagram (optional)">
                </div>
            </div>

            <!-- Öffnungszeiten -->
            <div class="mt-3">
                <label class="form-label">Öffnungszeiten *</label>
                <textarea name="hours" class="form-control" rows="3"
                    placeholder="z.B. Mo–Fr 08:00–20:00, Sa 10:00–18:00" required></textarea>
            </div>

            <!-- Geeignet für -->
            <div class="mt-3">
                <label class="form-label">Wofür ist der Ort gut geeignet?</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="suitable[]" value="einzelarbeit" id="s1">
                            <label class="form-check-label" for="s1">Einzelarbeit / Lernen allein</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="suitable[]" value="gruppenarbeit" id="s2">
                            <label class="form-check-label" for="s2">Gruppenarbeit / Lerngruppe</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="suitable[]" value="lange_sitzen" id="s3">
                            <label class="form-check-label" for="s3">Längeres Sitzen / Lernen möglich</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="suitable[]" value="ruhig" id="s4">
                            <label class="form-check-label" for="s4">Eher ruhig</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="suitable[]" value="hintergrundmusik" id="s5">
                            <label class="form-check-label" for="s5">Mit Hintergrundmusik</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="suitable[]" value="barrierefrei" id="s6">
                            <label class="form-check-label" for="s6">Barrierefreier Zugang</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ausstattung -->
            <div class="mt-3">
                <label class="form-label">Ausstattung</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="features[]" value="wifi" id="f1">
                            <label class="form-check-label" for="f1">WLAN vorhanden</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="features[]" value="steckdosen" id="f2">
                            <label class="form-check-label" for="f2">Viele Steckdosen</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="features[]" value="reservierung" id="f3">
                            <label class="form-check-label" for="f3">Reservierung möglich</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="features[]" value="essen" id="f4">
                            <label class="form-check-label" for="f4">Essen / Snacks</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="features[]" value="getraenke" id="f5">
                            <label class="form-check-label" for="f5">Kaffee / Getränke</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="features[]" value="outdoor" id="f6">
                            <label class="form-check-label" for="f6">Außenbereich / Terrasse</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kurzbeschreibung -->
            <div class="mt-3">
                <label class="form-label">Kurzbeschreibung des Ortes *</label>
                <textarea name="description" class="form-control" rows="4"
                    placeholder="Was macht euren Ort besonders? Warum ist er gut zum Lernen geeignet?"
                    required></textarea>
            </div>

            <!-- Sonstiges -->
            <div class="mt-3">
                <label class="form-label">Zusätzliche Hinweise (optional)</label>
                <textarea name="notes" class="form-control" rows="3"
                    placeholder="z.B. Mindestkonsum, Zeitlimit, spezielle Regeln, bevorzugte Zielgruppe …"></textarea>
            </div>

            <!-- Einwilligung -->
            <div class="mt-3 mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="consent" id="consent" required>
                    <label class="form-check-label small" for="consent">
                        Ich bestätige, dass ich berechtigt bin, diesen Ort anzumelden, und bin einverstanden,
                        dass StudySpot meine Angaben zur Prüfung und Kontaktaufnahme speichert.
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-success w-100">
                Anfrage absenden
            </button>

        </form>
        <!-- Formular endet hier -->

    </div>

</body>

</html>