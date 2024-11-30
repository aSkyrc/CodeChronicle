<?php 
include_once '../user/navigationBar.php';
?>

<body>
    <div class="community" style="margin-top: 70px;">
    <select id="communityCategory" onchange="filterCommunity()">
    <option value="">Select Category</option>
    <option value="Frontend Development">Frontend Development</option>
    <option value="Backend Development">Backend Development</option>
    <option value="Data Science and Machine Learning">Data Science and Machine Learning</option>
    <option value="Mobile Development">Mobile Development</option>
    <option value="DevOps and Cloud Computing">DevOps and Cloud Computing</option>
    <option value="Cybersecurity">Cybersecurity</option>
    <option value="Programming Language">Programming Language</option>
    <option value="Algorithms and Data Structures">Algorithms and Data Structures</option>
    <option value="Game Development">Game Development</option>
    <option value="Career and Networking">Career and Networking</option>
</select>
<div id="communityContent">
    <!-- Dynamic content will be injected here -->
</div>
    </div>
    

<script>
const communityData = {
    "Frontend Development": `
        <h2>Frontend Development</h2>
        <p>Welcome to the Frontend Development community! Share and learn about HTML, CSS, JavaScript, frameworks like React, Angular, and more.</p>
    `,
    "Backend Development": `
        <h2>Backend Development</h2>
        <p>Join the Backend Development community! Discuss servers, databases, APIs, Node.js, Python, Ruby, and more.</p>
    `,
    "Data Science and Machine Learning": `
        <h2>Data Science and Machine Learning</h2>
        <p>Explore the world of Data Science and Machine Learning! Share insights, projects, and ideas about Python, R, AI models, and more.</p>
    `,
    "Mobile Development": `
        <h2>Mobile Development</h2>
        <p>Dive into Mobile Development! Discuss Android, iOS, Flutter, React Native, and more.</p>
    `,
    "DevOps and Cloud Computing": `
        <h2>DevOps and Cloud Computing</h2>
        <p>Discuss DevOps practices and Cloud platforms like AWS, Azure, Docker, Kubernetes, and more.</p>
    `,
    "Cybersecurity": `
        <h2>Cybersecurity</h2>
        <p>Learn about securing systems and data. Share knowledge about ethical hacking, network security, and more.</p>
    `,
    "Programming Language": `
        <h2>Programming Languages</h2>
        <p>Talk about Python, Java, C++, JavaScript, and more programming languages in this category.</p>
    `,
    "Algorithms and Data Structures": `
        <h2>Algorithms and Data Structures</h2>
        <p>Share tips and solutions about algorithms, data structures, coding challenges, and more.</p>
    `,
    "Game Development": `
        <h2>Game Development</h2>
        <p>Connect with game developers to discuss Unity, Unreal Engine, game design, and more.</p>
    `,
    "Career and Networking": `
        <h2>Career and Networking</h2>
        <p>Network and share career advice, resume tips, and interview preparation techniques.</p>
    `,
};

// Function to filter and display the selected category's content
function filterCommunity() {
    const selectedCategory = document.getElementById("communityCategory").value;
    const communityContent = document.getElementById("communityContent");

    // Update the content based on the selection
    communityContent.innerHTML = communityData[selectedCategory] || `<p>Please select a category to view the community content.</p>`;
}

// Initialize with a default message
document.getElementById("communityContent").innerHTML = `<p>Please select a category to view the community content.</p>`;
</script>
</body>
</html>