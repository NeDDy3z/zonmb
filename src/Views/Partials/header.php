<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Oficiální stránka organizace neslyšících v Mladé Boleslavy">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/header_footer.css">
    <link rel="stylesheet" type="text/css" href="assets/css/page.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"/>
    <title><?php echo $title ?? 'ZONMB'; ?></title>
</head>
<body>
<header>
    <div>
        <nav>
            <a href="."><img src="/assets/images/logo-wide.png" alt="ZONMB"></a>
            <ul>
                <li><a href="./news">Novinky</a></li>
                <li><a href="./user"><?php echo $username ?? 'Uživatel'; ?></a></li>
            </ul>
        </nav>
    </div>
</header>

    
