<?php

?>
<main>
    <h1>User</h1>
    <p>Welcome to the user page</p>
    <p>Here you can see your user information</p>
    <p>Username: <?php echo $user->getUsername(); ?></p>
    <p>Email: <?php echo $user->getEmail(); ?></p>
    <p>Role: <?php echo $user->getRole(); ?></p>
    <p>Created at: <?php echo $user->getCreatedAt(); ?></p>
    <a href="./logout">Logout</a>
</main>