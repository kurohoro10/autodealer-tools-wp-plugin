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
                <div class="sort-my-listings-form sortby-form ms-auto">
                    <div class="orderby-wrapper d-flex align-items-center">
                        <span class="text-sort">Sort by: </span>
                        <div class="cpp_sort_dropdown">
                            <button id="sortby">
                                <span class="cpp_sort_btn_label">Default</span>
                                <span><i class="fa fa-caret-down"></i></span>
                            </button>
                            <div class="cpp_sort_dropdown_content hidden">
                                <ul>
                                    <li><a href="javascript:void(0)" class="cpp_default cpp_active">Default</a></li>
                                    <li><a href="javascript:void(0)" class="cpp_newest">Newest</a></li>
                                    <li><a href="javascript:void(0)" class="cpp_oldest">Oldest</a></li>
                                </ul>
                            </div>
                        </div>
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
                <div class="loading loading01">
                    <span>L</span>
                    <span>O</span>
                    <span>A</span>
                    <span>D</span>
                    <span>I</span>
                    <span>N</span>
                    <span>G</span>
                </div>
            </div>
            <div id="pagination"></div>
        </div>
    </div>