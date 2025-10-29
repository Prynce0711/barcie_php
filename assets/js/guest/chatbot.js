const chatbotKnowledgeBase = {
  'room availability': { response: "To check room availability, simply visit our Availability Calendar section. You can also browse our Rooms & Facilities page to see real-time availability status. Green indicators mean the room is available!", quickReplies: ['booking process', 'pricing'] },
  'booking process': { response: "Booking is easy! Navigate to 'Booking & Reservation', choose between Reservation (for rooms) or Pencil Booking (for function halls), fill in the required details, and submit. No account needed! You'll receive a confirmation email.", quickReplies: ['discount', 'payment'] },
  'pricing': { response: "Our pricing varies by room type and facility. Room rates start from ₱1,500/night for standard rooms. Function hall rates depend on capacity and duration. You can see exact prices when browsing our Rooms & Facilities section.", quickReplies: ['discount', 'amenities'] },
  'facilities': { response: "We offer comfortable rooms, function halls for events, free WiFi, complimentary parking, and 24/7 front desk service. All rooms are air-conditioned with modern amenities.", quickReplies: ['room availability', 'booking process'] },
  'discount': { response: "We offer discounts! PWD/Senior Citizens get 20%, LCUP Personnel get 10%, and LCUP Students/Alumni get 7%. Just select your discount type when booking and upload a valid ID.", quickReplies: ['booking process', 'payment'] },
  'payment': { response: "Payment details and methods will be provided after your booking is confirmed. We accept various payment options for your convenience.", quickReplies: ['booking process', 'cancellation'] },
  'cancellation': { response: "For cancellation or modification of bookings, please contact us at +63 912 345 6789 or visit our Contact section. We'll assist you promptly.", quickReplies: ['contact', 'booking process'] },
  'contact': { response: "You can reach us at:\n📞 +63 912 345 6789\n📧 Through our contact form\n🕐 Check-in: 2:00 PM\n🕐 Check-out: 12:00 PM", quickReplies: ['booking process', 'facilities'] },
  'amenities': { response: "All rooms include: Air conditioning, Free WiFi, Clean linens & towels, 24/7 security, and complimentary parking. Function halls have audio-visual equipment available.", quickReplies: ['pricing', 'booking process'] },
  'checkin': { response: "Check-in time is 2:00 PM onwards. Early check-in may be available depending on room availability. Please contact us in advance if you need early check-in.", quickReplies: ['checkout', 'contact'] },
  'checkout': { response: "Check-out time is 12:00 PM. Late check-out may be available upon request and subject to room availability. Additional charges may apply.", quickReplies: ['checkin', 'payment'] }
};

function toggleChatbot() {
  const container = document.getElementById('chatbotContainer');
  const toggle = document.getElementById('chatbotToggle');
  const badge = toggle.querySelector('.chatbot-badge');
  container.classList.toggle('active');
  if (container.classList.contains('active')) {
    document.getElementById('chatbotInput').focus();
    if (badge) badge.style.display = 'none';
  }
}

// AI toggle state helpers (persist in sessionStorage)
function useAiEnabled() {
  try { const v = sessionStorage.getItem('chatbot_use_ai'); return v === null ? true : v === '1'; } catch (e) { return true; }
}

function setAiEnabled(enabled) {
  try { sessionStorage.setItem('chatbot_use_ai', enabled ? '1' : '0'); } catch (e) {}
  updateAiToggleUI(enabled);
}

function updateAiToggleUI(enabled) {
  const cb = document.getElementById('chatbotAiToggle');
  if (cb) cb.checked = !!enabled;
  updateStatusLabel(enabled ? 'ai' : 'local');
}

function updateStatusLabel(source) {
  const el = document.getElementById('chatbotAiStatus');
  if (!el) return;
  if (source === 'ai') { el.textContent = 'AI'; el.classList.remove('text-white-50'); el.style.opacity = 1; }
  else if (source === 'local') { el.textContent = 'Local KB'; el.classList.add('text-white-50'); el.style.opacity = 0.9; }
  else { el.textContent = 'Unknown'; el.classList.add('text-white-50'); }
}

function sendChatbotMessage() {
  const input = document.getElementById('chatbotInput');
  const message = input.value.trim();
  if (!message) return;
  addUserMessage(message);
  input.value = '';
  // If AI is enabled, try backend (which may call LLM). Otherwise use local KB.
  (async () => {
    const inputEl = document.getElementById('chatbotInput');
    if (inputEl) inputEl.disabled = true;
    showTypingIndicator();
    try {
      if (useAiEnabled()) {
        const data = await queryChatbotAPI(message);
        hideTypingIndicator();
        if (data && data.answer) {
          addBotMessage(data.answer, data.quickReplies || []);
          updateStatusLabel('ai');
        } else {
          const response = generateResponse(message);
          addBotMessage(response.text, response.quickReplies);
          updateStatusLabel('local');
        }
      } else {
        // AI disabled - local response only
        hideTypingIndicator();
        const response = generateResponse(message);
        addBotMessage(response.text, response.quickReplies);
        updateStatusLabel('local');
      }
    } catch (err) {
      hideTypingIndicator();
      const response = generateResponse(message);
      addBotMessage(response.text, response.quickReplies);
      updateStatusLabel('local');
    } finally {
      if (inputEl) inputEl.disabled = false;
    }
  })();
}

