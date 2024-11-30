<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lalezar&display=swap" rel="stylesheet">
    <title>Blog and Rating</title>
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
            margin: 0;
            font-family: Arial, sans-serif;
            height: 100vh;
            background-color: rgb(236, 236, 236);
            display: flex;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            height: 500px;
            border: 1px solid rgb(151, 151, 151);
            background-color: whitesmoke;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            margin: 10px;
            margin-left: 80px;
            font-family: 'Lalezar';
            margin-top: 7%;
        }

        .profile-container {
            display: flex;
            align-items: center; /* Align profile picture and text vertically in the center */
            justify-content: flex-start; /* Align them horizontally starting from the left */
            margin-bottom: 20px;
            width: 100%;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 10px; /* Space between picture and text */
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
        }

        .author-name {
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .role-description {
            font-size: 14px;
            color: gray;
            margin-bottom: 10px;
        }

        .user-rating {
            font-size: 14px;
        }

        .rating-value {
            font-weight: bold;
        }


        hr {
            width: 80%;
            margin: 10px 0;
            border: none;
            border-top: 1px solid gray;
            margin-bottom: 40px;
        }

        .sidebar a {
            color: black;
            text-decoration: none;
            margin: 10px 0;
            font-size: 16px;
            text-align: center;
        }

        .sidebar a:hover {
            text-decoration: underline;
        }

        .rate-blog {
            text-align: center;
            margin: 15px 0;
        }

        .star-rating {
            display: flex;
            justify-content: center;
            gap: 5px;
            font-size: 30px;
            color: gray;
            cursor: pointer;
        }

        .star-rating .star.active {
            color: gold;
        }

        .go-back-btn {
            background-color: #6C63FF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            width: 160px;
            border: 1px solid black;
            cursor: pointer;
            margin-top: 50px;
            font-size: 16px;
        }

        .go-back-btn:hover {
            background-color: #5752d8;
        }

        .main-content {
            flex: 1;
            padding-right: 100px;
            padding-top: 7%;
            overflow-y: auto;
        }

        .content-container {
            display: flex;
            text-align: left;
            margin-bottom: 0px;
            padding: 10px; 
            gap: 13px; 
        }

        .text-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
        }

        .category {
            font-weight: 500;
            font-family: 'Lalezar';
            font-size: 45px;
            letter-spacing: 2px;
            color: rgb(7, 0, 0);
        }

        .title {
            font-size: 70px;
            font-weight: 600;
            font-family: 'Lalezar';
            letter-spacing: 5px;
            margin-top: -20px;
            margin-right: 50px;
        }

        .shortdescription {
            font-size: 20px;
            font-family: 'Lalezar';
            word-wrap: break-word;
            color: black;
            font-weight: 500;
            line-height: 2;
        }

        .img-thumbnail {
            display: flex;
            flex-shrink: 1;
            align-items: flex-start;
        }

        .img-thumbnail img {
            margin-top: 10px;
            height: 250px;
            width: 400px;
            border-radius: 10px;
            object-fit: cover;
            padding-top: 30px;       
        }

        .add-tool-container {
            text-align: left;
            padding: 10px;
            padding-top: 1px; 
            gap: 13px;
        }

        .blogcontentdescription {
            font-size: 20px;
            font-family: 'Lalezar';
            word-wrap: break-word;
            color: black;
            font-weight: 500;
            line-height: 2;
        }

        .youtube-video-container {
            text-align: center;
            margin: 50px;
        }

        .horizontaldescription {
            font-size: 20px;
            font-family: 'Lalezar';
            word-wrap: break-word;
            color: black;
            font-weight: 500;
            text-align: center;
            line-height: 2;
            width: 500px;
            margin-left: 125px;
        }

        .fullblogdescription {
            font-size: 20px;
            font-family: 'Lalezar';
            word-wrap: break-word;
            color: black;
            font-weight: 500;
            text-align: center;
            line-height: 2;
            margin-left: 30px;
            padding: 10px;
            text-align: left;
            margin-top: 10px;
        }

        .imageuploadsection {
            text-align: center;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .imageuploadsection img {
            height: 400px;
            width: 860px;
            margin-top: 50px;
        } 

    </style>
</head>
<body>
  <div class="sidebar">
    <!-- Profile container (image and text) -->
    <div class="profile-container">
        <img class="profile-picture" src="https://via.placeholder.com/100" alt="Profile Picture">
        <div class="profile-details">
            <div class="author-name">Blog Author</div>
            <div class="role-description">Role Description</div>
            <div class="user-rating">User Rating: <span class="rating-value" id="rating-value">0</span></div>
        </div>
    </div>
    
    <hr>
    <a href="#">Visit Author Profile</a>
    <a href="#">Follow</a>
    <a href="#">Save Blog</a>
    <div class="rate-blog">
        <span>Rate Blog Post</span>
        <div class="star-rating" id="star-rating">
            <span class="star" data-value="1">★</span>
            <span class="star" data-value="2">★</span>
            <span class="star" data-value="3">★</span>
            <span class="star" data-value="4">★</span>
            <span class="star" data-value="5">★</span>
        </div>
    </div>
    <button class="go-back-btn">Go Back</button>
</div>


    <div class="main-content" id="post-container"></div>

    <script>
        window.onload = function () {
            const postContainer = document.getElementById('post-container');
            const upload = JSON.parse(localStorage.getItem('upload')) || [];

            if (upload.length === 0) {
                postContainer.innerHTML = '<p>No posts to display</p>';
                return;
            }

            upload.reverse().forEach((post) => {
                const postUpload = document.createElement('div');
                postUpload.innerHTML = `
                    <div class="content-container">
                        <div class="text-content">
                            <div class="category">${post.category}</div>
                            <div class="title">${post.title || 'Untitled'}</div>
                            <div class="shortdescription">${post.shortdescription || ''}</div>
                        </div>
                        <div class="img-thumbnail">
                            <img src="${post.imgThumbnail}" alt="Thumbnail">       
                        </div>
                    </div>

                    <div class="add-tool-container">
                        <div class="blogcontentdescription">
                            <div class="blogcontentdescription">${post.blogcontentdescription || ''}</div>
                        </div>

                        ${post.youtubeVideoLink ? `
                            <div class="youtube-video-container">
                                <iframe width="560" height="315" src="https://www.youtube.com/embed/${extractVideoId(post.youtubeVideoLink)}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        ` : ''}

                        <div class=" horizontaldescription">
                            <div class=" horizontaldescription">${post.fullblogdescription || ''}</div>
                        </div>

                        <div class="imageuploadsection">
                            <img src="${post.imageuploadsection}" alt="">        
                        </div>                                             

                        <div class="fullblogdescription">
                            <div class="fullblogdescription">${post.horizontaldescription || ''}</div>
                        </div>
                    </div>
                `;
                postContainer.prepend(postUpload);
            });
        };

        function extractVideoId(url) {
            const match = url.match(/(?:https?:\/\/(?:www\.)?youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            return match ? match[1] : '';
        }

        // JavaScript for interactive star rating
        const stars = document.querySelectorAll('.star-rating .star');
        const ratingValueElement = document.getElementById('rating-value');

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.getAttribute('data-value');
                
                // Update the rating value display
                ratingValueElement.textContent = rating;

                // Highlight the selected stars
                stars.forEach(s => {
                    s.classList.toggle('active', s.getAttribute('data-value') <= rating);
                });
            });
        });
    </script>
</body>
</html>