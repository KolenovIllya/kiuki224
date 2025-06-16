<?php
$start = $_POST['start_date'];
$end = $_POST['end_date'];

$time = $_POST['time'];
$browser = $_POST['browser'];
$location = $_POST['location'];

$dsn = "mysql:host=MySQL-8.2;dbname=lb_pdo_netstat";
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);

    // 🔄 Оновлення 3-го рядка
    $logStmt = $pdo->prepare("
        UPDATE client_request_info
        SET time_req = ?, browser_client = ?, location_home_client = ?
        WHERE id = 2
    ");
    $logStmt->execute([$time, $browser, $location]);

    // Основний запит
    $stmt = $pdo->prepare("
        SELECT s.*, c.name 
        FROM seanse s 
        JOIN client c ON s.fid_client = c.id_client 
        WHERE s.start >= ? AND s.stop <= ?
    ");
    $stmt->execute([$start, $end]);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>Клієнт</th>
            <th>ID Сеансу</th>
            <th>Початок</th>
            <th>Кінець</th>
            <th>Вхідний трафік</th>
            <th>Вихідний трафік</th>
          </tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['id_seanse']}</td>";
        echo "<td>{$row['start']}</td>";
        echo "<td>{$row['stop']}</td>";
        echo "<td>{$row['in_traffic']}</td>";
        echo "<td>{$row['out_traffic']}</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "Помилка з'єднання: " . $e->getMessage();
}
?>
