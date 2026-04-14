const chatbotKnowledgeBase = {
  "room availability": {
    response:
      "To check room availability, visit our Availability Calendar section. You can see real-time status - green means available! We have Standard Rooms, Deluxe Rooms, and Function Halls available for booking.",
    quickReplies: ["booking process", "pricing", "facilities"],
  },
  "booking process": {
    response:
      "Booking is easy! Go to 'Booking & Reservation', choose Reservation (rooms) or Pencil Booking (function halls), fill in your details, upload required IDs if claiming discount, and submit. You'll receive a confirmation email with payment details.",
    quickReplies: ["discount", "payment", "requirements"],
  },

  pricing: {
    response:
      "Standard Rooms start from ₱1,500/night, Deluxe Rooms from ₱2,500/night. Function halls vary by capacity (₱3,000-₱8,000). Discounts available: PWD/Senior 20%, LCUP Personnel 10%, Students/Alumni 7%.",
    quickReplies: ["discount", "booking process"],
  },
  facilities: {
    response:
      "We offer: Comfortable air-conditioned rooms, Function halls (50-200 capacity), Free WiFi throughout, Complimentary parking, 24/7 front desk & security, Clean linens & towels, Modern amenities in all rooms.",
    quickReplies: ["room availability", "amenities"],
  },
  discount: {
    response:
      "Available discounts: PWD/Senior Citizens: 20%, LCUP Personnel: 10%, LCUP Students/Alumni: 7%. Select your discount type when booking and upload a valid ID (School ID, PWD card, etc.) for verification.",
    quickReplies: ["booking process", "requirements"],
  },
  payment: {
    response:
      "After booking approval, you'll receive payment instructions via email. We accept bank transfers, GCash, and on-site payments. A deposit may be required for reservations. QR code for payment will be provided.",
    quickReplies: ["booking process", "cancellation"],
  },
  cancellation: {
    response:
      "To cancel or modify your booking, contact us immediately at barcieinternationalcenter.web@gmail.com or through our contact form. Cancellation policies apply - contact us for details.",
    quickReplies: ["contact", "booking process"],
  },
  contact: {
    response:
      "BarCIE International Center\n📧 barcieinternationalcenter.web@gmail.com\n📍 La Consolacion University Philippines\n🕐 Check-in: 2:00 PM | Check-out: 12:00 PM\n📅 Available for inquiries 24/7",
    quickReplies: ["booking process", "location"],
  },
  amenities: {
    response:
      "All rooms include: Air conditioning, Free WiFi, Cable TV, Private bathroom, Clean linens & towels, Desk & chair, Wardrobe. Function halls include: Tables & chairs, Audio system, Projector/screen (on request), Air conditioning.",
    quickReplies: ["pricing", "facilities"],
  },
  checkin: {
    response:
      "Check-in: 2:00 PM onwards. Early check-in may be available if the room is ready (subject to confirmation). Please bring valid ID and booking confirmation. Late arrivals are accommodated 24/7.",
    quickReplies: ["checkout", "requirements"],
  },
  checkout: {
    response:
      "Check-out: 12:00 PM (noon). Late check-out available upon request (additional charges may apply, subject to room availability). Please settle all bills before checkout and return room keys.",
    quickReplies: ["checkin", "payment"],
  },
  requirements: {
    response:
      "For booking: Valid ID (required), Contact details, Check-in/out dates. For discounts: Valid ID (School ID for students, PWD card, Company ID). Photo upload required during booking for verification.",
    quickReplies: ["discount", "booking process"],
  },
  location: {
    response:
      "BarCIE International Center is located at La Consolacion University Philippines campus. Easily accessible with ample parking. Near restaurants, shops, and city center.",
    quickReplies: ["contact", "facilities"],
  },
  "function hall": {
    response:
      "We offer multiple function halls for events, conferences, meetings: Small (50pax), Medium (100pax), Large (200pax). All include tables, chairs, air conditioning. Audio-visual equipment available on request.",
    quickReplies: ["pricing", "booking process"],
  },
  "guest portal": {
    response:
      "Our Guest Portal features: Overview dashboard, Availability Calendar (real-time), Rooms & Facilities browser, Booking & Reservation system, Feedback submission. No account needed - book directly!",
    quickReplies: ["booking process", "room availability"],
  },
  features: {
    response:
      "Website features: Real-time availability checking, Online booking system, Pencil booking for function halls, Discount application with ID upload, Email confirmations, Guest feedback system, AI-powered chatbot assistance.",
    quickReplies: ["guest portal", "booking process"],
  },
};

