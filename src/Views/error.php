<?php
$error_code = (http_response_code()) ? http_response_code() : 404;

$error_dictionary = [
    301 => 'Stránka byla přesunuta',
    302 => 'Stránka byla přesunuta',
    400 => 'Chybný požadavek',
    401 => 'Neautorizovaný přístup',
    403 => 'Přístup odepřen',
    404 => 'Stránka nebyla nalezena',
];
?>

<main>
    <h1>Error <?php echo $error_code; ?></h1>
    <p><b><?php echo $error_dictionary[(int)$error_code]; ?></b></p>
    <p>Vrátit se zpět na <a href="../">domovskou stránku</a></p>
</main>