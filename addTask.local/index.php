<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Моя історія подорожі</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="">
    </script>
    <style>
        #gpx-output {
            white-space: pre-wrap;
            background: #f4f4f4;
            padding: 10px;
            margin-top: 20px;
            border: 1px solid #ccc;
            font-family: monospace;
        }
    </style>
</head>
<body>

<div id="map" style="height: 500px; width: 800px;"></div>

<label for="locations">Ваші координати:</label>
<select id="locations">
    <option value="">-- Оберіть --</option>
</select>

<div id="gpx-output"></div>

<button onclick="clearStorage()">Очистити всі дані</button>

<script>
    let map, marker, circle;
    let points = JSON.parse(localStorage.getItem('geoPoints')) || [];

    function formatDateTime(date) {
        return date.toLocaleString("uk-UA", {
            day: "2-digit", month: "long", year: "numeric",
            hour: "2-digit", minute: "2-digit", second: "2-digit"
        });
    }

    function addPoint(lat, lng) {
    const timestamp = new Date().toLocaleString("sv-SE").replace(" ", " T");

    const select = document.getElementById("locations");
    const option = document.createElement("option");
    option.value = `${lat},${lng},${timestamp}`;
    option.text = `${lat.toFixed(5)}, ${lng.toFixed(5)} (${formatDateTime(new Date(timestamp))})`;
    select.appendChild(option);

    points.push({ lat, lng, timestamp });
    localStorage.setItem('geoPoints', JSON.stringify(points));
    }

    function clearStorage() {
        localStorage.removeItem('geoPoints');
        points = [];
        document.getElementById("locations").innerHTML = '<option value="">-- Оберіть --</option>';
        document.getElementById("gpx-output").textContent = "";
        alert("Дані очищено.");
    }

    function success(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;

        map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        marker = L.marker([lat, lng]).addTo(map);
        circle = L.circle([lat, lng], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 500
        }).addTo(map);

        addPoint(lat, lng);
    }

    navigator.geolocation.getCurrentPosition(success);

    setInterval(() => {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            map.setView([lat, lng]);

            map.removeLayer(marker);
            map.removeLayer(circle);

            marker = L.marker([lat, lng]).addTo(map);
            circle = L.circle([lat, lng], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 500
            }).addTo(map);

            addPoint(lat, lng);
        });
    }, 60 * 1000); // 1 хвилина

    window.addEventListener('load', () => {
        points.forEach(p => {
            const select = document.getElementById("locations");
            const option = document.createElement("option");
            option.value = `${p.lat},${p.lng},${p.timestamp}`;
            option.text = `${parseFloat(p.lat).toFixed(5)}, ${parseFloat(p.lng).toFixed(5)} (${formatDateTime(new Date(p.timestamp))})`;
            select.appendChild(option);
        });
    });

    document.getElementById("locations").addEventListener("change", function () {
        const selected = this.value;
        if (!selected) {
            document.getElementById("gpx-output").textContent = "";
            return;
        }

        const selectedPoints = Array.from(this.options)
            .filter(opt => opt.value)
            .map(opt => {
                const [lat, lng, timestamp] = opt.value.split(",");
                return { lat, lng, timestamp };
            });

        const gpx =
`<gpx version="1.1" creator="Моя історія подорожі" xmlns="http://www.topografix.com/GPX/1/1">
     <trk>
        <name>Шлях користувача</name>
         <trkseg>
    ${selectedPoints.map(p => `      <trkpt lat="${p.lat}" lon="${p.lng}">\n        <time>${p.timestamp}</time>\n      </trkpt>`).join("\n")}
        </trkseg>
    </trk>
</gpx>`;

        document.getElementById("gpx-output").textContent = gpx;
    });
    
</script>

</body>
</html>