function handleChatbotEnter(event) { if (event.key === 'Enter') sendChatbotMessage(); }

function sendQuickReply(query) {
  addUserMessage(query);
  (async () => {
    const inputEl = document.getElementById('chatbotInput');
    if (inputEl) inputEl.disabled = true;
    showTypingIndicator();
    try {
      if (useAiEnabled()) {
        const data = await queryChatbotAPI(query);
        hideTypingIndicator();
        if (data && data.answer) {
          addBotMessage(data.answer, data.quickReplies || []);
          updateStatusLabel('ai');
        } else {
          const response = generateResponse(query);
          addBotMessage(response.text, response.quickReplies);
          updateStatusLabel('local');
        }
      } else {
        hideTypingIndicator();
        const response = generateResponse(query);
        addBotMessage(response.text, response.quickReplies);
        updateStatusLabel('local');
      }
    } catch (err) {
      hideTypingIndicator();
      const response = generateResponse(query);
      addBotMessage(response.text, response.quickReplies);
      updateStatusLabel('local');
    } finally {
      if (inputEl) inputEl.disabled = false;
    }
  })();
}

// Sends the user's message to the backend API which can provide project-level answers
async function queryChatbotAPI(message) {
  try {
    // Use a relative path and include message as query param as a fallback for servers that don't pass JSON body
    const url = 'api/chatbot_answer.php?message=' + encodeURIComponent(message);
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message })
    });
    if (!res.ok) return null;
    const j = await res.json();
    return j;
  } catch (e) {
    console.error('chatbot API error', e);
    return null;
  }
}

// Initialize toggle UI on load
document.addEventListener('DOMContentLoaded', () => {
  const cb = document.getElementById('chatbotAiToggle');
  if (cb) {
    // default to enabled if not set
    const enabled = useAiEnabled();
    cb.checked = enabled;
    cb.addEventListener('change', (e) => {
      setAiEnabled(!!e.target.checked);
    });
  }
  // reflect initial status
  updateAiToggleUI(useAiEnabled());
});

function addUserMessage(message) {
  const messagesContainer = document.getElementById('chatbotMessages');
  const messageDiv = document.createElement('div');
  messageDiv.className = 'chatbot-message user-message';
  messageDiv.innerHTML = `
    <div class="message-avatar"><i class="fas fa-user"></i></div>
    <div class="message-content"><p>${message}</p></div>
  `;
  messagesContainer.appendChild(messageDiv);
  scrollToBottom();
}

function addBotMessage(message, quickReplies = []) {
  const messagesContainer = document.getElementById('chatbotMessages');
  const messageDiv = document.createElement('div');
  messageDiv.className = 'chatbot-message bot-message';
  let quickRepliesHTML = '';
  if (quickReplies.length > 0) {
    quickRepliesHTML = '<div class="quick-replies mt-2">';
    quickReplies.forEach(reply => {
      const label = reply.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
      quickRepliesHTML += `<button class="quick-reply-btn" onclick="sendQuickReply('${reply}')"><i class="fas fa-arrow-right me-1"></i>${label}</button>`;
    });
    quickRepliesHTML += '</div>';
  }
  messageDiv.innerHTML = `
    <div class="message-avatar"><i class="fas fa-robot"></i></div>
    <div class="message-content"><p>${message}</p>${quickRepliesHTML}</div>
  `;
  messagesContainer.appendChild(messageDiv);
  scrollToBottom();
}

function showTypingIndicator() {
  const messagesContainer = document.getElementById('chatbotMessages');
  const typingDiv = document.createElement('div');
  typingDiv.className = 'chatbot-message bot-message typing-indicator-message';
  typingDiv.innerHTML = `
    <div class="message-avatar"><i class="fas fa-robot"></i></div>
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

function hideTypingIndicator() { const typing = document.querySelector('.typing-indicator-message'); if (typing) typing.remove(); }

function generateResponse(message) {
  const lowerMessage = message.toLowerCase();
  for (const [key, value] of Object.entries(chatbotKnowledgeBase)) {
    if (lowerMessage.includes(key) || lowerMessage.includes(key.replace(/ /g, ''))) {
      return { text: value.response, quickReplies: value.quickReplies || [] };
    }
  }
  if (lowerMessage.includes('thank') || lowerMessage.includes('thanks')) {
    return { text: "You're welcome! Is there anything else I can help you with?", quickReplies: ['room availability', 'booking process', 'contact'] };
  }
  if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
    return { text: "Hello! How can I assist you today?", quickReplies: ['room availability', 'booking process', 'facilities'] };
  }
  return { text: "I'd be happy to help! Here are some topics I can assist you with. Please choose one or ask me anything specific about BarCIE International Center.", quickReplies: ['room availability', 'booking process', 'pricing', 'facilities'] };
}

function scrollToBottom() { const messagesContainer = document.getElementById('chatbotMessages'); messagesContainer.scrollTop = messagesContainer.scrollHeight; }
