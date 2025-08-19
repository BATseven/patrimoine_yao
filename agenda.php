<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = 'Utilisateur';
try {
    $user_query = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    if ($user_data = $user_result->fetch_assoc()) {
        $user_name = $user_data['full_name'] ?: 'Utilisateur';
    }
    $user_result->free();
    $user_query->close();
} catch (mysqli_sql_exception $e) {
    error_log("Erreur SQL dans agenda.php : " . $e->getMessage());
    $user_name = 'Utilisateur';
}

// Gestion de l'ajout d'événement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $event_date = $_POST['event_date'] ?? date('Y-m-d');
    $event_title = trim($_POST['event_title']);
    if (!empty($event_title)) {
        try {
            $stmt = $conn->prepare("INSERT INTO events (user_id, event_date, event_title) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $event_date, $event_title);
            $stmt->execute();
            $stmt->close();
            // Rafraîchir la page pour afficher le nouvel événement
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log("Erreur lors de l'ajout d'événement : " . $e->getMessage());
        }
    }
}

// Récupérer les événements pour le mois affiché
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year = 2025; // Année actuelle selon le système
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$first_day = date('N', strtotime("$current_year-$current_month-01")) - 1; // 0 (Lun) à 6 (Dim)

$events = [];
try {
    $stmt = $conn->prepare("SELECT event_date, event_title FROM events WHERE user_id = ? AND MONTH(event_date) = ? AND YEAR(event_date) = ?");
    $stmt->bind_param("iii", $user_id, $current_month, $current_year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[$row['event_date']][] = $row['event_title'];
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log("Erreur lors de la récupération des événements : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Patrimoine Plus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        .agenda-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .day {
            padding: 10px;
            text-align: center;
            background: #f1f5f9;
            border-radius: 5px;
            min-height: 100px;
            position: relative;
        }
        .day-name {
            font-weight: bold;
            color: #1e3a8a;
        }
        .event {
            font-size: 0.8em;
            color: #dc3545;
            margin-top: 5px;
        }
        .event-input {
            margin-top: 20px;
        }
        .event-input input {
            width: 70%;
            margin-right: 10px;
        }
        .event-input button {
            background-color: #1e3a8a;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
        }
        .event-input button:hover {
            background-color: #152e6f;
        }
        .empty-day {
            background: #e9ecef;
            min-height: 50px;
        }
    </style>
</head>
<body>
    <div class="agenda-container">
        <h2 class="text-center mb-4">Agenda - Bienvenue, <?php echo htmlspecialchars($user_name); ?></h2>
        <div class="calendar-header">
            <button class="btn btn-secondary" onclick="changeMonth(<?php echo $current_month > 1 ? $current_month - 1 : 12; ?>)"><i class="fas fa-chevron-left"></i> Précédent</button>
            <h4><?php echo date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year)); ?></h4>
            <button class="btn btn-secondary" onclick="changeMonth(<?php echo $current_month < 12 ? $current_month + 1 : 1; ?>)">Suivant <i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-days">
            <?php
            $day_names = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            foreach ($day_names as $day_name) {
                echo "<div class='day'><span class='day-name'>$day_name</span></div>";
            }
            for ($i = 0; $i < $first_day; $i++) {
                echo "<div class='day empty-day'></div>";
            }
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = sprintf("%04d-%02d-%02d", $current_year, $current_month, $day);
                echo "<div class='day'>";
                echo "<span class='day-name'>$day</span>";
                if (isset($events[$date])) {
                    foreach ($events[$date] as $event) {
                        echo "<div class='event'>$event</div>";
                    }
                }
                echo "</div>";
            }
            ?>
        </div>
        <div class="event-input">
            <form method="post">
                <input type="date" name="event_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                <input type="text" name="event_title" class="form-control" placeholder="Ajouter un événement..." required>
                <button type="submit" name="add_event" class="btn">Ajouter</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeMonth(month) {
            window.location.href = '?month=' + month;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>