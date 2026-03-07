<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer QR Code - BarCIE</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .qr-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .qr-container h1 {
            color: #1e3c72;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .qr-container p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .qr-code-wrapper {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 3px solid #1e3c72;
        }
        
        .qr-code-wrapper img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
        
        #qrcode {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #qrcode img {
            width: 100% !important;
            height: auto !important;
            max-width: 280px;
        }
        
        .bank-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: left;
        }
        
        .bank-details h3 {
            color: #1e3c72;
            margin-bottom: 15px;
            font-size: 18px;
            text-align: center;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        
        .detail-value {
            color: #333;
            font-weight: 500;
        }
        
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #856404;
        }
        
        .instructions h4 {
            margin-bottom: 10px;
            color: #856404;
        }
        
        .instructions ol {
            text-align: left;
            padding-left: 20px;
        }
        
        .instructions li {
            margin: 5px 0;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .qr-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h1>💳 Bank Transfer Payment</h1>
        <p>Scan the QR code below with your banking app</p>
        
        <div class="qr-code-wrapper">
            <!-- QR Code will be generated here -->
            <div id="qrcode" style="width: 280px; height: 280px; margin: 0 auto; background: white; display: flex; align-items: center; justify-content: center;"></div>
        </div>
        
        <div class="bank-details">
            <h3>Bank Account Details</h3>
            <div class="detail-row">
                <span class="detail-label">Bank Name:</span>
                <span class="detail-value">BDO / BPI / GCash</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Name:</span>
                <span class="detail-value">La Consolacion University Philippines</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Number:</span>
                <span class="detail-value">575-7-575007089</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Branch:</span>
                <span class="detail-value">Malolos Mc Arthur</span>
            </div>
        </div>
        
        <div class="instructions">
            <h4>📝 Payment Instructions:</h4>
            <ol>
                <li>Scan the QR code using your bank's mobile app</li>
                <li>Enter the payment amount as indicated in your booking</li>
                <li>Complete the transaction in your banking app</li>
                <li>Take a screenshot of the payment confirmation</li>
                <li>Upload the proof of payment in your booking form</li>
            </ol>
        </div>
        
        <p style="margin-top: 20px; font-size: 12px; color: #999;">
            For assistance, contact: pc.clemente11@gmail.com
        </p>
    </div>
    
    <script>
        // Generate QR Code when page loads
        window.addEventListener('DOMContentLoaded', function() {
            const qrContainer = document.getElementById('qrcode');
            
            if (qrContainer) {
                // Bank transfer details to encode
                const bankDetails = {
                    accountName: 'La Consolacion University Philippines',
                    accountNumber: '575-7-575007089',
                    branch: 'Malolos Mc Arthur',
                    bank: 'BDO/BPI/GCash'
                };
                
                // Create formatted text for QR code
                const qrText = `Bank Transfer Payment\n\nAccount Name: ${bankDetails.accountName}\nAccount Number: ${bankDetails.accountNumber}\nBranch: ${bankDetails.branch}\nBank: ${bankDetails.bank}\n\nBarCIE International Center`;
                
                try {
                    // Generate QR code using QRCode.js library
                    if (typeof QRCode !== 'undefined') {
                        new QRCode(qrContainer, {
                            text: qrText,
                            width: 280,
                            height: 280,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    } else {
                        // Fallback: use API to generate QR code image
                        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(qrText)}`;
                        const img = document.createElement('img');
                        img.src = qrApiUrl;
                        img.alt = 'Bank Transfer QR Code';
                        img.style.width = '100%';
                        img.style.height = 'auto';
                        img.style.maxWidth = '280px';
                        img.style.borderRadius = '10px';
                        qrContainer.appendChild(img);
                    }
                } catch (err) {
                    console.error('Failed to generate QR code:', err);
                    qrContainer.innerHTML = '<p style="color: #dc3545;">Failed to generate QR code. Please use the account details below.</p>';
                }
            }
        });
    </script>
</body>
</html>
