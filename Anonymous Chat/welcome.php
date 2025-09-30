<?php
session_start();
// Line 9: CHECK if user is logged in and REDIRECT immediately if not.
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit(); // <-- This is the crucial line to stop execution
}

// Get user data for the JS - these lines are now safe because we exited above if they were missing.
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Deeposts Feed</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <header class="header">
	<img src="1.png" width="100" height="100">
      <!--<span class="logo">Anonymous Chat</span>-->
      <div class="profile-menu">
        <div class="profile-pic" id="profilePic"></div>
        <span id="profileName"></span>
        <span class="logout-btn" onclick="window.location.href='logout.php'">Log Out</span>
      </div>
    </header>

    <main class="main-content">
      <div class="create-post">
        <div class="post-input-container">
          <div class="profile-pic" id="miniPic"></div>
          <input id="postInput" class="post-input" placeholder="What's on your mind?">
          <button id="sendButton" class="send-button"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
      </div>
      <div id="feed"></div>
    </main>
  </div>
  
  <script>
    const CURRENT_USER_ID = <?php echo $current_user_id; ?>;
    const CURRENT_USERNAME = '<?php echo $current_username; ?>';
  </script>
  <script src="script.js"></script>
</body>
</html>