function toggleChatbot() {
  const container = document.getElementById("chatbotContainer");
  const toggle = document.getElementById("chatbotToggle");
  const badge = toggle.querySelector(".chatbot-badge");
  container.classList.toggle("active");
  if (container.classList.contains("active")) {
    document.getElementById("chatbotInput").focus();
    if (badge) badge.style.display = "none";
  }
}

// AI toggle state helpers (persist in sessionStorage)
function useAiEnabled() {
  try {
    const v = sessionStorage.getItem("chatbot_use_ai");
    return v === null ? true : v === "1";
  } catch (e) {
    return true;
  }
}

function setAiEnabled(enabled) {
  try {
    sessionStorage.setItem("chatbot_use_ai", enabled ? "1" : "0");
  } catch (e) {}
  updateAiToggleUI(enabled);
}

function updateAiToggleUI(enabled) {
  const cb = document.getElementById("chatbotAiToggle");
  if (cb) cb.checked = !!enabled;
  updateStatusLabel(enabled ? "ai" : "local");
}

function updateStatusLabel(source) {
  const el = document.getElementById("chatbotAiStatus");
  if (!el) return;
  if (source === "ai") {
    el.textContent = "AI Mode";
    el.style.color = "#10b981";
    el.style.fontWeight = "600";
  } else if (source === "local") {
    el.textContent = "Local KB";
    el.style.color = "rgba(255, 255, 255, 0.8)";
    el.style.fontWeight = "500";
  } else {
    el.textContent = "Unknown";
    el.style.color = "rgba(255, 255, 255, 0.6)";
  }
}

function sendChatbotMessage() {
  const input = document.getElementById("chatbotInput");
  const message = input.value.trim();
  if (!message) return;
  addUserMessage(message);
  input.value = "";
  // If AI is enabled, try backend (which may call LLM). Otherwise use local KB.
  (async () => {
    const inputEl = document.getElementById("chatbotInput");
    if (inputEl) inputEl.disabled = true;
    showTypingIndicator();
    try {
      if (useAiEnabled()) {
        const data = await queryChatbotAPI(message);
        hideTypingIndicator();
        if (data && data.answer) {
          addBotMessage(data.answer, data.quickReplies || []);
          updateStatusLabel("ai");
        } else {
          const response = generateResponse(message);
          addBotMessage(response.text, response.quickReplies);
          updateStatusLabel("local");
        }
      } else {
        // AI disabled - local response only
        hideTypingIndicator();
        const response = generateResponse(message);
        addBotMessage(response.text, response.quickReplies);
        updateStatusLabel("local");
      }
    } catch (err) {
      hideTypingIndicator();
      const response = generateResponse(message);
      addBotMessage(response.text, response.quickReplies);
      updateStatusLabel("local");
    } finally {
      if (inputEl) inputEl.disabled = false;
    }
  })();
}

function handleChatbotEnter(event) {
  if (event.key === "Enter") sendChatbotMessage();
}

function sendQuickReply(query) {
  addUserMessage(query);
  (async () => {
    const inputEl = document.getElementById("chatbotInput");
    if (inputEl) inputEl.disabled = true;
    showTypingIndicator();
    try {
      if (useAiEnabled()) {
        const data = await queryChatbotAPI(query);
        hideTypingIndicator();
        if (data && data.answer) {
          addBotMessage(data.answer, data.quickReplies || []);
          updateStatusLabel("ai");
        } else {
          const response = generateResponse(query);
          addBotMessage(response.text, response.quickReplies);
          updateStatusLabel("local");
        }
      } else {
        hideTypingIndicator();
        const response = generateResponse(query);
        addBotMessage(response.text, response.quickReplies);
        updateStatusLabel("local");
      }
    } catch (err) {
      hideTypingIndicator();
      const response = generateResponse(query);
      addBotMessage(response.text, response.quickReplies);
      updateStatusLabel("local");
    } finally {
      if (inputEl) inputEl.disabled = false;
    }
  })();
}

