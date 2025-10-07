
(function () {
  const conversations = [
    {
      name: "Pocket Park Revival",
      avatar: "PR",
      time: "Just now",
      status: "new",
      messages: [
        { sender: "other", text: "Need extra gloves for Saturday?" },
        { sender: "user", text: "I can bring 3 pairs!" }
      ]
    },
    {
      name: "Herb Lab Volunteers",
      avatar: "HL",
      time: "2h ago",
      status: "waiting",
      messages: [
        { sender: "other", text: "Weekly produce drop confirmed." },
        { sender: "user", text: "Yes, I can help document yields." }
      ]
    },
    {
      name: "Swap HQ",
      avatar: "SW",
      time: "Yesterday",
      status: "",
      messages: [
        { sender: "other", text: "Lisa accepted your blender listing." },
        { sender: "user", text: "Great! Let's arrange a meetup." }
      ]
    },
    {
      name: "Compost Cooperative",
      avatar: "CC",
      time: "Mon",
      status: "",
      messages: [
        { sender: "other", text: "Reminder: bring brown materials." }
      ]
    }
  ];

  const list = document.getElementById("conversationItems");
  const conversationList = document.querySelector(".conversation-list");
  const chatbox = document.getElementById("chatbox");
  const chatTitle = document.getElementById("chatTitle");
  const chatMessages = document.getElementById("chatMessages");
  const messageInput = document.getElementById("messageInput");
  let currentChat = null;

  function renderList() {
    list.innerHTML = "";
    conversations.forEach((conv) => {
      const item = document.createElement("div");
      item.className = "conversation-item";
      if (currentChat && currentChat.name === conv.name) {
        item.classList.add("active");
      }
      const preview = conv.messages.length ? conv.messages[conv.messages.length - 1].text : "";
      item.innerHTML = `
        <div class="avatar">${conv.avatar}</div>
        <strong>${conv.name}</strong>
        <div class="conversation-meta">
          <span>${conv.time}</span>
        </div>
        <p>${preview}</p>
      `;
      if (conv.status) {
        const statusPill = document.createElement("span");
        statusPill.className = `status-pill ${conv.status}`;
        statusPill.textContent = conv.status;
        item.querySelector(".conversation-meta").appendChild(statusPill);
      }
      item.addEventListener("click", () => openChat(conv));
      list.appendChild(item);
    });
  }

  function renderMessages(conv) {
    chatMessages.innerHTML = "";
    conv.messages.forEach((msg) => {
      const bubble = document.createElement("div");
      bubble.className = `chat-message ${msg.sender}`;
      bubble.textContent = msg.text;
      chatMessages.appendChild(bubble);
    });
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function openChat(conv) {
    chatTitle.textContent = conv.name;
    currentChat = conv;
    renderMessages(conv);
    chatbox.classList.add("is-active");
    conversationList.classList.add("is-hidden");
    renderList();
  }

  function goBack() {
    conversationList.classList.remove("is-hidden");
    chatbox.classList.remove("is-active");
    currentChat = null;
    chatTitle.textContent = "Chat";
    chatMessages.innerHTML = "";
    messageInput.value = "";
    renderList();
  }

  function sendMessage() {
    const text = messageInput.value.trim();
    if (!text || !currentChat) {
      return;
    }
    currentChat.messages.push({ sender: "user", text });
    messageInput.value = "";
    renderMessages(currentChat);
    renderList();
  }

  renderList();
  window.goBack = goBack;
  window.sendMessage = sendMessage;
})();
