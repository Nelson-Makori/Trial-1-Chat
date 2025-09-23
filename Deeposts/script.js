let currentUser = JSON.parse(localStorage.getItem("currentUser") || "null");
if (!currentUser) {
  window.location = "login1.html";
}

document.getElementById("profilePic").textContent = currentUser.username[0].toUpperCase();
document.getElementById("miniPic").textContent = currentUser.username[0].toUpperCase();
document.getElementById("profileName").textContent = currentUser.username;

const feed = document.getElementById("feed");
const sendBtn = document.getElementById("sendButton");
const postInput = document.getElementById("postInput");
let posts = JSON.parse(localStorage.getItem("posts") || "[]");

function savePosts() {
  localStorage.setItem("posts", JSON.stringify(posts));
}

function logout() {
  localStorage.removeItem("currentUser");
  window.location = "login1.html";
}

function renderPosts() {
  feed.innerHTML = "";
  posts.forEach((post, i) => {
    const el = document.createElement("div");
    el.className = "post-card";
    el.innerHTML = `
      <div class="post-header">
        <div class="profile-pic">${post.user[0].toUpperCase()}</div>
        <div class="user-info"><h4>${post.user}</h4><span class="timestamp">${post.time}</span></div>
      </div>
      <div class="post-content"><p>${post.text}</p><span class="edit-btn post-edit">✏️ Edit</span></div>
      <div class="post-footer">
        <div class="post-actions">
          <div class="action-item like"><i class="fa-solid fa-heart"></i> ${post.likes}</div>
          <div class="action-item comment"><i class="fa-solid fa-comment"></i> ${post.comments.length}</div>
        </div>
      </div>
      <div class="comments">
        ${post.comments.map((c, ci) => `<div class="comment"><span>${c}</span><span class="edit-btn comment-edit" data-ci="${ci}">✏️</span></div>`).join("")}
        <input type="text" class="comment-input" placeholder="Write a comment..."/>
      </div>`;
    
    el.querySelector(".like").onclick = () => {
      post.likes++;
      savePosts();
      renderPosts();
    };

    el.querySelector(".post-edit").onclick = () => {
      let nt = prompt("Edit post:", post.text);
      if (nt) {
        post.text = nt;
        savePosts();
        renderPosts();
      }
    };
	
	

    el.querySelector(".comment-input").onkeypress = e => {
      if (e.key === "Enter" && e.target.value.trim()) {
        post.comments.push(e.target.value.trim());
        savePosts();
        renderPosts();
      }
    };

    el.querySelectorAll(".comment-edit").forEach(btn => {
      btn.onclick = () => {
        let ci = btn.dataset.ci;
        let nc = prompt("Edit comment:", post.comments[ci]);
        if (nc) {
          post.comments[ci] = nc;
          savePosts();
          renderPosts();
        }
      };
    });

    feed.appendChild(el);
  });
}

sendBtn.onclick = () => {
  if (postInput.value.trim()) {
    posts.unshift({
      user: currentUser.username,
      text: postInput.value.trim(),
      time: new Date().toLocaleString(),
      likes: 0,
      comments: []
    });
    savePosts();
    renderPosts();
    postInput.value = "";
  }
};

renderPosts();