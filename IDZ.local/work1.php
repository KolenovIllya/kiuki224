<?php
header("Content-Type: application/javascript");

$client_id = $_GET['client_id'];
$time = $_GET['time'];
$browser = $_GET['browser'];
$location = $_GET['location'];
$callback = $_GET['callback'] ?? 'callback';

$dsn = "mysql:host=MySQL-8.2;dbname=lb_pdo_netstat";
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);

    // Оновлення журналу
    $logStmt = $pdo->prepare("
        UPDATE client_request_info
        SET time_req = ?, browser_client = ?, location_home_client = ?
        WHERE id = 1
    ");
    $logStmt->execute([$time, $browser, $location]);

    // Отримання сеансів клієнта
    $stmt = $pdo->prepare("SELECT * FROM seanse WHERE fid_client = ?");
    $stmt->execute([$client_id]);

    // Формування HTML-таблиці як рядка
    $html = "<table border='1' cellpadding='5'>";
    $html .= "<tr><th>ID Сеансу</th><th>Початок</th><th>Кінець</th><th>Вхідний трафік</th><th>Вихідний трафік</th></tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= "<tr>";
        $html .= "<td>{$row['id_seanse']}</td>";
        $html .= "<td>{$row['start']}</td>";
        $html .= "<td>{$row['stop']}</td>";
        $html .= "<td>{$row['in_traffic']}</td>";
        $html .= "<td>{$row['out_traffic']}</td>";
        $html .= "</tr>";
    }

    $html .= "</table>";

    // Повернення результату у форматі JSONP
    echo "$callback(" . json_encode($html) . ");";

} catch (PDOException $e) {
    echo "$callback(" . json_encode("Помилка з'єднання: " . $e->getMessage()) . ");";
}
?>
