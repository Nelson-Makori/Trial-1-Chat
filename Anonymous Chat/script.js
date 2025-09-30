// Global constants are now defined in welcome.php from PHP session
// const CURRENT_USER_ID;
// const CURRENT_USERNAME; 

// UI initialization
document.getElementById("profilePic").textContent = CURRENT_USERNAME[0].toUpperCase();
document.getElementById("miniPic").textContent = CURRENT_USERNAME[0].toUpperCase();
document.getElementById("profileName").textContent = CURRENT_USERNAME;

const feed = document.getElementById("feed");
const sendBtn = document.getElementById("sendButton");
const postInput = document.getElementById("postInput");

/**
 * Handles all AJAX communication with the backend.
 * @param {string} action The action to perform (e.g., 'create_post').
 * @param {Object} data Additional data to send (e.g., {content: 'Hello'}).
 */
async function api(action, data = {}) {
  const formData = new FormData();
  formData.append('action', action);
  for (const key in data) {
    formData.append(key, data[key]);
  }

  try {
    const response = await fetch('api.php', {
      method: 'POST',
      body: formData
    });
    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    alert('A network error occurred.');
    return { success: false, message: 'Network error.' };
  }
}

// ... existing functions and setup ...

/**
 * Renders all posts fetched from the database.
 */
async function renderPosts() {
  const result = await api('fetch_posts');
  if (!result.success) {
    feed.innerHTML = `<p style="color:red;">Error fetching posts: ${result.message}</p>`;
    return;
  }

  const posts = result.posts;
  feed.innerHTML = "";

  posts.forEach(post => {
    // Check ownership using the user_id retrieved from the API
    const isPostOwner = post.post_user_id == CURRENT_USER_ID; 
    const isLiked = post.is_liked;

    // Build the list of comments with delete buttons
    const commentsHtml = post.comments.map(c => {
        // Check ownership for the comment
        const isCommentOwner = c.comment_user_id == CURRENT_USER_ID; 
        const deleteBtnHtml = isCommentOwner 
            ? `<span class="delete-btn comment-delete" data-id="${c.comment_id}">🗑️</span>` 
            : '';
        return `<div class="comment" data-comment-id="${c.comment_id}"><b>${c.username}:</b> <span>${c.content}</span>${deleteBtnHtml}</div>`;
    }).join("");


    const el = document.createElement("div");
    el.className = "post-card";
    el.innerHTML = `
      <div class="post-header">
        <div class="profile-pic">${post.username[0].toUpperCase()}</div>
        <div class="user-info"><h4>${post.username}</h4><span class="timestamp">${post.created_at}</span></div>
      </div>
      <div class="post-content">
        <p>${post.content}</p>
        ${isPostOwner ? `<span class="edit-btn post-edit" data-id="${post.post_id}">✏️ Edit</span>` : ''}
        ${isPostOwner ? `<span class="delete-btn post-delete" data-id="${post.post_id}">🗑️ Delete</span>` : ''}
      </div>
      <div class="post-footer">
        <div class="post-actions">
          <div class="action-item like-btn" data-id="${post.post_id}">
            <i class="fa-solid fa-heart" style="color: ${isLiked ? 'red' : 'gray'};"></i> 
            <span class="like-count">${post.likes_count}</span>
          </div>
          <div class="action-item comment"><i class="fa-solid fa-comment"></i> ${post.comments.length}</div>
        </div>
      </div>
      <div class="comments">
        ${commentsHtml}
        <input type="text" class="comment-input" data-id="${post.post_id}" placeholder="Write a comment..."/>
      </div>`;
    
    // 1. LIKE POST Handler (Unchanged)
    el.querySelector(".like-btn").onclick = async (e) => {
      // ... like logic (unchanged) ...
      const postId = e.currentTarget.dataset.id;
      const response = await api('like_post', { post_id: postId });
      if (response.success) {
        renderPosts();
      } else {
        alert(response.message);
      }
    };

    // 2. EDIT POST Handler (Unchanged)
    if (isPostOwner) {
      el.querySelector(".post-edit").onclick = async (e) => {
        // ... edit logic (unchanged) ...
        const postId = e.currentTarget.dataset.id;
        let newContent = prompt("Edit post:", post.content);
        if (newContent) {
          const response = await api('edit_post', { post_id: postId, content: newContent });
          if (response.success) {
            renderPosts();
          } else {
            alert(response.message);
          }
        }
      };
      
      // 3. POST DELETION Handler (NEW)
      el.querySelector(".post-delete").onclick = async (e) => {
        if (confirm("Are you sure you want to delete this post?")) {
            const postId = e.currentTarget.dataset.id;
            const response = await api('delete_post', { post_id: postId });
            if (response.success) {
                renderPosts();
            } else {
                alert(response.message);
            }
        }
      };
    }

    // 4. COMMENT Handler (Unchanged)
    el.querySelector(".comment-input").onkeypress = async (e) => {
      // ... comment logic (unchanged) ...
      if (e.key === "Enter" && e.target.value.trim()) {
        const postId = e.target.dataset.id;
        const commentContent = e.target.value.trim();
        const response = await api('add_comment', { post_id: postId, content: commentContent });
        if (response.success) {
          e.target.value = "";
          renderPosts();
        } else {
          alert(response.message);
        }
      }
    };
    
    // 5. COMMENT DELETION Handler (NEW)
    el.querySelectorAll(".comment-delete").forEach(btn => {
        btn.onclick = async (e) => {
            if (confirm("Are you sure you want to delete this comment?")) {
                const commentId = e.currentTarget.dataset.id;
                const response = await api('delete_comment', { comment_id: commentId });
                if (response.success) {
                    renderPosts();
                } else {
                    alert(response.message);
                }
            }
        };
    });


    feed.appendChild(el);
  });
}

// ... rest of your script.js ...

// Initial load
renderPosts();

// Note: The global logout function is no longer needed in script.js as it's handled by a link to logout.php in welcome.php