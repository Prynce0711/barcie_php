<?php /* migrated from Components/Guest/js/guest-overview-chat.js */ ?>
<script>
  function loadFeaturedItems() {
    if (!window.allItems) {
      setTimeout(loadFeaturedItems, 1000);
      return;
    }

    const featuredContainer = document.getElementById("featured-items");
    if (!featuredContainer) {
      return;
    }

    // Get 3 featured items (mix of rooms and facilities)
    const rooms = window.allItems
      .filter((item) => item.item_type === "room")
      .slice(0, 2);
    const facilities = window.allItems
      .filter((item) => item.item_type === "facility")
      .slice(0, 1);
    const featuredItems = [...rooms, ...facilities];

    featuredContainer.innerHTML = "";

    featuredItems.forEach((item) => {
      const availability = Math.random() > 0.3 ? "Available" : "Occupied";
      const badgeClass =
        availability === "Available" ? "bg-success" : "bg-warning";
      const icon = item.item_type === "room" ? "fa-bed" : "fa-building";

      const itemHtml = `
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 featured-item" data-item-type="${item.item_type
        }">
                    ${item.image
          ? `<img src="${item.image.startsWith("http") || item.image.startsWith("/") ? item.image : "/" + item.image}" class="card-img-top" style="height:120px;object-fit:cover;" alt="${item.name}">`
          : ""
        }
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">
                            <i class="fas ${icon} me-1"></i>${item.name}
                        </h6>
                        <p class="card-text small text-muted mb-2">${item.description
          ? item.description.substring(0, 60) + "..."
          : "Premium accommodation with modern amenities."
        }</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge ${badgeClass} small">${availability}</span>
                            <small class="text-primary fw-bold">₱${parseInt(
          item.price,
        ).toLocaleString()}</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

      featuredContainer.insertAdjacentHTML("beforeend", itemHtml);
    });

    // Add click handlers for featured items
    featuredContainer.addEventListener("click", function (e) {
      const card = e.target.closest(".featured-item");
      if (card) {
        const { itemType } = card.dataset;
        // Switch to rooms section and filter by type (don't save to sessionStorage)
        showSection("rooms", null, false);
        setTimeout(() => {
          const typeRadio = document.querySelector(
            `input[name="type"][value="${itemType}"]`,
          );
          if (typeRadio) {
            typeRadio.checked = true;
            if (typeof window.filterItems === "function") {
              window.filterItems();
            }
          }
        }, 300);
        showToast(`Viewing ${itemType}s in Rooms & Facilities`, "info");
      }
    });
  }

  // Initialize Guest Chat System (Simplified)
  function initializeChatSystem() {
    console.log("Chat system temporarily disabled for feedback system stability");

    // Only initialize basic form handling without server calls
    const chatForm = document.getElementById("chat-form");
    const chatInput = document.getElementById("chat-input");

    if (chatForm && chatInput) {
      chatForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const message = chatInput.value.trim();

        if (message) {
          // Show message locally without server call
          showToast(
            "Message received. Chat system is currently under maintenance.",
            "info",
          );
          chatInput.value = "";
        }
      });
    }
  }

  // Load chat messages for guest (Simplified)
  function loadChatMessages() {
    // Temporarily disabled to prevent errors
    console.log("Chat messages loading disabled for system stability");

    const chatMessages = document.getElementById("chat-messages");
    if (chatMessages) {
      chatMessages.innerHTML = `
      <div class="text-center text-muted">
        <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
        <h5>Chat System Under Maintenance</h5>
        <p>Please use the feedback system to contact us</p>
      </div>
    `;
    }
  }

  // Display chat messages in guest interface
  function displayChatMessages(messages) {
    const chatMessages = document.getElementById("chat-messages");
    if (!chatMessages) {
      return;
    }

    if (messages.length === 0) {
      chatMessages.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
                <h5>Welcome to BarCIE Support</h5>
                <p>Send us a message and we'll respond as soon as possible</p>
            </div>
        `;
      return;
    }

    let messagesHtml = "";

    messages.forEach((message) => {
      const isFromGuest = message.sender_type === "guest";
      const messageClass = isFromGuest ? "sent" : "received";
      const messageTime = new Date(message.created_at).toLocaleString();
      const senderName = isFromGuest ? "You" : "Support";

      messagesHtml += `
            <div class="chat-message ${messageClass}">
                <div class="message-content">
                    ${escapeHtml(message.message)}
                    <div class="message-time">${senderName} • ${messageTime}</div>
                </div>
            </div>
        `;
    });

    chatMessages.innerHTML = messagesHtml;

    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Send chat message from guest (Simplified)
  function sendChatMessage(message) {
    // Temporarily disabled to prevent errors
    showToast(
      "Chat system is under maintenance. Please use the feedback system instead.",
      "info",
    );
  }

  // Send quick message (for quick help buttons)
  function sendQuickMessage(message) {
    const chatInput = document.getElementById("chat-input");
    if (chatInput) {
      chatInput.value = message;
      chatInput.focus();
    }
  }

  // Update unread message count (Simplified)
  function updateUnreadCount() {
    // Temporarily disabled to prevent errors
    const unreadBadge = document.getElementById("unread-count");
    if (unreadBadge) {
      unreadBadge.style.display = "none";
    }
  }

  // Export new functions for global access
  window.sendQuickMessage = sendQuickMessage;
  window.initializeChatSystem = initializeChatSystem;

  // Utility function to escape HTML

</script>
