async function handleDelete(blogId) {
    const confirmed = confirm('Are you sure you want to delete this blog?');
    if (!confirmed) return false;

    try {
        const response = await fetch('/Code Chronicle/admin/deleteBlog.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ blog_id: blogId })
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
