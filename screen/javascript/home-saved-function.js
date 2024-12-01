function openModal() {
    const modal = document.getElementById("homepage-interest-modal");
    modal.style.display = "block";
}

function closeModal() {
    const modal = document.getElementById("homepage-interest-modal");
    modal.style.display = "none";
}
    // This will trigger the form submission (if needed)
    function addInterest() {
        const selectedInterests = [];
        const checkboxes = document.querySelectorAll('input[name="interest[]"]:checked');
        checkboxes.forEach(function(checkbox) {
            selectedInterests.push(checkbox.value);
        });

        if (selectedInterests.length > 0) {
            // Send the selected interests to the server via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "addInterest.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Interests added successfully!');
                    location.reload(); // Optionally reload the page to reflect the updates
                } else {
                    alert('Error adding interests.');
                }
            };

            // Send interests as form data, not JSON
            xhr.send("interest[]=" + selectedInterests.join("&interest[]=")); // Correct format for sending an array
        } else {
            alert("Please select at least one interest.");
        }
    }


    document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');

    // Attach click listener to all .save divs
    document.querySelectorAll('.save').forEach((saveDiv) => {
        const img = saveDiv.querySelector('img');
        const hiddenButton = saveDiv.querySelector('button');

        img.addEventListener('click', () => {
            console.log('Image clicked. Triggering hidden button.');
            hiddenButton.click(); // Trigger hidden button click
        });
    });

    // Save blog function
    function saveBlog(element) {
        const blogId = element.getAttribute('data-id');
        console.log('Saving blog with ID:', blogId);

        fetch('addBlog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ blogId }),
        })
        .then((response) => {
            console.log('Response received:', response);
            return response.json();
        })
        .then((data) => {
            console.log('Parsed JSON data:', data);
            if (data.success) {
                alert('Blog saved successfully!');
            } else {
                alert('This ' + data.message);
            }
        })
        .catch((error) => {
            console.error('Error during fetch:', error);
            alert('An error occurred while saving the blog.');
        });
    }

    window.saveBlog = saveBlog; // Make it globally accessible
});

if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
  document.querySelector('.homepage-center-content').style.overflowY = 'hidden';
}

