<div class="chatbot-container" id="chatbotContainer">
  <div class="chatbot-header">
    <div class="d-flex align-items-center">
      <div class="chatbot-avatar me-2"><i class="fas fa-hotel"></i></div>
      <div>
        <h6 class="mb-0">BarCIE Assistant</h6>
        <small class="text-white-50">Ask me anything!</small>
      </div>
    </div>
    <div class="d-flex align-items-center">
      <div class="me-2">
        <label class="ai-toggle-label" for="chatbotAiToggle"
          style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
          <div class="toggle-switch">
            <input id="chatbotAiToggle" type="checkbox" onchange="setAiEnabled(this.checked)" />
            <span class="toggle-slider"></span>
          </div>
          <small id="chatbotAiStatus" class="text-white" style="font-size:.8rem;font-weight:500;">Local KB</small>
        </label>
      </div>
      <button class="chatbot-close" onclick="toggleChatbot()"><i class="fas fa-times"></i></button>
    </div>
  </div>

  <div class="chatbot-messages" id="chatbotMessages">
    <div class="chatbot-message bot-message">
      <div class="message-avatar"><i class="fas fa-hotel"></i></div>
      <div class="message-content">
        <p>Hello! 👋 I'm your BarCIE assistant. How can I help you today?</p>
        <div class="quick-replies mt-2">
          <button class="quick-reply-btn" onclick="sendQuickReply('room availability')"><i
              class="fas fa-bed me-1"></i>Room Availability</button>
          <button class="quick-reply-btn" onclick="sendQuickReply('booking process')"><i
              class="fas fa-calendar me-1"></i>How to Book</button>
          <button class="quick-reply-btn" onclick="sendQuickReply('pricing')"><i
              class="fas fa-tag me-1"></i>Pricing</button>
          <button class="quick-reply-btn" onclick="sendQuickReply('facilities')"><i
              class="fas fa-building me-1"></i>Facilities</button>
        </div>
      </div>
    </div>
  </div>

  <div class="chatbot-input">
    <input type="text" id="chatbotInput" placeholder="Type your question..." onkeypress="handleChatbotEnter(event)">
    <button onclick="sendChatbotMessage()"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<button class="chatbot-toggle" id="chatbotToggle" onclick="toggleChatbot()">
  <i class="fas fa-hotel"></i>
  <span class="chatbot-badge">1</span>
</button>

<link rel="stylesheet"
  href="<?php echo htmlspecialchars((defined('GUEST_COMPONENT_BASE_URL') ? GUEST_COMPONENT_BASE_URL : 'Components') . '/Guest/ChatBot/chatbot.css', ENT_QUOTES, 'UTF-8'); ?>">
<script
  src="<?php echo htmlspecialchars((defined('GUEST_COMPONENT_BASE_URL') ? GUEST_COMPONENT_BASE_URL : 'Components') . '/Guest/ChatBot/chatbot.js', ENT_QUOTES, 'UTF-8'); ?>"
  defer></script>
