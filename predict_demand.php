<?php
include 'db_connect.php';
$blood_group = $_GET['blood_group'] ?? 'O+';

$sql = "
SELECT 
    MONTH(request_date) AS month,
    COUNT(*) AS total
FROM blood_requests
WHERE blood_group = ?
GROUP BY MONTH(request_date)
ORDER BY MONTH(request_date)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blood_group);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $months[] = date("M", mktime(0, 0, 0, $row['month'], 1));
    $counts[] = $row['total'];
}

/* ===== ML: Moving Average + Trend ===== */
$prediction = 0;

$totalMonths = count($counts);

if ($totalMonths >= 3) {

    $last = $counts[$totalMonths - 1];
    $prev = $counts[$totalMonths - 2];
    $prev2 = $counts[$totalMonths - 3];

    // 3-month moving average
    $movingAvg = ($last + $prev + $prev2) / 3;

    // growth trend
    $trend = $last - $prev;

    $prediction = max(1, round($movingAvg + $trend));

} elseif ($totalMonths >= 2) {

    $last = $counts[$totalMonths - 1];
    $prev = $counts[$totalMonths - 2];

    $prediction = max(1, round($last + ($last - $prev)));

} elseif ($totalMonths == 1) {

    $prediction = $counts[0];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Blood Demand Prediction</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            text-align: center;
            margin: 0;
        }

        h2 {
            margin-top: 40px;
            color: #b30000;
        }

        .chart-card {
            width: 65%;
            max-width: 750px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        canvas {
            height: 320px !important;
        }

        select {
            padding: 8px 12px;
            font-size: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .prediction-box {
            margin-top: 25px;
            font-size: 18px;
            background: #ffe6e6;
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
        }

        .ml-note {
            color: gray;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<h2>📊 Predicted Blood Demand (Next Month)</h2>

<form method="GET" style="margin-top:20px;">
    <label><b>Select Blood Group:</b></label>
    <select name="blood_group" onchange="this.form.submit()">
        <?php
        $groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
        foreach ($groups as $g) {
            $sel = ($g == $blood_group) ? 'selected' : '';
            echo "<option value='$g' $sel>$g</option>";
        }
        ?>
    </select>
</form>

<div class="chart-card">
    <canvas id="demandChart"></canvas>

    <div class="prediction-box">
        🔮 <b>Predicted <?= htmlspecialchars($blood_group) ?> Demand:</b>
        <?= $prediction ?> requests
    </div>

    <div class="ml-note">
        ML Model: Time-Series Trend Analysis (Growth Based Prediction)
    </div>
</div>

<script>
const ctx = document.getElementById('demandChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Past Demand (<?= $blood_group ?>)',
            data: <?= json_encode($counts) ?>,
            borderColor: '#e60000',
            backgroundColor: 'rgba(230,0,0,0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#e60000',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: "#eee"
                },
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

</body>
</html>
