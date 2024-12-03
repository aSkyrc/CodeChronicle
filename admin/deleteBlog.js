async function handleDelete(blogId) {
    const confirmed = confirm('Are you sure you want to delete this blog?');
    if (!confirmed) return false;

    // Get the logged-in user's ID (this is just an example, adjust it based on your app's structure)
    const userId = 'user_id_here';  // Replace with actual logic to retrieve the logged-in user's ID
    
    if (!userId) {
        alert("User ID is missing. Please log in.");
        return false;
    }

    try {
        const response = await fetch('/Code Chronicle/admin/deleteBlog.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                blog_id: blogId,
                user_id: userId // Add user_id to the request
            })
        });

        const result = await response.json();

        // Check if the response was successful
        if (result.success) {
            alert(result.message);
            // Reload the page to reflect changes
            window.location.reload();
        } else {
            alert(`Error: ${result.message}`);
        }
    } catch (error) {
        console.error('An error occurred:', error);
        alert('Failed to delete the blog. Please try again.');
    }

    // Prevent default anchor behavior
    return false;
}
