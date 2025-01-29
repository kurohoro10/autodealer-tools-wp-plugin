<?php
    // Template for frontend input for prices
?>

<div>
    <label for="cpp_hourly_price">Hourly Price:</label>
    <input type="number" step="0.01" name="cpp_hourly_price" id="cpp_hourly_price" value="<?= esc_attr($hourly_price); ?>">
</div>

<div>
    <label for="cpp_daily_price">Daily Price:</label>
    <input type="number" step="0.01" name="cpp_daily_price" id="cpp_daily_price" value="<?= esc_attr($daily_price); ?>">
</div>

<div>
    <label for="cpp_weekly_price">Weekly Price:</label>
    <input type="number" step="0.01" name="cpp_weekly_price" id="cpp_weekly_price" value="<?= esc_attr($weekly_price); ?>">
</div>