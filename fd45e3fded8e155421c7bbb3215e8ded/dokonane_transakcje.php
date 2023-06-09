<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="pl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta charset="utf-8">
    <title>Dulkowski</title>
    <link rel="stylesheet" type="text/css" href="transakcje.css">
</head>

<body>
    <a href='index.php'>Strona główna</a>
    <a href='formularz.php'>Formularz</a>
    <?php
    class Przewalutowania
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

        public function wyswietlPrzewalutowania($page = 1, $resultsPerPage = 10)
        {
            // Obliczanie offsetu
            $offset = ($page - 1) * $resultsPerPage;

            // Pobieranie historycznych przewalutowań z tabeli przewalutowania z uwzględnieniem paginacji
            $query = "SELECT * FROM `przewalutowania` LIMIT $offset, $resultsPerPage";
            $result = $this->connect->query($query);

            if ($result->num_rows > 0) {
                echo '<div id="result-container">';

                while ($row = $result->fetch_assoc()) {
                    echo '<div>';
                    echo '<strong>Nazwa waluty posiadanej:</strong> ' . $row['nazwa_waluty_posiadanej'] . '<br>';
                    echo '<strong>Nazwa waluty oczekiwanej:</strong> ' . $row['nazwa_waluty_oczekiwanej'] . '<br>';
                    echo '<strong>Kwota posiadana:</strong> ' . $row['kwota_posiadana'] . '<br>';
                    echo '<strong>Kwota po przewalutowaniu:</strong> ' . $row['kwota_po_przewalutowaniu'] . '<br>';
                    echo '</div>';
                }

                echo '</div>';

                // Wyświetlanie nawigacji stron
                $totalResults = $this->getTotalResults();
                $totalPages = ceil($totalResults / $resultsPerPage);

                if ($totalPages > 1) {
                    echo '<div class="pagination">';


                    for ($i = 1; $i <= $totalPages; $i++) {
                        if ($i == $page) {
                            echo '<a class="active" href="?page=' . $i . '">' . $i . '</a>';
                        } else {
                            echo '<a href="?page=' . $i . '">' . $i . '</a>';
                        }
                    }



                    echo '</div>';
                }
            } else {
                echo 'Brak wyników.';
            }
        }

        public function getTotalResults()
        {
            $query = "SELECT COUNT(*) as total FROM `przewalutowania`";
            $result = $this->connect->query($query);
            $row = $result->fetch_assoc();
            return $row['total'];
        }
    }

    // Inicjalizacja obiektu klasy Przewalutowania
    $przewalutowania = new Przewalutowania();

    // Pobranie numeru strony z parametru GET
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

    // Wywołanie metody wyswietlPrzewalutowania z podaną numerem strony
    $przewalutowania->wyswietlPrzewalutowania($currentPage);
    ?>
</body>

</html>