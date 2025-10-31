
(function () {
  // Fetch notifications from backend
  let notifications = [];
  
  const list = document.getElementById("conversationItems");
  const conversationList = document.querySelector(".conversation-list");
  const chatbox = document.getElementById("chatbox");
  const chatTitle = document.getElementById("chatTitle");
  const chatMessages = document.getElementById("chatMessages");
  const messageInput = document.getElementById("messageInput");
  const newBadge = document.querySelector(".status-pill.new");
  let currentNotification = null;

  // Fetch notifications on page load
  async function fetchNotifications() {
    try {
      const response = await fetch('php/getNotifications.php');
      
      // Check if response is ok
      if (!response.ok) {
        throw new Error('HTTP error ' + response.status);
      }
      
      // Get response text first to debug
      const text = await response.text();
      
      // Try to parse as JSON
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('Response is not valid JSON:', text.substring(0, 200));
        list.innerHTML = '<p style="padding: 20px; text-align: center; color: #e74c3c;">Error loading notifications. Please check if you are logged in.</p>';
        return;
      }
      
      if (data.success) {
        notifications = data.notifications || [];
        if (newBadge) {
          const count = data.unread_count || 0;
          newBadge.textContent = count + ' new';
          if (count === 0) {
            newBadge.style.display = 'none';
          }
        }
        renderList();
      } else {
        console.error('Failed to load notifications:', data.message);
        if (data.message === 'Not authenticated') {
          list.innerHTML = '<p style="padding: 20px; text-align: center; color: #e74c3c;">Please <a href="loginPage.html">login</a> to view notifications.</p>';
        } else {
          list.innerHTML = '<p style="padding: 20px; text-align: center; color: #e74c3c;">Error: ' + data.message + '</p>';
        }
      }
    } catch (error) {
      console.error('Error fetching notifications:', error);
      list.innerHTML = '<p style="padding: 20px; text-align: center; color: #e74c3c;">Network error. Please check your connection.</p>';
    }
  }

  function renderList() {
    list.innerHTML = "";
    
    if (notifications.length === 0) {
      list.innerHTML = '<p style="padding: 20px; text-align: center; color: #666;">No notifications yet</p>';
      return;
    }
    
    notifications.forEach((notif) => {
      const item = document.createElement("div");
      item.className = "conversation-item";
      if (notif.Is_read == 0) {
        item.classList.add("unread");
      }
      if (currentNotification && currentNotification.NotificationID === notif.NotificationID) {
        item.classList.add("active");
      }
      
      // Format timestamp
      const timestamp = new Date(notif.Notification_Timestamp);
      const now = new Date();
      const diffMs = now - timestamp;
      const diffMins = Math.floor(diffMs / 60000);
      let timeStr = '';
      
      if (diffMins < 1) {
        timeStr = 'Just now';
      } else if (diffMins < 60) {
        timeStr = diffMins + 'm ago';
      } else if (diffMins < 1440) {
        timeStr = Math.floor(diffMins / 60) + 'h ago';
      } else {
        timeStr = Math.floor(diffMins / 1440) + 'd ago';
      }
      
      // Create preview (first 60 chars of message)
      const preview = notif.Message.length > 60 ? notif.Message.substring(0, 60) + '...' : notif.Message;
      
      item.innerHTML = `
        <div class="avatar">SW</div>
        <strong>Swap Notification</strong>
        <div class="conversation-meta">
          <span>${timeStr}</span>
        </div>
        <p>${preview}</p>
      `;
      
      if (notif.Is_read == 0) {
        const statusPill = document.createElement("span");
        statusPill.className = "status-pill new";
        statusPill.textContent = "new";
        item.querySelector(".conversation-meta").appendChild(statusPill);
      }
      
      item.addEventListener("click", () => openNotification(notif));
      list.appendChild(item);
    });
  }

  function openNotification(notif) {
    // If notification has ExchangeID, redirect to view swap request page
    if (notif.ExchangeID) {
      window.location.href = 'php/viewSwapRequest.php?exchange_id=' + notif.ExchangeID;
    } else {
      // Otherwise, show in chat view
      chatTitle.textContent = "Swap Notification";
      currentNotification = notif;
      chatMessages.innerHTML = `
        <div class="chat-message other">
          ${notif.Message}
        </div>
      `;
      chatbox.classList.add("is-active");
      conversationList.classList.add("is-hidden");
      renderList();
    }
  }

  function goBack() {
    conversationList.classList.remove("is-hidden");
    chatbox.classList.remove("is-active");
    currentNotification = null;
    chatTitle.textContent = "Chat";
    chatMessages.innerHTML = "";
    messageInput.value = "";
    renderList();
  }

  function sendMessage() {
    // Not applicable for notifications view
    return;
  }

  // Initialize
  fetchNotifications();
  
  window.goBack = goBack;
  window.sendMessage = sendMessage;
})();
