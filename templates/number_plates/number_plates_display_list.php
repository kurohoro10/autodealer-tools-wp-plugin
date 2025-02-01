<?php
    // Template for displaying the list of number plates in dashboard
?>

<h2 class="title-profile">Number Plates</h2>
    <div class="box-white-dashboard">
        <div class="space-bottom-30">
            <!-- Search and sort table -->
            <div class="d-md-flex align-items-center top-dashboard-search">
                <!-- Search in list -->
                <div class="search-my-listings-form widget-search search-listings-form">
                    <div class="d-flex align-items-center">
                        <button class="search-submit btn btn-search">
                            <i class="flaticon-search"></i>
                        </button>
                        <input placeholder="Search ..." id="cpp_np_search" class="form-control" type="text" name="search" value="">
                    </div>
                </div>

                <!-- Sort lists -->
                <!-- <div class="sort-my-listings-form sortby-form ms-auto">
                    <div class="orderby-wrapper d-flex align-items-center">
                        <span class="text-sort">Sort by:</span>
                        <select name="sortby" id="sortby" class="orderby select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                <option value="menu_order">Default</option>
                                <option value="newest" selected="selected">Newest</option>
                                <option value="oldest">Oldest</option>
                        </select>
                    </div>
                </div> -->
                <div class="sort-my-listings-form sortby-form ms-auto">
                    <div class="orderby-wrapper d-flex align-items-center">
                        <span class="text-sort">Sort by: </span>
                        <select name="sortby" id="sortby" class="select2-selection select2-selection--single">
                            <option value="default" selected="selected">Default</option>
                            <option value="desc">Newest</option>
                            <option value="asc">Oldest</option>
                        </select>
                        <input type="hidden" name="paged" value="1">
                    </div>
                </div>
            </div>
        </div>

        <!-- Inner -->
        <div class="inner">
            <!-- Inner header -->
            <div class="layout-my-listings d-flex align-items-center header-layout">
                <div class="listing-thumbnail-wrapper d-none d-md-block">
                    Image			
                </div>
                <div class="layout-left d-flex align-items-center inner-info flex-grow-1">
                    <div class="inner-info-left">
                        Information				
                    </div>
                    <div class="d-none d-md-block">
                        Status				
                    </div>
                    <div class="d-none d-md-block">
                        View				
                    </div>
                    <div>
                        Action				
                    </div>
                </div>
            </div>

            <!-- Inner item -->
            <div class="number_plates_listing">
                <p class="loading">Loading number plates please wait ...</p>
            </div>
            <div id="pagination"></div>
        </div>
    </div>