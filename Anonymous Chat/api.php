<?php
include 'db_connection.php';
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

switch ($action) {
    case 'create_post':
        $content = trim($_POST['content'] ?? '');
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Content cannot be empty.']);
            break;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
            $stmt->execute([$user_id, $content]);
            echo json_encode(['success' => true, 'message' => 'Post created.']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
        break;

    case 'edit_post':
        $post_id = $_POST['post_id'] ?? null;
        $content = trim($_POST['content'] ?? '');

        if (!$post_id || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Missing data.']);
            break;
        }
        try {
            // Only allow the post owner to edit the post
            $stmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$content, $post_id, $user_id]);
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'Post updated.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Post not found or you are not the owner.']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
        break;
		
    case 'delete_post':
        $post_id = $_POST['post_id'] ?? null;
        if (!$post_id) {
            echo json_encode(['success' => false, 'message' => 'Missing Post ID.']);
            break;
        }
        try {
            // Only allow deletion if the post belongs to the current user
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'Post deleted.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Post not found or you are not the owner.']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error.']);
        }
        break;

    case 'add_comment':
        $post_id = $_POST['post_id'] ?? null;
        $content = trim($_POST['content'] ?? '');

        if (!$post_id || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Missing data.']);
            break;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $user_id, $content]);
            echo json_encode(['success' => true, 'message' => 'Comment added.']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
        break;

    case 'delete_comment':
        $comment_id = $_POST['comment_id'] ?? null;
        if (!$comment_id) {
            echo json_encode(['success' => false, 'message' => 'Missing Comment ID.']);
            break;
        }
        try {
            // Only allow deletion if the comment belongs to the current user
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
            $stmt->execute([$comment_id, $user_id]);
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'Comment deleted.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Comment not found or you are not the owner.']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error.']);
        }
        break;
        
    case 'like_post':
        $post_id = $_POST['post_id'] ?? null;
        if (!$post_id) {
            echo json_encode(['success' => false, 'message' => 'Missing post ID.']);
            break;
        }
        try {
            // Check if user already liked
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
            if ($stmt->fetchColumn() > 0) {
                // User already liked, remove like
                $pdo->prepare("DELETE FROM post_likes WHERE user_id = ? AND post_id = ?")->execute([$user_id, $post_id]);
                $pdo->prepare("UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?")->execute([$post_id]);
                echo json_encode(['success' => true, 'liked' => false, 'message' => 'Like removed.']);
            } else {
                // Add like
                $pdo->prepare("INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)")->execute([$user_id, $post_id]);
                $pdo->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?")->execute([$post_id]);
                echo json_encode(['success' => true, 'liked' => true, 'message' => 'Post liked.']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
        break;

    case 'fetch_posts':
        try {
            // 1. Fetch Posts and User Info
            $stmt = $pdo->query("
                SELECT p.id as post_id, p.content, p.likes_count, p.created_at, p.user_id as post_user_id, u.username
                FROM posts p JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
            ");
            $posts_data = $stmt->fetchAll();

            // 2. Fetch all comments in a single query (more efficient)
            $stmt = $pdo->query("
                SELECT c.id as comment_id, c.post_id, c.content, c.user_id as comment_user_id, u.username
                FROM comments c JOIN users u ON c.user_id = u.id
                ORDER BY c.created_at ASC
            ");
            $comments_data = $stmt->fetchAll();
            $comments_by_post = [];
            foreach ($comments_data as $comment) {
                $comments_by_post[$comment['post_id']][] = $comment;
            }

            // 3. Check which posts the current user has liked
            $stmt = $pdo->prepare("SELECT post_id FROM post_likes WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $liked_posts = array_column($stmt->fetchAll(), 'post_id');

            // 4. Combine and send data
            $final_posts = [];
            foreach ($posts_data as $post) {
                $post['comments'] = $comments_by_post[$post['post_id']] ?? [];
                $post['is_liked'] = in_array($post['post_id'], $liked_posts);
                $final_posts[] = $post;
            }

            echo json_encode(['success' => true, 'posts' => $final_posts]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'DB Fetch Error: ' . $e->getMessage()]);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing action.']);
        break;
}
?>