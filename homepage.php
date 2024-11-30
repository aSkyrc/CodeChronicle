
  </style>
</head>
<body>

  <div class="container">

    <aside class="sidebar">
      <div class="user-info-sidebar">
          <div class="avatar">
              <img src="https://cdn-icons-png.flaticon.com/512/3917/3917688.png" alt="User Avatar">
          </div>
          <div class="user-details">
              <p class="username">Username</p>
              <p class="role">Student</p>
          </div>
          <div class="line">
              <h5>_______________</h5>
          </div>
          <button class="post-blog-btn">Post Blog</button>
      </div>

      <div class="interests">
          <h4>Your Interests</h4>
          <h5>________________________</h5>
          <ul>
              <li>+ Add Interest</li>
              <li>Frontend Development</li>
              <li>Backend Development</li>
              <li>Mobile Development</li>
              <h6>_____________________________</h6>
          </ul>      
      </div>
  </aside>

    <div class="center-content" id="post-container">
    <div class="content-container">
                <div class="user-info">
                <img src="${post.userImage}" alt="User Image">
                <div class="user-name">${post.userName || 'User'}</div>
                </div>

                 <div class="save">
                  <img src="https://cdn-icons-png.flaticon.com/512/3916/3916593.png">
                </div>

                <div class="text-content">
                    <p>${post.category}</p>
                </div>
                <div class="tutorial">
                <div class=<tutorial><h2>a tutorial about Frontend</h2></div>  
                </div>
                <button class="continue-button" onclick="alert('Redirecting to the full post...')">Continue read...</button>
            </div>
            <img src="${post.mainImage}" alt="Post Image">
    </div>
    
    <div class="sidebar-right">
      <h3>Popular Blog Content</h3>
      <ul>
      </ul>
    </div>
  </div>

  <script>
    window.onload = function () {
      const postContainer = document.getElementById('post-container');
      const posts = JSON.parse(localStorage.getItem('posts')) || [];

      if (posts.length === 0) {
        console.log('No posts to display');
        return;
      }

      posts.reverse().forEach(post => {
        const postCard = document.createElement('div');
        postCard.classList.add('post-card');
        postCard.innerHTML = `
            <div class="content-container">
                <div class="user-info">
                <img src="${post.userImage}" alt="User Image">
                <div class="user-name">${post.userName || 'User'}</div>
                </div>

                 <div class="save">
                  <img src="https://cdn-icons-png.flaticon.com/512/3916/3916593.png">
                </div>

                <div class="text-content">
                    <p>${post.category}</p>
                </div>
                <div class="tutorial">
                <div class=<tutorial><h2>a tutorial about Frontend</h2></div>  
                </div>
                <button class="continue-button" onclick="alert('Redirecting to the full post...')">Continue read...</button>
            </div>
            <img src="${post.mainImage}" alt="Post Image">
        `;
        postContainer.prepend(postCard);
      });

      console.log('Posts loaded:', posts);
    };
  </script>
</body>
</html>