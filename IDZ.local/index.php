<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Клієнти та Сеанси</title>
</head>
<body>
 
   <h2>Оберіть клієнта</h2>
<form id="clientForm">
    <label for="client_id">Клієнт:</label>
    <select name="client_id" id="client_id">
        <option value="">-- Оберіть --</option>
        <?php
            $pdo = new PDO("mysql:host=MySQL-8.2;dbname=lb_pdo_netstat", "root", "");
            $stmt = $pdo->query("SELECT id_client, name FROM client");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id_client']}'>{$row['name']}</option>";
            }
        ?>
    </select>
    <button type="submit">Показати всі сеанси клієнта</button>
</form>

<hr>

<h2>Пошук усіх сеансів за вказаний час</h2>
<form id="dateForm">
    <label for="start_date">З:</label>
    <input type="time" name="start_date" id="start_date" required step="1">

    <label for="end_date">По:</label>
    <input type="time" name="end_date" id="end_date" required step="1">

    <button type="submit">Пошук</button>
</form>

<hr>

<h2>Клієнти з від'ємним балансом</h2>
<form id="negativeBalanceForm">
    <button type="submit">Показати</button>
</form>

<div id="result"></div> 

<script>

function getReadableBrowser(userAgent) {
    if (userAgent.includes("Chrome") && !userAgent.includes("Edg") && !userAgent.includes("OPR")) return "Chrome";
    if (userAgent.includes("Firefox")) return "Firefox";
    if (userAgent.includes("Safari") && !userAgent.includes("Chrome")) return "Safari";
    if (userAgent.includes("Edg")) return "Edge";
    if (userAgent.includes("OPR") || userAgent.includes("Opera")) return "Opera";
    return "Інший";
}

function getFormattedTime() {
    const now = new Date();
    return now.getFullYear() + "-" +
        String(now.getMonth() + 1).padStart(2, '0') + "-" +
        String(now.getDate()).padStart(2, '0') + " " +
        String(now.getHours()).padStart(2, '0') + ":" +
        String(now.getMinutes()).padStart(2, '0') + ":" +
        String(now.getSeconds()).padStart(2, '0');
}


document.getElementById('clientForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const clientId = document.getElementById('client_id').value;
    const browser = getReadableBrowser(navigator.userAgent);
    const time = getFormattedTime();

    navigator.geolocation.getCurrentPosition(function(position) {
        const location = position.coords.latitude + ',' + position.coords.longitude;

        const callbackName = 'handleClientResponse_' + Math.floor(Math.random() * 100000);
        
        window[callbackName] = function(data) {
            document.getElementById('result').innerHTML = data;
            delete window[callbackName];
            script.remove();
        };

        const script = document.createElement('script');
        script.src = `work1.php?client_id=${encodeURIComponent(clientId)}&time=${encodeURIComponent(time)}&browser=${encodeURIComponent(browser)}&location=${encodeURIComponent(location)}&callback=${callbackName}`;
        document.body.appendChild(script);
    });
});


document.getElementById('dateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    navigator.geolocation.getCurrentPosition(function(position) {
        const formData = new FormData();
        formData.append('start_date', document.getElementById('start_date').value);
        formData.append('end_date', document.getElementById('end_date').value);
        formData.append('time', getFormattedTime());
        formData.append('browser', getReadableBrowser(navigator.userAgent));
        formData.append('location', position.coords.latitude + ', ' + position.coords.longitude);

        const ajax = new XMLHttpRequest();
        ajax.onreadystatechange = function() {
            if (ajax.readyState === 4 && ajax.status === 200) {
                document.getElementById('result').innerHTML = ajax.responseText;
            }
        };

        ajax.open("POST", "work2.php", true);
        ajax.send(formData);
    });
});


document.getElementById('negativeBalanceForm').addEventListener('submit', function (e) {
    e.preventDefault();

    navigator.geolocation.getCurrentPosition(function (position) {
        const now = new Date();
        const browserName = getReadableBrowser(navigator.userAgent);
        const location = position.coords.latitude + ', ' + position.coords.longitude;

        const formData = new FormData();
        formData.append('time', getFormattedTime());
        formData.append('browser', browserName);
        formData.append('location', location);

        const ajax = new XMLHttpRequest();
        ajax.onreadystatechange = function () {
            if (ajax.readyState === 4 && ajax.status === 200) {
                document.getElementById('result').innerHTML = ajax.responseText;
            }
        };

        ajax.open("POST", "work3.php", true);
        ajax.send(formData);
        alert("Геолокацію не вдалося отримати: " + error.message);
    });
});


</script>

</body>
</html>
