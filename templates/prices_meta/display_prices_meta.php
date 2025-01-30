<?php
    // Template to display the prices in a shortcode
?>
<div class="cpp_prices">
    <div>
        <div class="d_flex">
            <div class="currency">RM</div>
            <div class="cpp_price"><?= esc_html($price_per_hour); ?></div>
            <div class="cpp_dropdown">
                <button class="cpp_dropbtn">
                    Per Hour &nbsp;
                    <span class="caret_icon">
                        <i class="fa fa-angle-down"></i>
                    </span>
                </button>

                <div id="cpp_myDropdown" class="cpp_dropdown-content hidden">
                    <div class="cpp_dropdown_item">
                        <span class="hidden cpp_rate_amount"><?= esc_html($price_per_hour); ?></span>
                        <span class="cpp_rate">Per Hour</span>
                    </div>
                    <div class="cpp_dropdown_item">
                        <span class="hidden cpp_rate_amount"><?= esc_html($price_per_day); ?></span>
                        <span class="cpp_rate">Per Day</span>
                    </div>
                    <div class="cpp_dropdown_item">
                        <span class="hidden cpp_rate_amount"><?= esc_html($price_per_week); ?></span>
                        <span class="cpp_rate">Per Week</span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>