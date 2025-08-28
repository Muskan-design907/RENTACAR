<?php
// index.php
require 'db.php';
 
// Fetch distinct filter options from cars table
$carTypes = $pdo->query("SELECT DISTINCT car_type FROM cars")->fetchAll(PDO::FETCH_COLUMN);
$fuelTypes = $pdo->query("SELECT DISTINCT fuel_type FROM cars")->fetchAll(PDO::FETCH_COLUMN);
$brands = $pdo->query("SELECT DISTINCT brand FROM cars")->fetchAll(PDO::FETCH_COLUMN);
 
// Fetch featured cars (top 4 by rating)
$featuredCars = $pdo->query("SELECT * FROM cars ORDER BY rating DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>RentACar - Home</title>
<style>
  /* Reset and basic styling */
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
  body { background: #f5f6fa; color: #333; }
  header { background: #007bff; padding: 20px; color: white; text-align: center; }
  h1 { margin-bottom: 10px; }
  main { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
  form.search-form {
    background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1);
    display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;
  }
  form.search-form > div {
    flex: 1 1 150px;
    min-width: 150px;
    display: flex; flex-direction: column;
  }
  label { margin-bottom: 6px; font-weight: bold; }
  input[type=text], input[type=date], select {
    padding: 8px; border: 1px solid #ccc; border-radius: 4px;
    font-size: 1rem;
  }
  button {
    background: #007bff; color: white; border: none;
    padding: 12px 25px; border-radius: 4px; font-size: 1rem;
    cursor: pointer; transition: background-color 0.3s ease;
  }
  button:hover { background: #0056b3; }
 
  section.filters {
    margin-top: 30px;
    background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1);
  }
  section.filters h2 {
    margin-bottom: 15px; font-size: 1.3rem;
  }
  .filter-group {
    display: flex; flex-wrap: wrap; gap: 15px;
  }
  .filter-group > div {
    flex: 1 1 150px;
  }
 
  section.featured-cars {
    margin-top: 40px;
  }
  section.featured-cars h2 {
    margin-bottom: 20px; font-size: 1.5rem; text-align: center;
  }
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
    form.search-form {
      flex-direction: column;
      align-items: stretch;
    }
    form.search-form > div {
      flex: none;
      width: 100%;
    }
  }
</style>
</head>
<body>
<header>
  <h1>RentACar</h1>
  <p>Find and book your rental car easily</p>
</header>
<main>
 
  <form class="search-form" action="car-listing.php" method="GET">
    <div>
      <label for="pickup_location">Pickup Location</label>
      <input type="text" id="pickup_location" name="pickup_location" placeholder="Enter city or airport" required />
    </div>
    <div>
      <label for="start_date">Rental Start Date</label>
      <input type="date" id="start_date" name="start_date" required />
    </div>
    <div>
      <label for="end_date">Return Date</label>
      <input type="date" id="end_date" name="end_date" required />
    </div>
    <div>
      <button type="submit">Search Cars</button>
    </div>
  </form>
 
  <section class="filters" aria-label="Filters">
    <h2>Filter Options (Example Filters)</h2>
    <div class="filter-group">
      <div>
        <label for="car_type_filter">Car Type</label>
        <select id="car_type_filter" disabled title="Filters will work on listing page">
          <option>Various types available on listing</option>
          <?php foreach ($carTypes as $type): ?>
          <option><?=htmlspecialchars($type)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="fuel_type_filter">Fuel Type</label>
        <select id="fuel_type_filter" disabled title="Filters will work on listing page">
          <option>Various fuel types on listing page</option>
          <?php foreach ($fuelTypes as $fuel): ?>
          <option><?=htmlspecialchars($fuel)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="brand_filter">Brand</label>
        <select id="brand_filter" disabled title="Filters will work on listing page">
          <option>Various brands on listing page</option>
          <?php foreach ($brands as $brand): ?>
          <option><?=htmlspecialchars($brand)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="price_range_filter">Price Range (per day)</label>
        <select id="price_range_filter" disabled title="Filters work on listing page">
          <option>Filters work on listing page</option>
          <option>0 - 50</option>
          <option>51 - 100</option>
          <option>101 - 200</option>
          <option>200+</option>
        </select>
      </div>
    </div>
    <small style="color:#888;">* Filters will be active on car listing page after search.</small>
  </section>
 
  <section class="featured-cars" aria-label="Featured Rental Cars">
    <h2>Featured Rental Cars</h2>
    <div class="cars-grid">
      <?php foreach($featuredCars as $car): ?>
      <div class="car-card">
        <img src="<?=htmlspecialchars($car['image'])?>" alt="<?=htmlspecialchars($car['brand'] . ' ' . $car['model'])?>" />
        <div class="car-info">
          <h3><?=htmlspecialchars($car['brand'] . ' ' . $car['model'])?></h3>
          <p class="description"><?=htmlspecialchars(substr($car['description'],0,80))?>...</p>
          <div class="price">$<?=number_format($car['price_per_day'],2)?> / day</div>
          <div class="rating">⭐ <?=number_format($car['rating'],1)?> / 5</div>
          <a class="book-btn" href="car-listing.php?pickup_location=&start_date=&end_date=&car_id=<?=intval($car['id'])?>">Book Now</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
 
</main>
</body>
</html>
 
