<?php
// car-listing.php
require 'db.php';

function sanitize($str) {
    return htmlspecialchars(trim($str));
}

// Get and sanitize inputs
$pickup_location = isset($_GET['pickup_location']) ? sanitize($_GET['pickup_location']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$car_type = isset($_GET['car_type']) ? sanitize($_GET['car_type']) : '';
$fuel_type = isset($_GET['fuel_type']) ? sanitize($_GET['fuel_type']) : '';
$brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$price_range = isset($_GET['price_range']) ? sanitize($_GET['price_range']) : '';

$sort_by = isset($_GET['sort_by']) ? sanitize($_GET['sort_by']) : 'price_asc';

// Validate required inputs
$errors = [];
if (!$pickup_location) $errors[] = "Pickup location is required.";
if (!$start_date) $errors[] = "Rental start date is required.";
if (!$end_date) $errors[] = "Return date is required.";
elseif ($end_date < $start_date) $errors[] = "Return date must be after start date.";

if (!empty($errors)) {
    echo "<h2>Error:</h2><ul>";
    foreach ($errors as $e) echo "<li>" . $e . "</li>";
    echo "</ul><a href='index.php'>Go Back</a>";
    exit;
}

// Fetch distinct filter options for filter form
$carTypes = $pdo->query("SELECT DISTINCT car_type FROM cars")->fetchAll(PDO::FETCH_COLUMN);
$fuelTypes = $pdo->query("SELECT DISTINCT fuel_type FROM cars")->fetchAll(PDO::FETCH_COLUMN);
$brands = $pdo->query("SELECT DISTINCT brand FROM cars")->fetchAll(PDO::FETCH_COLUMN);

// Build SQL query with filters
$sql = "SELECT * FROM cars WHERE 1=1 ";
$params = [];

// Example: you can add location-based filtering if you had location data per car
// For now, location filtering is just a required search param but doesn't filter cars

// Filters
if ($car_type) {
    $sql .= " AND car_type = :car_type ";
    $params[':car_type'] = $car_type;
}
if ($fuel_type) {
    $sql .= " AND fuel_type = :fuel_type ";
    $params[':fuel_type'] = $fuel_type;
}
if ($brand) {
    $sql .= " AND brand = :brand ";
    $params[':brand'] = $brand;
}
if ($price_range) {
    // Parse price ranges like "0 - 50", "51 - 100", "200+"
    if ($price_range === "200+") {
        $sql .= " AND price_per_day >= 200 ";
    } elseif (preg_match('/(\d+)\s*-\s*(\d+)/', $price_range, $matches)) {
        $min = floatval($matches[1]);
        $max = floatval($matches[2]);
        $sql .= " AND price_per_day BETWEEN :min_price AND :max_price ";
        $params[':min_price'] = $min;
        $params[':max_price'] = $max;
    }
}

// Sorting
$order_sql = " ORDER BY price_per_day ASC ";
if ($sort_by === 'price_desc') $order_sql = " ORDER BY price_per_day DESC ";
elseif ($sort_by === 'rating_desc') $order_sql = " ORDER BY rating DESC ";
elseif ($sort_by === 'rating_asc') $order_sql = " ORDER BY rating ASC ";

$sql .= $order_sql;

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>RentACar - Available Cars</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; color: #333; margin: 0; padding: 0;}
  header { background: #007bff; padding: 20px; color: white; text-align: center;}
  main { max-width: 1200px; margin: 20px auto; padding: 0 15px;}
  h1 { margin-bottom: 10px;}
  .back-link {
    display: inline-block; margin-bottom: 20px;
    color: #007bff; text-decoration: none; font-weight: bold;
  }
  .back-link:hover { text-decoration: underline; }
  form.filter-form {
    background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;
  }
  form.filter-form > div {
    flex: 1 1 150px;
    min-width: 150px;
    display: flex; flex-direction: column;
  }
  label { font-weight: bold; margin-bottom: 6px; }
  select {
    padding: 8px; border: 1px solid #ccc; border-radius: 4px;
    font-size: 1rem;
  }
  button {
    background: #007bff; color: white; border: none;
    padding: 12px 25px; border-radius: 4px; font-size: 1rem;
    cursor: pointer; transition: background-color 0.3s ease;
  }
  button:hover { background: #0056b3; }

  .cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
    gap: 20px;
  }
  .car-card {
    background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
    overflow: hidden; display: flex; flex-direction: column;
    transition: box-shadow 0.3s ease;
  }
  .car-card:hover { box-shadow: 0 0 15px rgba(0,123,255,0.3); }
  .car-card img {
    width: 100%; height: 160px; object-fit: cover;
  }
  .car-info {
    padding: 15px;
    flex-grow: 1;
    display: flex; flex-direction: column; justify-content: space-between;
  }
  .car-info h3 {
    margin-bottom: 8px;
  }
  .car-info p.description {
    flex-grow: 1;
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 10px;
  }
  .car-info .price {
    font-weight: bold; font-size: 1.1rem; color: #007bff;
    margin-bottom: 10px;
  }
  .car-info .rating {
    color: #ffb400;
    margin-bottom: 10px;
  }
  .car-info a.book-btn {
    text-decoration: none;
    background: #28a745;
    color: white;
    padding: 10px 0;
    text-align: center;
    border-radius: 4px;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }
  .car-info a.book-btn:hover {
    background: #1e7e34;
  }

  /* Responsive */
  @media (max-width: 600px) {
    form.filter-form {
      flex-direction: column;
      align-items: stretch;
    }
    form.filter-form > div {
      flex: none;
      width: 100%;
    }
  }
</style>
</head>
<body>
<header>
  <h1>RentACar - Available Cars</h1>
</header>
<main>
  <a class="back-link" href="index.php">&larr; Back to Search</a>

  <form class="filter-form" method="GET" action="car-listing.php" aria-label="Filter available cars">
    <input type="hidden" name="pickup_location" value="<?=htmlspecialchars($pickup_location)?>" />
    <input type="hidden" name="start_date" value="<?=htmlspecialchars($start_date)?>" />
    <input type="hidden" name="end_date" value="<?=htmlspecialchars($end_date)?>" />

    <div>
      <label for="car_type">Car Type</label>
      <select id="car_type" name="car_type">
        <option value="">All Types</option>
        <?php foreach ($carTypes as $type): ?>
        <option value="<?=htmlspecialchars($type)?>" <?= $car_type === $type ? 'selected' : '' ?>><?=htmlspecialchars($type)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="fuel_type">Fuel Type</label>
      <select id="fuel_type" name="fuel_type">
        <option value="">All Fuel Types</option>
        <?php foreach ($fuelTypes as $fuel): ?>
        <option value="<?=htmlspecialchars($fuel)?>" <?= $fuel_type === $fuel ? 'selected' : '' ?>><?=htmlspecialchars($fuel)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="brand">Brand</label>
      <select id="brand" name="brand">
        <option value="">All Brands</option>
        <?php foreach ($brands as $b): ?>
        <option value="<?=htmlspecialchars($b)?>" <?= $brand === $b ? 'selected' : '' ?>><?=htmlspecialchars($b)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="price_range">Price Range (per day)</label>
      <select id="price_range" name="price_range">
        <option value="">All Prices</option>
        <option value="0 - 50" <?= $price_range === '0 - 50' ? 'selected' : '' ?>>0 - 50</option>
        <option value="51 - 100" <?= $price_range === '51 - 100' ? 'selected' : '' ?>>51 - 100</option>
        <option value="101 - 200" <?= $price_range === '101 - 200' ? 'selected' : '' ?>>101 - 200</option>
        <option value="200+" <?= $price_range === '200+' ? 'selected' : '' ?>>200+</option>
      </select>
    </div>
    <div>
      <label for="sort_by">Sort By</label>
      <select id="sort_by" name="sort_by">
        <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="rating_desc" <?= $sort_by === 'rating_desc' ? 'selected' : '' ?>>Rating: High to Low</option>
        <option value="rating_asc" <?= $sort_by === 'rating_asc' ? 'selected' : '' ?>>Rating: Low to High</option>
      </select>
    </div>
    <div style="min-width:120px;">
      <button type="submit">Apply Filters</button>
    </div>
  </form>

  <?php if (count($cars) === 0): ?>
    <p>No cars found matching your criteria.</p>
  <?php else: ?>
    <div class="cars-grid" role="list">
      <?php foreach($cars as $car): ?>
      <article class="car-card" role="listitem" tabindex="0">
        <img src="<?=htmlspecialchars($car['image'])?>" alt="<?=htmlspecialchars($car['brand'].' '.$car['model'])?>" />
        <div class="car-info">
          <h3><?=htmlspecialchars($car['brand'].' '.$car['model'])?></h3>
          <p class="description"><?=htmlspecialchars(substr($car['description'],0,100))?>...</p>
          <div class="price">$<?=number_format($car['price_per_day'],2)?> / day</div>
          <div class="rating">⭐ <?=number_format($car['rating'],1)?> / 5</div>
          <a class="book-btn" href="booking.php?car_id=<?=intval($car['id'])?>&pickup_location=<?=urlencode($pickup_location)?>&start_date=<?=urlencode($start_date)?>&end_date=<?=urlencode($end_date)?>">
            Book Now
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
