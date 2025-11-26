<!DOCTYPE html>
<html>
<head>
    <title>Chatbot Test</title>
    <script>
        async function testChatbot() {
            const message = document.getElementById('testMessage').value;
            const output = document.getElementById('output');
            
            output.innerHTML = 'Testing...';
            
            try {
                const res = await fetch('api/chatbot_answer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message })
                });
                
                const data = await res.json();
                output.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                
                // Test local KB fallback
                if (data.answer === null) {
                    const localKB = {
                        'pricing': 'Standard Rooms start from ₱1,500/night, Deluxe Rooms from ₱2,500/night.',
                        'booking': 'Visit Booking & Reservation section and fill in your details.',
                        'default': 'I can help with: room availability, booking process, pricing, facilities.'
                    };
                    
                    let response = localKB.default;
                    if (message.toLowerCase().includes('price') || message.toLowerCase().includes('cost')) {
                        response = localKB.pricing;
                    } else if (message.toLowerCase().includes('book')) {
                        response = localKB.booking;
                    }
                    
                    output.innerHTML += '<hr><strong>Local KB Response:</strong><br>' + response;
                }
            } catch (err) {
                output.innerHTML = 'Error: ' + err.message;
            }
        }
    </script>
</head>
<body>
    <h1>Chatbot API Test</h1>
    <p>Test questions:</p>
    <ul>
        <li>how much are rooms?</li>
        <li>how do I book?</li>
        <li>what facilities do you have?</li>
        <li>tell me about discounts</li>
    </ul>
    
    <input type="text" id="testMessage" value="how much are rooms?" style="width:400px">
    <button onclick="testChatbot()">Test</button>
    
    <div id="output" style="margin-top:20px; padding:15px; border:1px solid #ccc; background:#f5f5f5;"></div>
</body>
</html>
