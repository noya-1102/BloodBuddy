<?php
include 'db_connect.php';

$mode = isset($_POST['emergency']) ? "emergency" : "normal";
// AI emergency mode

$blood_group = $_POST['blood_group'] ?? '';
$state       = $_POST['state'] ?? '';
$district    = $_POST['district'] ?? '';

/* ===== STEP 1: FETCH DONORS ===== */
$sql = "SELECT name, state, district, mobile, age, last_donation,
               hemoglobin, weight, bp, diabetes, surgery, infection
        FROM donors
        WHERE blood_group = ?
          AND state = ?
          AND district = ?
          AND availability = 'yes'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $blood_group, $state, $district);
$stmt->execute();
$result = $stmt->get_result();

/* ===== STEP 2: AI PRIORITY FUNCTION ===== */
function calculatePriority($distance, $daysSinceDonation, $age, $mode,
                           $hemoglobin, $weight, $bp, $diabetes, $surgery, $infection) {

    $score = 0;

    // Distance weight
    if ($distance <= 5) $score += 40;
    else if ($distance <= 10) $score += 30;
    else if ($distance <= 20) $score += 20;
    else $score += 10;

    // Donation gap
    if ($daysSinceDonation >= 180) $score += 30;
    else if ($daysSinceDonation >= 120) $score += 20;
    else $score += 10;

    // Age suitability
    if ($age >= 18 && $age <= 35) $score += 20;
    else if ($age <= 45) $score += 15;
    else $score += 10;

    // 🔬 HEALTH CHECK BONUS
    if ($hemoglobin >= 12.5) $score += 15;
    if ($weight >= 50) $score += 10;
    if ($bp == "normal") $score += 10;
    if ($diabetes == "no") $score += 10;
    if ($surgery == "no") $score += 10;
    if ($infection == "no") $score += 15;

    // Emergency boost
    if ($mode === "emergency") $score += 20;

    return $score;
}


/* ===== STEP 3: SCORE ALL DONORS ===== */
$donors = [];
$bestDonor = null;
$highestScore = 0;

while ($row = $result->fetch_assoc()) {

    $row['distance'] = rand(1, 15); // demo distance (AI simulation)

    $days = (strtotime(date("Y-m-d")) - strtotime($row['last_donation'])) / (60*60*24);

    $row['priority_score'] = calculatePriority(
    $row['distance'],
    $days,
    $row['age'],
    $mode,
    $row['hemoglobin'],
    $row['weight'],
    $row['bp'],
    $row['diabetes'],
    $row['surgery'],
    $row['infection']
);


    if ($row['priority_score'] > $highestScore) {
        $highestScore = $row['priority_score'];
        $bestDonor = $row;
    }

    $donors[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Donor Selection</title>
    <style>
        body { font-family: Arial; background:#f4f6f8; text-align:center; }
        h2 { color:#d9534f; }

        table {
            width: 85%;
            margin: 20px auto;
            border-collapse: collapse;
            background:#fff;
        }
        th, td {
            padding: 10px;
            border:1px solid #ddd;
        }
        th {
            background:#d9534f;
            color:#fff;
        }
        tr:nth-child(even) { background:#f2f2f2; }

        .best {
            background:#d4edda !important;
            font-weight:bold;
        }

        .card {
            background:#fff;
            padding:20px;
            max-width:450px;
            margin:20px auto;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,.1);
        }

        a {
            display:inline-block;
            margin:20px;
            padding:10px 20px;
            background:#d9534f;
            color:white;
            text-decoration:none;
            border-radius:5px;
        }
    </style>
</head>

<body>

<h2>🚨 AI Emergency Donor Selection</h2>

<?php if ($bestDonor): ?>
<div class="card">
    <h3>🏆 Best Donor (AI Selected)</h3>
    <p><b>Name:</b> <?= htmlspecialchars($bestDonor['name']) ?></p>
    <p><b>Location:</b> <?= htmlspecialchars($bestDonor['state']." - ".$bestDonor['district']) ?></p>
    <p><b>Age:</b> <?= $bestDonor['age'] ?></p>
    <p><b>Last Donation:</b> <?= $bestDonor['last_donation'] ?></p>
    <p><b>Mobile:</b> <?= $bestDonor['mobile'] ?></p>
    <p><b>AI Priority Score:</b> <?= $bestDonor['priority_score'] ?>/110</p>

    <p style="color:green;">✅ Selected using ML-style emergency priority scoring</p>
</div>
<?php endif; ?>

<?php if (!empty($donors)): ?>
<table>
    <tr>
        <th>Name</th>
        <th>Location</th>
        <th>Age</th>
        <th>Last Donation</th>
        <th>Distance (km)</th>
        <th>Medical Status</th>
        <th>AI Score</th>
    </tr>

<?php foreach ($donors as $d): ?>
<tr class="<?= ($d === $bestDonor) ? 'best' : '' ?>">
    <td><?= htmlspecialchars($d['name']) ?></td>
    <td><?= htmlspecialchars($d['state']." / ".$d['district']) ?></td>
    <td><?= $d['age'] ?></td>
    <td><?= $d['last_donation'] ?></td>
    <td><?= $d['distance'] ?></td>

    <!-- ADD THIS COLUMN -->
    <td>
        <?php 
        if ($d['hemoglobin'] >= 12.5 && 
            $d['infection'] == "no" &&
            $d['diabetes'] == "no" &&
            $d['surgery'] == "no") {
            echo "<span style='color:green; font-weight:bold;'>Eligible</span>";
        } else {
            echo "<span style='color:red; font-weight:bold;'>Risk</span>";
        }
        ?>
    </td>

    <td><?= $d['priority_score'] ?></td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p style="color:red;">❌ No eligible donors found</p>
<?php endif; ?>

<a href="index.php">⬅ Go Back</a>

</body>
</html>

<?php $conn->close(); ?>
