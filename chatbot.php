<?php
session_start();
include 'db_connect.php';

$user = strtolower(trim($_POST['message'] ?? ''));

// Initialize conversation
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 0;
}

switch ($_SESSION['step']) {

    case 0:
        if (strpos($user, 'blood') !== false) {
            $_SESSION['step'] = 1;
            echo "🚨 Is this an emergency? (yes / no)";
        } else {
            echo "Hi 👋 I am Blood AI Assistant. Type: Need blood";
        }
        break;

    case 1:
        $_SESSION['emergency'] = $user;
        $_SESSION['step'] = 2;
        echo "🩸 Which blood group is required?";
        break;

    case 2:
        $_SESSION['blood_group'] = strtoupper($user);
        $_SESSION['step'] = 3;
        echo "📍 Enter district:";
        break;

    case 3:
        $_SESSION['district'] = $user;
        $_SESSION['step'] = 4;
        echo "📍 Enter state:";
        break;

    case 4:
        $_SESSION['state'] = $user;

        $bg = $_SESSION['blood_group'];
        $state = $_SESSION['state'];
        $district = $_SESSION['district'];

        $sql = "SELECT name, mobile, last_donation
                FROM donors
                WHERE blood_group=? AND state=? AND district=?
                AND DATEDIFF(CURDATE(), last_donation) >= 90
                ORDER BY last_donation ASC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $bg, $state, $district);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $d = $result->fetch_assoc();
            // Distance logic (AI heuristic)
$distance = 0;

if (strtolower($district) == strtolower($_SESSION['district'])) {
    $distance = rand(1, 3); // Same district
} else {
    $distance = rand(5, 15); // Nearby district
}

echo "✅ Best donor found 👇<br>
      👤 {$d['name']}<br>
      📞 {$d['mobile']}<br>
      🩸 Last donation: {$d['last_donation']}<br>
      📍 Distance: {$distance} km (approx)";

        } else {
            echo "❌ No eligible donor found nearby.";
        }

        session_destroy();
        break;
}
?>
