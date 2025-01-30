document.addEventListener('DOMContentLoaded', () => {
    // Preview featured image before uploading.
    const featured_img = document.getElementById('featured_image');
    const prev_container = document.querySelector('.wp-cardealer-uploaded-file-preview');
    const remove_btn = document.querySelector('.remove_btn_preview');
    const existing_img = document.getElementById('image_preview');

    // Create remove button element in the img
    let remove_btn_preview = document.createElement('a');
        remove_btn_preview.classList.add('wp-cardealer-remove-uploaded-file', 'remove_btn_preview');
        remove_btn_preview.href = '#';
        remove_btn_preview.textContent = '[remove]';
    
    // Create img element inside the preview container
    let img = document.createElement('img');
        img.src = '#';
        img.alt = 'Your image'
        img.setAttribute('id', 'image_preview');

    const check_image_preview = () => {
        if (existing_img) {
            existing_img.remove();
        }
    };

    // Preview image on upload before sending to backend
    if (featured_img && prev_container) {
        featured_img.addEventListener('change', (e) => {
            const file_input = e.target;
            const files = file_input.files;

            if (files && files.length > 0) {
                check_image_preview();
                const file = files[0];
                img.src = URL.createObjectURL(file);
                prev_container.appendChild(remove_btn_preview);
                prev_container.appendChild(img);
            }
        });
    }

    // Remove image from preview
    if (remove_btn_preview) {
        remove_btn_preview.addEventListener('click', (e) => {
            e.preventDefault();
            img.remove();
            featured_img.value = null;
        }); 
    }

    if (remove_btn) {
        remove_btn.addEventListener('click', (e) => {
            e.preventDefault();
            existing_img.remove();
            featured_img.value = null;
        }); 
    }

    // Function for observing the DOM if the existing image exists
    const obs = new MutationObserver(() => {
        const existing_img = document.getElementById('image_preview');
        const featured_image_preview = document.getElementById('remove_featured_image');
        if (!existing_img) {
            if (featured_image_preview) {
                featured_image_preview.value = '1'
            }
        } else {
            if (featured_image_preview) {
                featured_image_preview.value = '0'
            }
        }
    });

    obs.observe(document.body, {childList: true, subtree: true});

    // Function for displaying image in the list
    const check_img_path = (path) => {
        if (path && path.thumbnail && path.title) {
            return `<img src="${path.thumbnail}" alt="${path.title}" />`;
        } else {
            return '';
        }
    };

    // container for displaying the list of number plates in listing page
    const container = document.querySelector('.number_plates_listing');

    // Template to render the list of number plates
    const renderNumberPlates = (plates) => {
        if (plates.length === 0) {
            if (container) {
                container.innerHTML = `<div class="alert alert-warning">
                                            <p>You don't have any plates yet. Start by adding a new one.</p>
                                        </div>`;
                return;
            }
        }

        const listHtml = plates.map(plate => {
            return `
                <div class="my-listings-item listing-item">
                    <div class="d-flex align-items-center layout-my-listings">
                        <div class="listing-thumbnail-wrapper d-none d-md-block">
                            ${check_img_path(plate)}
                            <div class="top-label"></div>
                        </div>
                        <div class="inner-info flex-grow-1 d-flex align-items-center layout-left">
                            <div class="inner-info-left">
                                <h3 class="listing-title">
                                    <a href="${plate.permalink}">
                                        ${plate.title}
                                    </a>
                                </h3>
                                <div class="listing-meta with-icon tagline">
                                    <span class="value-suffix"></span>
                                </div>

                                <div class="listing-price d-flex align-items-baseline">
                                    <div class="main-price">
                                        <span class="suffix">RM</span> 
                                        <span class="price-text">
                                            ${plate.price}
                                        </span>
                                    </div>
                                </div>							
                            </div>

                            <div class="listing-info-date-expiry d-none d-md-block">
                                <div class="listing-table-info-content-expiry">
                                        --								
                                </div>
                            </div>
                            <div class="status-listing-wrapper d-none d-md-block">
                                <span class="status-listing publish">
									Published							
                                </span>
                            </div>
                            <div class="view-listing-wrapper d-none d-md-block">
                                0							
                            </div>
                            <div class="warpper-action-listing">
                                <a data-toggle="tooltip" href="/number-plates/?plate_id=${plate.id}&action=continue" class="edit-btn btn-action-icon edit  job-table-action" title="" data-bs-original-title="Continue">
                                    <i class="ti-arrow-top-right"></i>
                                </a>
                                            
                                <a data-toggle="tooltip" class="remove-btn btn-action-icon number-plate-table-action number-plate-button-delete" href="javascript:void(0)" data-number_plate_id="${plate.id}" data-nonce="${plate.nonce}" title="" data-bs-original-title="Remove">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.06907 7.69027C7.37819 7.65937 7.65382 7.8849 7.68472 8.19405L8.05972 11.944C8.0907 12.2531 7.8651 12.5288 7.55602 12.5597C7.2469 12.5906 6.97124 12.3651 6.94033 12.0559L6.56533 8.30595C6.53442 7.99687 6.75995 7.72117 7.06907 7.69027Z" fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.931 7.69027C11.2401 7.72117 11.4656 7.99687 11.4347 8.30595L11.0597 12.0559C11.0288 12.3651 10.7532 12.5906 10.444 12.5597C10.135 12.5288 9.90943 12.2531 9.94033 11.944L10.3153 8.19405C10.3462 7.8849 10.6219 7.65937 10.931 7.69027Z" fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.59285 0.937514H10.129C10.2913 0.937409 10.4327 0.937319 10.5662 0.958642C11.0936 1.04287 11.5501 1.37186 11.7968 1.84563C11.8592 1.96555 11.9039 2.09971 11.9551 2.2537L12.0388 2.50488C12.053 2.5474 12.057 2.55943 12.0605 2.56891C12.1918 2.932 12.5323 3.17744 12.9183 3.18723C12.9284 3.18748 12.9409 3.18753 12.9859 3.18753H15.2359C15.5466 3.18753 15.7984 3.43936 15.7984 3.75003C15.7984 4.06069 15.5466 4.31253 15.2359 4.31253H2.48584C2.17518 4.31253 1.92334 4.06069 1.92334 3.75003C1.92334 3.43936 2.17518 3.18753 2.48584 3.18753H4.73591C4.78094 3.18753 4.79336 3.18748 4.80352 3.18723C5.18951 3.17744 5.53002 2.93202 5.66136 2.56893C5.66481 2.55938 5.66879 2.54761 5.68303 2.50488L5.76673 2.25372C5.81796 2.09973 5.86259 1.96555 5.92503 1.84563C6.17174 1.37186 6.62819 1.04287 7.15566 0.958642C7.28918 0.937319 7.43057 0.937409 7.59285 0.937514ZM6.61695 3.18753C6.65558 3.11176 6.68982 3.03303 6.71927 2.95161C6.72821 2.92688 6.73699 2.90056 6.74825 2.86675L6.82311 2.64219C6.89149 2.43706 6.90723 2.39522 6.92285 2.36523C7.00508 2.2073 7.15724 2.09764 7.33306 2.06956C7.36646 2.06423 7.41111 2.06253 7.62735 2.06253H10.0945C10.3107 2.06253 10.3553 2.06423 10.3888 2.06956C10.5646 2.09764 10.7168 2.2073 10.799 2.36523C10.8146 2.39522 10.8303 2.43705 10.8987 2.64219L10.9735 2.86662L11.0026 2.95162C11.032 3.03304 11.0663 3.11176 11.1049 3.18753H6.61695Z" fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.83761 5.81377C4.14757 5.7931 4.41561 6.02763 4.43628 6.3376L4.78123 11.5119C4.84862 12.5228 4.89664 13.2262 5.00207 13.7554C5.10433 14.2688 5.24708 14.5405 5.45215 14.7323C5.6572 14.9241 5.93782 15.0485 6.45682 15.1164C6.99187 15.1864 7.6969 15.1875 8.71 15.1875H9.29004C10.3031 15.1875 11.0081 15.1864 11.5432 15.1164C12.0622 15.0485 12.3428 14.9241 12.5479 14.7323C12.7529 14.5405 12.8957 14.2688 12.998 13.7554C13.1034 13.2262 13.1514 12.5228 13.2188 11.5119L13.5638 6.3376C13.5844 6.02763 13.8524 5.7931 14.1624 5.81377C14.4724 5.83443 14.7069 6.10246 14.6863 6.41244L14.3387 11.6262C14.2745 12.5883 14.2228 13.3654 14.1013 13.9752C13.975 14.6092 13.7602 15.1388 13.3165 15.5538C12.8728 15.9689 12.3301 16.148 11.6891 16.2319C11.0726 16.3125 10.2938 16.3125 9.32957 16.3125H8.67047C7.70627 16.3125 6.92743 16.3125 6.3109 16.2319C5.66991 16.148 5.12725 15.9689 4.68356 15.5538C4.23986 15.1388 4.02505 14.6092 3.89875 13.9752C3.77727 13.3654 3.72547 12.5883 3.66136 11.6262L3.31377 6.41244C3.2931 6.10246 3.52763 5.83443 3.83761 5.81377Z" fill="currentColor"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (container) {
            container.innerHTML = listHtml;
        }
    }

    const targetSelector = '.number-plate-button-delete';

    // Check if the class exist within the container
    if (container && container.querySelector(targetSelector)) {
        console.log(`Element matching "${targetSelector}" already exists in the container`);
    }

    // Set up a MutationObserver to watch for future changes
    if (container) {
        const observer = new MutationObserver(mutationsList => {
            for (const mutation of mutationsList) {
                // Check if nodes are added to the container
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType ===1 && node.matches(targetSelector)) {
                            node.addEventListener('click', () => {
                                remove_data(node);
                            });
                        } else if (node.querySelector) {
                            const matchingChild = node.querySelector(targetSelector);
                            if (matchingChild) {
                                matchingChild.addEventListener('click', () => {
                                    remove_data(matchingChild);
                                });
                            }
                        }
                    });
                }
            }
        });

        // Start observing the container for child changes
        observer.observe(container, { childList: true, subtree: true });
    }

    // Stop observing (if needed) after some time or event
    // observer.disconnect();

    // Function to remove number plate of the lists.
    const remove_data = async (btn) => {
        const plate_id = btn.getAttribute('data-number_plate_id');
        const delete_nonce = btn.getAttribute('data-nonce');

        if (!confirm('Are you sure?')) {
            return;
        }

        btn.classList.add('loading');

        try {
            const response = await fetch(`${cpp_script_data.ajaxUrl}?action=delete_number_plate&plate_id=${plate_id}&delete_nonce=${delete_nonce}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': cpp_script_data.nonce,
                }
            });

            if (!response.ok) {
                popup_message('Failed to delete the number plate. Please try again later or contact the administrator.', 'alert', 'bg-warning');
                throw new Error('Failed to delete the number plate.');
            }

            const data = await response.json();
            btn.classList.remove('loading');

            if (data.success) {
                popup_message('Number plate deleted successfully.', 'alert', 'bg-info');
                get_number_list();
            } else {
                popup_message('Failed to delete number plate.', 'alert', 'bg-warning');
                get_number_list();
            }
        } catch (error) {
            console.error('Error: ', error);
            popup_message('An error occurred please try again later.', 'alert', 'bg-warning');
        }
    };
    
    // For displaying notifications
    const popup_message = (message, ...class_names) => {
        const div = document.createElement('div');
        div.setAttribute('id', 'wp-cardealer-popup-message');
        div.classList.add('animated', 'delay-2s', 'fadeOutRight');

        const inner_div = document.createElement('div');
        inner_div.classList.add('message-inner');

        class_names.forEach(class_name => {
            inner_div.classList.add(class_name);
        });

        inner_div.textContent = message;

        div.appendChild(inner_div);
        document.body.appendChild(div);
    };

    // Fetch all the list of number plates for current user
    const get_number_list = async () => {
        try {
            const response = await fetch(`${cpp_script_data.ajaxUrl}?action=get_number_plates`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': cpp_script_data.nonce
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                renderNumberPlates(data.data);
            } else {
                if (container) {
                    container.innerHTML = ` <div class="alert alert-warning">
                    <p>${data.data.message}.</p>
                    </div>`;
                }
            }
        } catch (error) {
            console.error('Error fetching number plates: ', error);
            if (container) {
                container.innerHTML = ` <div class="alert alert-warning">
                <p>Failed to load number plates. Please try again later.</p>
                </div>`;
            }
        }
    };

    get_number_list();
});