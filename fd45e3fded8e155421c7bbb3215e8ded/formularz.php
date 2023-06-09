<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="pl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta charset="utf-8">
    <title>Dulkowski</title>
    <script>
        function validateForm() {
            var waluta1 = document.getElementById("waluta").value;
            var waluta2 = document.getElementById("waluta2").value;

            if (waluta1 === waluta2) {
                alert("Nie możesz wybrać tej samej waluty w polu 'Oczekiwana waluta'.");
                return false;
            }

            return true;
        }
    </script>

    <link rel="stylesheet" type="text/css" href="form.css">
</head>

<body>

    <a href='index.php'>Strona główna</a>
    <a href='dokonane_transakcje.php'>Dokonane transakcje</a>
    <h1>Formularz z danymi z bazy danych</h1>
    <?php
    class KonwersjaWalut
    {
        private $connect;

        public function __construct()
        {
            $servername = "localhost.serwer2226107.home.pl";
            $username = "36742534_nbp2";
            $password = "Kozak1324??";
            $dbname = "36742534_nbp2";
            $this->connect = mysqli_connect($servername, $username, $password, $dbname);
            $this->connect->set_charset("utf8");
            if (!$this->connect) {
                die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
            }
        }

        public function przewalutowanie($waluta_posiadana, $waluta_oczekiwana, $kwota)
        {
            // Pobierz kurs waluty posiadaną
            $kurs_waluty_posiadanej_query = "SELECT `kurs` FROM `aktualne_kursy` WHERE `nazwa waluty` = ?";
            $stmt_posiadanej = $this->connect->prepare($kurs_waluty_posiadanej_query);
            $stmt_posiadanej->bind_param("s", $waluta_posiadana);
            $stmt_posiadanej->execute();
            $result_posiadanej = $stmt_posiadanej->get_result();

            if ($result_posiadanej && $result_posiadanej->num_rows > 0) {
                $row_posiadanej = $result_posiadanej->fetch_assoc();
                $kurs_waluty_posiadanej = $row_posiadanej['kurs'];
                echo "Kurs waluty posiadanej: $kurs_waluty_posiadanej<br>";
            } else {
                die("Błąd podczas pobierania kursu waluty posiadanej: " . mysqli_error($this->connect));
            }

            $stmt_posiadanej->close();

            // Przewalutowanie na złotówki
            $kwota_zl = round($kwota * $kurs_waluty_posiadanej, 2);
            // echo "Przewalutowana kwota na złotówki: $kwota_zl zł<br>";

            // Pobierz kurs waluty oczekiwanej
            $kurs_waluty_oczekiwanej_query = "SELECT `kurs` FROM `aktualne_kursy` WHERE `nazwa waluty` = ?";
            $stmt_oczekiwanej = $this->connect->prepare($kurs_waluty_oczekiwanej_query);
            $stmt_oczekiwanej->bind_param("s", $waluta_oczekiwana);
            $stmt_oczekiwanej->execute();
            $result_oczekiwanej = $stmt_oczekiwanej->get_result();

            if ($result_oczekiwanej && $result_oczekiwanej->num_rows > 0) {
                $row_oczekiwanej = $result_oczekiwanej->fetch_assoc();
                $kurs_waluty_oczekiwanej = $row_oczekiwanej['kurs'];
                echo "Kurs waluty oczekiwanej: $kurs_waluty_oczekiwanej<br>";
            } else {
                die("Błąd podczas pobierania kursu waluty oczekiwanej: " . mysqli_error($this->connect));
            }

            $stmt_oczekiwanej->close();

            // Przewalutowanie na walutę oczekiwaną
            $kwota_oczekiwana = round($kwota_zl / $kurs_waluty_oczekiwanej, 2);
            echo "Kwota w walucie oczekiwanej: $kwota_oczekiwana $waluta_oczekiwana<br>";

            // Zapisz operację w bazie danych
            $insert_query = "INSERT INTO `przewalutowania` (`nazwa_waluty_posiadanej`, `nazwa_waluty_oczekiwanej`, `kwota_posiadana`, `kwota_po_przewalutowaniu`) VALUES (?, ?, ?, ?)";
            $stmt_insert = $this->connect->prepare($insert_query);
            $stmt_insert->bind_param("ssdd", $waluta_posiadana, $waluta_oczekiwana, $kwota, $kwota_oczekiwana);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        public function __destruct()
        {
            mysqli_close($this->connect);
        }
    }

    $konwersja = new KonwersjaWalut();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $selectedCurrency1 = $_POST['waluta'] ?? '';
        $selectedCurrency2 = $_POST['waluta2'] ?? '';
        $kwota = $_POST['kwota'] ?? '';

        if (!empty($selectedCurrency1) && !empty($selectedCurrency2) && !empty($kwota)) {
            echo "<h2>Wynik przewalutowania:</h2>";
            echo "<div>";
            $konwersja->przewalutowanie($selectedCurrency1, $selectedCurrency2, $kwota);
            echo "</div>";
        }
    }

    $servername = "localhost.serwer2226107.home.pl";
    $username = "36742534_nbp2";
    $password = "Kozak1324??";
    $dbname = "36742534_nbp2";
    $connect = mysqli_connect($servername, $username, $password, $dbname);
    $connect->set_charset("utf8");
    if (!$connect) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }

    $query = "SELECT DISTINCT `nazwa waluty` FROM `aktualne_kursy`";
    $result = mysqli_query($connect, $query);

    $rates = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $currency = $row['nazwa waluty'];
            $rates[] = $currency;
        }
    } else {
        die("Błąd przy pobieraniu danych z bazy: " . mysqli_error($connect));
    }

    mysqli_close($connect);
    ?>
    <form method="POST" onsubmit="return validateForm()">
        <label for="waluta">Posiadana waluta:</label>
        <select id="waluta" name="waluta">
            <option value="">Wybierz walutę</option>
            <?php
            mysqli_set_charset($connect, "utf8");
            foreach ($rates as $currency) {
                if ($currency !== $selectedCurrency2) {
                    echo "<option value='$currency'>$currency</option>";
                }
            }
            ?>
        </select>
        <br>
        <label for="kwota">Kwota</label>
        <input type="number" step="0.01" min="0.01" id="kwota" name="kwota" placeholder="Wprowadź kwotę" required>
        <br>
        <label for="waluta2">Oczekiwana waluta</label>
        <select id="waluta2" name="waluta2">
            <option value="">Wybierz walutę</option>
            <?php
            mysqli_set_charset($connect, "utf8");
            foreach ($rates as $currency) {
                if ($currency !== $selectedCurrency2) {
                    echo "<option value='$currency'>$currency</option>";
                }
            }
            ?>
        </select>
        <br>
        <input type="submit" value="Wyślij">
    </form>
</body>

</html>