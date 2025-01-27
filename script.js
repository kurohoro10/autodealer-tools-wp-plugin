const queryData = JSON.parse(cpp_script_data.query);
console.log(queryData);


// Remove plate number on lists
const remove_btns = document.querySelectorAll('.number-plate-button-delete');

remove_btns.forEach(remove_btn => {
    remove_btn.addEventListener('click', async (e) => {
        e.preventDefault();

        const plate_id = remove_btn.getAttribute('data-number_plate_id');
        const delete_nonce = remove_btn.getAttribute('data-nonce');

        if (!confirm('Are you sure?')) {
            return;
        }

        remove_btn.classList.add('loading');

       try {
            const response = await fetch(`/my-listings/?action=delete&plate_id=${plate_id}&delete_nonce=${delete_nonce}`, {
                method: 'GET'
            });

            if (!response.ok) {
                throw new Error('Failed to delete the number plate.');
            }

            const data = await response.text();
            popup_message('Number plate deleted successfully.', 'alert', 'bg-info');
            remove_btn.classList.remove('loading');
            location.reload();
       } catch (error) {
            console.error("Error:", error);
       }
    });
});

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