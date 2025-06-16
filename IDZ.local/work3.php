<?php
$time = $_POST['time'];
$browser = $_POST['browser'];
$location = $_POST['location'];

$dsn = "mysql:host=MySQL-8.2;dbname=lb_pdo_netstat";
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);

    // Вивід клієнтів з від’ємним балансом
    $stmt = $pdo->query("SELECT * FROM client WHERE balance < 0");

    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>ID</th>
            <th>Ім'я</th>
            <th>Логін</th>
            <th>IP</th>
            <th>Баланс</th>
          </tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id_client']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['login']}</td>";
        echo "<td>{$row['ip']}</td>";
        echo "<td style='color:red;'>{$row['balance']}</td>";
        echo "</tr>";
    }

    echo "</table>";

    $update = $pdo->prepare("
        UPDATE client_request_info
        SET time_req = ?, browser_client = ?, location_home_client = ?
        WHERE id = 3
    ");
    $update->execute([$time, $browser, $location]);

} catch (PDOException $e) {
    echo "Помилка з'єднання: " . $e->getMessage();
}
?>
