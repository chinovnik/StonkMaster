<!--
  CSC 226
  Final Project
  Stonkmaster, the Stock Tracker
  by Roman Gelman, Julia Seleznyov, & Nicholas Palermo
-->

<?php

$servername = "localhost";
$username = "root";
$password = "88888888";
$dbname = "stonkmaster";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/index.css" />
    <title>Stonkmaster</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
  </head>

  <!-- animated loading page -->
  <script>
      $(window).on("load", function(){
        $(".loader").fadeOut("slow");
      });

      if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
      }

      function goHome() {
          window.location.replace('../index.php')
      }
    </script>

  <body>
    <!-- Pretty animated preloader that fades out -->
    <div class="loader">
      <img class="logo" src="/logo.png" alt="logo.png" />
    </div>

    <!-- Page header with logo and title -->
    <div class="header">
      <div class="logo-container">
        <img style="cursor: pointer;" onclick="goHome()" class="logo" src="/logo.png" alt="logo.png" />
        <h1 style="cursor: pointer;" onclick="goHome()">Stonkmaster</h1>
      </div>

      <!-- Watchlist -->
      <div class="watchlist">
        <h2>Watchlist</h2>
        <?php
          $sql = "SELECT symbol from watchlist";
          $result = $conn->query($sql);
          $watchlist = array();

          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              array_push($watchlist, $row['symbol']);
            }
            echo '<table class="watchlist-table">
                    <thead>
                      <tr>
                      <th>Symbol</th>
                      <th>Previous Close</th>
                      <th>Price</th>
                      <th>% Change</th>
                      <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>';
            foreach($watchlist as $symbol) {
              $url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?region=US&lang=en-US&includePrePost=false&interval=1h&useYfid=true&range=1d";
              $stock_data = json_decode(file_get_contents($url), true);
              $price = number_format($stock_data['chart']['result'][0]['meta']['regularMarketPrice'], 2);
              $previousClose = number_format($stock_data['chart']['result'][0]['meta']['previousClose'], 2);
              $change = @number_format(round(($price/$previousClose * 100) - 100, 2), 2);
              $sign = ($change > 0) ? "+" : "";
              $color = ($change > 0) ? "#50c878" : (($change == 0) ? "#aaaaaa" : "red");
              echo '<tr><form action="index.php" method="post">
                    <input class="hidden" name="del-symbol" type="text" value="' . $symbol . '"/>
                    <td>$' . $symbol . '</td><td>$' . $previousClose . '</td><td>$' . $price . '</td><td style="color: ' . $color . '; text-align: right;">' . $sign . $change . '%</td><td><a href="quote.php/?symbol=' . $symbol . '">🔎</a> | <input style="background-color: #202020; border: none; cursor: pointer;" type="submit" value="🗑️"/></td></form></tr>';
            }
            echo "</tbody></table>";
          } else {
            echo "<p>Your watchlist is empty.</p>";
          }
        ?>
        <div class="add-to-watchlist">
          <p>Add a stock to your watchlist:</p>
          <form action="index.php" method="post">
            <input class="watchlist-input" type="text" name="add-symbol" placeholder="GME"/>
            <input class="watchlist-add" type="submit" name="add" value="♡"/>
          </form>
          <?php
            if (isset($_POST['add-symbol'])) {
              $symbol = strtoupper($_POST['add-symbol']);
              $url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?region=US&lang=en-US&includePrePost=false&interval=1h&useYfid=true&range=1d";
              $stock_data = @json_decode(file_get_contents($url), true);
              if (isset($stock_data['chart']['result'][0]['meta']['regularMarketPrice'])) {
                $sql="INSERT INTO watchlist (symbol) VALUES ('{$symbol}')";
                if (mysqli_query($conn, $sql)) {
                  echo "<div class=\"watchlist-add-success\">Symbol <b>$". $symbol ."</b> has been added to your watchlist! <a href=\"index.php\">Refresh</a> to update.</div>";
                } else {
                  echo "<div class=\"watchlist-add-fail\">Symbol <b>$" . $symbol . "</b> is already in your watchlist</div>";
                }
              } else {
                echo "<div class=\"watchlist-add-fail\">Symbol <b>$" . $symbol . "</b> could not found</div>";
              }
            }
            elseif (isset($_POST['del-symbol'])) {
              $delsymbol = strtoupper($_POST['del-symbol']);
              $sql = "DELETE FROM `watchlist` WHERE `watchlist`.`symbol` = '{$delsymbol}'";
              if (mysqli_query($conn, $sql)) {
                echo "<div class=\"watchlist-add-success\">Symbol <b>$" . $delsymbol . "</b> has been removed from your watchlist. <a href=\"index.php\">Refresh</a> to update.</div>";
              } else {
                echo "<div class=\"watchlist-add-fail\">Symbol <b>$" . $delsymbol . "</b> could not be removed</div>";
              }
            }
          ?>
        </div>
      </div>
    </div>
  </body>
</html>