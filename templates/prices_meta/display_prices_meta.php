<?php
    // Template to display the prices in a shortcode
?>
<div class="cpp-prices">
    <div><strong>Per Hour:</strong> <?= esc_html($price_per_hour); ?> </div>
    <div><strong>Per Day:</strong> <?= esc_html($price_per_day); ?> </div>
    <div><strong>Per Week:</strong> <?= esc_html($price_per_week); ?> </div>
</div>