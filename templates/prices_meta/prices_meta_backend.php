<?php
    // Template in backend for prices input
?>
<div>
    <div style="display: flex;gap: 2rem;width: 100%;align-items: center; padding: 1rem 0;">
        <div style="width: 100px;">
            <label for="cpp_hourly_price">Hourly Price:</label>
        </div>
        <div style="flex-grow: 1;">
            <input type="number" step="0.01" name="cpp_hourly_price" id="cpp_hourly_price" value="<?= esc_attr($hourly_price); ?>" style="width: 80%;">
        </div>
    </div>

    <div style="display: flex;gap: 2rem;width: 100%;align-items: center; padding-bottom: 1rem">
        <div style="width: 100px;">
            <label for="cpp_daily_price">Daily Price:</label>
        </div>
        <div style="flex-grow: 1;">
            <input type="number" step="0.01" name="cpp_daily_price" id="cpp_daily_price" value="<?= esc_attr($daily_price); ?>" style="width: 80%;">
        </div>
    </div>

    <div style="display: flex;gap: 2rem;width: 100%;align-items: center; padding-bottom: 1rem">
        <div style="width: 100px;">
            <label for="cpp_weekly_price">Weekly Price:</label>
        </div>
        <div style="flex-grow: 1;">
            <input type="number" step="0.01" name="cpp_weekly_price" id="cpp_weekly_price" value="<?= esc_attr($weekly_price); ?>" style="width: 80%;">
        </div>
    </div>
</div>