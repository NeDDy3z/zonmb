<main>
    <section>
        <h2>Administrace uživatelů</h2>
        <table class="users-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Uživatelské jméno</th>
                <th>Celé jméno</th>
                <th>Role</th>
                <th>Datum registrace</th>
            </tr>
            </thead>
            <tbody>
            <?php
                if (empty($users)) {
                    echo '<tr><td colspan="5">Žádní uživatelé nenalezeni</td></tr>';
                } else {
                    foreach ($users as $user) {
                        echo '<tr>
                                <td>'. htmlspecialchars($user->getId()) .'</td>
                                <td>'. htmlspecialchars($user->getUsername()) .'</td>
                                <td>'. htmlspecialchars($user->getFullname()) .'</td>
                                <td>'. htmlspecialchars($user->getRole()) .'</td>
                                <td>'. htmlspecialchars($user->getCreatedAt()) .'</td>
                            </tr>';
                    }
                }
            ?>
            </tbody>
        </table>
    </section>
    <section>

    </section>
</main>