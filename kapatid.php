<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile</title>
  <style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f9;
  }
  
  .profile-container {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
  
  .user-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #ddd;
    padding-bottom: 20px;
    margin-bottom: 20px;
  }
  
  .profile-pic {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background-color: #ccc;
    flex-shrink: 0; /* Ensures the image doesn't shrink */
  }
  
  .user-info {
    margin-left: 15px; /* Creates space between the profile pic and user info */
    flex: 1; /* Allows user-info to take available space */
  }
  
  .ratings {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .stars {
    color: gold;
  }
  
  .credentials ul {
    list-style: none;
    padding: 0;
  }
  
  .credentials button {
    margin-bottom: 10px;
    color: black;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
  }
  
  .posts .post {
    background: #f9f9f9;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
  }
  
  .posts .post h2 {
    margin-top: 0;
    flex-direction: row;
  }
  
  .read-more {
    background: #007BFF;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .tech-stack img {
    width: 150px;
    height: 150px;
    margin: 5px;
  }
  .post-content{
    display: flex;
  }
  .rating-container {
    text-align: center;
  }
  
  .stars {
    display: inline-flex;
    cursor: pointer;
    font-size: 1rem;
  }
  
  .star {
    color: #ccc;
    transition: color 0.3s;
  }
  
  .star.selected,
  .star:hover,
  .star:hover ~ .star {
    color: gold;
  }
  </style>
</head>
<body>
  <div class="profile-container">
    <div class="user-header">
      <div class="profile-pic"></div>
      <div class="user-info">
        <h1>USERNAME bulbol</h1>
        <p>User role</p>
        <div class="stars">
          <span class="star" data-value="1">★</span>
          <span class="star" data-value="2">★</span>
          <span class="star" data-value="3">★</span>
          <span class="star" data-value="4">★</span>
          <span class="star" data-value="5">★</span>
        </div>
        <p class="bio">Write about yourself</p>
      </div>
      <div class="credentials">
        <h3>Credentials & Highlights</h3>
        <ul>
          <li>Add Education Credentials</li>
          <li>Add Work Credentials</li>
          <li>Date</li>
        </ul>
      </div>
    </div>

    <div class="posts">
      
      <div class="post">
        <p class="post-date">January 10, 2024</p>
        <h2>Lorem Ipsum</h2>
        <div class="post-content"> 
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <div class="tech-stack">
          <img src="../img/karina.jpg" alt="Pascal">
        </div>
        </div> <button class="read-more">Continue reading...</button>
      </div>
    </div>
  </div>

  <script src="../js/continueR.js"></script>
  <script src="../js/rating.js"></script>
</body>
</html>