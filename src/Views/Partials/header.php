<?php
use Helpers\UrlHelper;

$username = isset($_SESSION['user_data']) ? htmlspecialchars($_SESSION['user_data']->getUsername()) : null;

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Oficiální stránka organizace neslyšících v Mladé Boleslavy">
    <link rel="icon" type="image/x-icon" href="<?= UrlHelper::baseUrl('assets/images/favicon.ico') ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <link rel="stylesheet" href="<?= UrlHelper::baseUrl('assets/css/header_footer.css') ?>">
    <link rel="stylesheet" href="<?= UrlHelper::baseUrl('assets/css/style.css') ?>">
    <title><?= $title ?? 'ZONMB'; ?></title>
</head>
<body>
<header>
    <div>
        <nav>
            <a href="<?= UrlHelper::baseUrl('/') ?>"><img src="<?= UrlHelper::baseUrl('assets/images/logo-wide.png') ?>" alt="ZONMB"></a>
            <ul>
                <li><a href="<?= UrlHelper::baseUrl('news') ?>">Novinky</a></li>
                <li><a href="<?= UrlHelper::baseUrl('users/'. $username) ?>"><?= $username ?? 'Uživatel' ?></a></li>
            </ul>
        </nav>
    </div>
</header>
    
