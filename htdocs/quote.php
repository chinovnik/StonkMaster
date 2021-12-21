<!--
  CSC 226
  Final Project
  Stonkmaster, the Stock Tracker
  by Roman Gelman, Julia Seleznyov, & Nicholas Palermo
-->

<?php
if (isset($_GET['symbol'])) {
    $symbol = $_GET['symbol'];
}

$url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?region=US&lang=en-US&includePrePost=false&interval=1h&useYfid=true&range=1d";
$stock_data = @json_decode(file_get_contents($url), true);
$price = number_format($stock_data['chart']['result'][0]['meta']['regularMarketPrice'], 4);
$previousClose = number_format($stock_data['chart']['result'][0]['meta']['previousClose'], 2);
$open = number_format($stock_data['chart']['result'][0]['indicators']['quote'][0]['open'][0], 2);
$change = @number_format(round(($price/$previousClose * 100) - 100, 2), 2);
$sign = ($change > 0) ? "+" : "";
$color = ($change > 0) ? "#50c878" : (($change == 0) ? "#aaaaaa" : "red");
$volume = 0;
foreach ($stock_data['chart']['result'][0]['indicators']['quote'][0]['volume'] as $vol) {
    $volume += $vol;
}
$volume = number_format($volume, 0, "", ",");
?>

<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="/quote.css" />
        <title>Stonkmaster</title>
        <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    </head>

    <!-- animated loading page -->
    <script>
      $(window).on("load", function(){
        $(".loader").fadeOut("slow");
      });

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
    </div>

    <div class="stock-chart">
        <?php echo '<h2>$' . $symbol . ': $' . $price . "\t" . $sign . '<span style="color: ' . $color . '">' . $change . '%</h2>';?>
        <!-- TradingView Widget BEGIN -->
        <div class="tradingview-widget-container">
            <div id="tradingview_6c2bf"></div>
            <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
            <script type="text/javascript">
                new TradingView.widget (
                {
                    "width": 514,
                    "height": 610,
                    <?php echo '"symbol": "NASDAQ:' . $symbol . '"'?>,
                    "interval": "D",
                    "timezone": "Etc/UTC",
                    "theme": "dark",
                    "style": "1",
                    "locale": "en",
                    "toolbar_bg": "#f1f3f6",
                    "enable_publishing": false,
                    "allow_symbol_change": true,
                    "container_id": "tradingview_6c2bf"
                });
            </script>
        </div>
        <!-- TradingView Widget END -->
    </div>
    <div class="stock-info">
        <p> Open<?php echo '<span>$' . $open . '</span>'; ?></p>
        <br/>
        <p> Previous Close:<?php echo '<span>$' . $previousClose . '</span>'; ?></p>
        <br/>
        <p> Volume: <?php echo '<span>' . $volume . '</span>'; ?></p>
        <br/>
        <p> <a style="text-decoration: underline; color: #50c878;" href="../index.php">Back to watchlist</a></p>
    </div>
  </body>
</html>