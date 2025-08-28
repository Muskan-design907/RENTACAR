<?php
// booking.php
require 'db.php';
 
function sanitize($str) {
    return htmlspecialchars(trim($str));
}
 
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;
$pickup_location = isset($_GET['pickup_location']) ? sanitize($_GET['pickup_location']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
 
if (!$car_id || !$pickup_location || !$start_date || !$end_date) {
    die("Missing booking details. Please start your booking from the search page.<br><a href='index.php'>Go back</a>");
}
 
// Fetch car details
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);
 
if (!$car) {
    die("Car not found.<br><a href='index.php'>Go back</a>");
}
 
// Calculate rental days
$start_dt = new DateTime($start_date);
$end_dt = new DateTime($end_date);
$interval = $start_dt->diff($end_dt);
$days = max($interval->days, 1); // minimum 1 day
 
$errors = [];
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
 
    if (!$customer_name) $errors[] = "Please enter your name.";
    if (!$customer_email) $errors[] = "Please enter your email.";
    elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
 
    if (empty($errors)) {
        // Insert booking into database
        $total_price = $days * $car['price_per_day'];
        $insert = $pdo->prepare("INSERT INTO bookings (car_id, customer_name, customer_email, pickup_location, rental_start, rental_end, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success_db = $insert->execute([
            $car_id,
            $customer_name,
            $customer_email,
            $pickup_location,
            $start_date,
            $end_date,
            $total_price
        ]);
 
        if ($success_db) {
            $success = "Thank you, " . sanitize($customer_name) . "! Your booking for the <strong>" . sanitize($car['brand'] . ' ' . $car['model']) . "</strong> from <strong>" . sanitize($start_date) . "</strong> to <strong>" . sanitize($end_date) . "</strong> has been confirmed. Total price: <strong>$" . number_format($total_price, 2) . "</strong>.";
        } else {
            $errors[] = "Failed to save your booking. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>RentACar - Booking</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 0; color: #333;}
  header { background: #007bff; padding: 20px; color: white; text-align: center;}
  main { max-width: 600px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
  h1 { margin-bottom: 15px; }
  .car-summary {
    display: flex; gap: 15px; margin-bottom: 20px;
    align-items: center;
  }
  .car-summary img {
    width: 140px; height: 90px; object-fit: cover; border-radius: 6px;
  }
  .car-summary div {
    flex-grow: 1;
  }
  form label {
    display: block; font-weight: bold; margin: 12px 0 6px;
  }
  form input[type=text], form input[type=email] {
    width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;
  }
  form button {
    margin-top: 20px;
    background: #28a745; color: white; border: none;
    padding: 12px; border-radius: 6px; font-size: 1rem; cursor: pointer;
    width: 100%;
    transition: background-color 0.3s ease;
  }
  form button:hover {
    background: #1e7e34;
  }
  .message {
    margin-bottom: 20px;
    padding: 12px 15px;
    border-radius: 5px;
  }
  .error {
    background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
  }
  .success {
    background: #d4edda; color: #155724; border: 1px solid #c3e6cb;
  }
  a.back-link {
    display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none;
  }
  a.back-link:hover { text-decoration: underline; }
 
  @media (max-width: 480px) {
    main { margin: 15px; padding: 15px; }
    .car-summary { flex-direction: column; align-items: flex-start; }
    .car-summary img { width: 100%; height: auto; }
  }
</style>
</head>
<body>
<header>
  <h1>Car Booking</h1>
</header>
<main>
  <div class="car-summary">
    <img src="<?=htmlspecialchars($car['image'])?>" alt="<?=htmlspecialchars($car['brand'].' '.$car['model'])?>">
    <div>
      <h2><?=htmlspecialchars($car['brand'].' '.$car['model'])?></h2>
      <p><?=htmlspecialchars($car['description'])?></p>
      <p><strong>Rental Dates:</strong> <?=htmlspecialchars($start_date)?> to <?=htmlspecialchars($end_date)?> (<?= $days ?> days)</p>
      <p><strong>Pickup Location:</strong> <?=htmlspecialchars($pickup_location)?></p>
      <p><strong>Price per day:</strong> $<?=number_format($car['price_per_day'], 2)?></p>
      <p><strong>Total Price:</strong> $<?=number_format($car['price_per_day'] * $days, 2)?></p>
    </div>
  </div>
 
  <?php if ($errors): ?>
    <div class="message error">
      <ul>
      <?php foreach ($errors as $error): ?>
        <li><?=sanitize($error)?></li>
      <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
 
  <?php if ($success): ?>
    <div class="message success">
      <?= $success ?>
    </div>
    <a class="back-link" href="index.php">Book Another Car</a>
  <?php else: ?>
  <form method="POST" action="">
    <label for="customer_name">Your Name</label>
    <input type="text" id="customer_name" name="customer_name" required value="<?=isset($_POST['customer_name']) ? sanitize($_POST['customer_name']) : ''?>" />
 
    <label for="customer_email">Your Email</label>
    <input type="email" id="customer_email" name="customer_email" required value="<?=isset($_POST['customer_email']) ? sanitize($_POST['customer_email']) : ''?>" />
 
    <button type="submit">Confirm Booking</button>
  </form>
  <?php endif; ?>
</main>
</body>
</html>
 
