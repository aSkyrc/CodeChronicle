function toggleJoinStatus(communityName) {
    fetch('/user/addCommunityInterest.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ community: communityName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const joinButton = document.getElementById('joinButton');
            joinButton.textContent = data.isJoined ? 'Joined' : 'Join';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}