// Sends the user's message to the backend API which can provide project-level answers
async function queryChatbotAPI(message) {
  try {
    // Use a relative path and include message as query param as a fallback for servers that don't pass JSON body
    const apiBase =
      (window.BARCIE_GUEST && window.BARCIE_GUEST.apiBaseUrl) || "api";
    const url = `${apiBase}/ChatbotAnswer.php?message=` + encodeURIComponent(message);
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message }),
    });
    if (!res.ok) return null;
    const j = await res.json();
    return j;
  } catch (e) {
    console.error("chatbot API error", e);
    return null;
  }
}

// Initialize toggle UI on load
document.addEventListener("DOMContentLoaded", () => {
  const cb = document.getElementById("chatbotAiToggle");
  if (cb) {
    // default to enabled if not set
    const enabled = useAiEnabled();
    cb.checked = enabled;
    cb.addEventListener("change", (e) => {
      setAiEnabled(!!e.target.checked);
    });
  }
  // reflect initial status
  updateAiToggleUI(useAiEnabled());
});

function addUserMessage(message) {
  const messagesContainer = document.getElementById("chatbotMessages");
  const messageDiv = document.createElement("div");
  messageDiv.className = "chatbot-message user-message";
  messageDiv.innerHTML = `
    <div class="message-avatar"><i class="fas fa-user"></i></div>
    <div class="message-content"><p>${message}</p></div>
  `;
  messagesContainer.appendChild(messageDiv);
  scrollToBottom();
}

function addBotMessage(message, quickReplies = []) {
  const messagesContainer = document.getElementById("chatbotMessages");
  const messageDiv = document.createElement("div");
  messageDiv.className = "chatbot-message bot-message";
  let quickRepliesHTML = "";
  if (quickReplies.length > 0) {
    quickRepliesHTML = '<div class="quick-replies mt-2">';
    quickReplies.forEach((reply) => {
      const label = reply
        .replace(/_/g, " ")
        .replace(/\b\w/g, (l) => l.toUpperCase());
      quickRepliesHTML += `<button class="quick-reply-btn" onclick="sendQuickReply('${reply}')"><i class="fas fa-arrow-right me-1"></i>${label}</button>`;
    });
    quickRepliesHTML += "</div>";
  }
  messageDiv.innerHTML = `
    <div class="message-avatar"><i class="fas fa-hotel"></i></div>
    <div class="message-content"><p>${message}</p>${quickRepliesHTML}</div>
  `;
  messagesContainer.appendChild(messageDiv);
  scrollToBottom();
}

function showTypingIndicator() {
  const messagesContainer = document.getElementById("chatbotMessages");
  const typingDiv = document.createElement("div");
  typingDiv.className = "chatbot-message bot-message typing-indicator-message";
  typingDiv.innerHTML = `
    <div class="message-avatar"><i class="fas fa-hotel"></i></div>
    <div class="message-content">
      <div class="typing-indicator">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
    </div>
  `;
  messagesContainer.appendChild(typingDiv);
  scrollToBottom();
}

function hideTypingIndicator() {
  const typing = document.querySelector(".typing-indicator-message");
  if (typing) typing.remove();
}

function generateResponse(message) {
  const lowerMessage = message.toLowerCase();
  for (const [key, value] of Object.entries(chatbotKnowledgeBase)) {
    if (
      lowerMessage.includes(key) ||
      lowerMessage.includes(key.replace(/ /g, ""))
    ) {
      return { text: value.response, quickReplies: value.quickReplies || [] };
    }
  }
  if (lowerMessage.includes("thank") || lowerMessage.includes("thanks")) {
    return {
      text: "You're welcome! Is there anything else I can help you with?",
      quickReplies: ["room availability", "booking process", "contact"],
    };
  }
  if (
    lowerMessage.includes("hello") ||
    lowerMessage.includes("hi") ||
    lowerMessage.includes("hey")
  ) {
    return {
      text: "Hello! How can I assist you today?",
      quickReplies: ["room availability", "booking process", "facilities"],
    };
  }
  return {
    text: "I'd be happy to help! Here are some topics I can assist you with. Please choose one or ask me anything specific about BarCIE International Center.",
    quickReplies: [
      "room availability",
      "booking process",
      "pricing",
      "facilities",
    ],
  };
}

function scrollToBottom() {
  const messagesContainer = document.getElementById("chatbotMessages");
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}
