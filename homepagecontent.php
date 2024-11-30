<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <style>
        * {
        margin: 0;
        padding: 0;

        }

        body {
        margin: 0 0 0 0;
        background-color: #F5F5F5;
        font-family: 'Lalezar';
        }



        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .frofile-container {
            width: 500px;
            max-width: 100%;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input[type="text"], input[type="file"], select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        button:hover {
            background-color: #0056b3;
        }

        .delete-button {
            background-color: #ff4c4c;
            margin-top: 15px;
        }

        .delete-button:hover {
            background-color: #e04a4a;
        }
    </style>
</head>
<body>
    <DIV class="">
        <h1>SEE THE RESULT LANG</h1>
    </DIV>
   
    <div class="frofile-container">


        <label for="profile-image-input">Upload Profile Image</label>
        <input type="file" id="profile-image-input" accept="image/*">
        

        <label for="username-input">Username</label>
        <input type="text" id="username-input" placeholder="Enter post title">

        <label for="category-select">Role</label>
        <select id="category-select">
            <option value="" disabled selected>Choose a category</option>
            <option value="Student">Student</option>
            <option value="Frontend Beginners">Frontend</option>
            <option value="Backend Beginners">Backend</option>
            <option value="Other">Other</option>
        </select>

        <label for="image-input">Upload Post Image</label>
        <input type="file" id="image-input" accept="image/*">

        <button onclick="submitPost()">Submit Post</button>
        <button class="delete-button" onclick="deleteAllPosts()">Delete All Posts</button>
    </div>

    <script>
        function submitPost() {
            const userName = document.getElementById('username-input').value;
            const category = document.getElementById('category-select').value;
            const imageInput = document.getElementById('image-input').files[0];
            const profileInput = document.getElementById('profile-image-input').files[0];

            if (!userName || !category || !imageInput || !profileInput) {
                alert('Please fill out all fields.');
                return;
            }

            const reader = new FileReader();
            const profileReader = new FileReader(); // Reader for profile image

            reader.onload = function (e) {
                const mainImage = e.target.result;

                profileReader.onload = function (e) {
                    const userImage = e.target.result; // Profile image data
                    const posts = JSON.parse(localStorage.getItem('posts')) || [];
                    posts.push({ userName, category, mainImage, userImage }); // Include category
                    localStorage.setItem('posts', JSON.stringify(posts));
                    alert('Post Blog Successfully');
                    location.href = 'try-homepage.html'; // Redirect to homepage after submitting
                };

                profileReader.readAsDataURL(profileInput);
            };

            reader.readAsDataURL(imageInput);
        }

        function deleteAllPosts() {
            const confirmDelete = confirm("Are you sure you want to delete all posts?");
            if (confirmDelete) {
                localStorage.removeItem('posts');
                alert('All posts have been deleted.');
                location.href = 'try-homepage.html'; // Optionally redirect after deletion
            }
        }
    </script>
</body>
</html>