<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Azania Bank Escrow</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3399BB;
            --success-color: #2ecc71;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #333;
            line-height: 1.6;
            padding: 0;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-x: hidden;
        }
        
        .success-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        
        .success-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
            animation: card-popup 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transform: scale(0.8);
            opacity: 0;
        }
        
        @keyframes card-popup {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-header {
            padding: 50px 0 30px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, #2596be 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 5;
            animation: icon-popup 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s both;
            transform: scale(0);
        }
        
        @keyframes icon-popup {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .success-icon i {
            font-size: 50px;
            color: var(--success-color);
            animation: check-mark 0.5s ease-out 0.8s both;
            transform: scale(0);
        }
        
        @keyframes check-mark {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .success-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            z-index: 5;
            animation: fade-in-up 0.5s ease-out 1s both;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .success-subtitle {
            font-size: 18px;
            font-weight: 300;
            margin-bottom: 0;
            opacity: 0;
            position: relative;
            z-index: 5;
            animation: fade-in-up 0.5s ease-out 1.2s both;
            transform: translateY(20px);
        }
        
        @keyframes fade-in-up {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-body {
            padding: 40px;
            opacity: 0;
            animation: fade-in 0.8s ease-out 1.5s forwards;
        }
        
        @keyframes fade-in {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
        
        .details-card {
            background-color: #f8fafc;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }
        
        .details-card h5 {
            color: var(--dark-color);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .details-card h5 i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .detail-row {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
        }
        
        .detail-label {
            font-weight: 500;
            color: #64748b;
            flex: 1;
        }
        
        .detail-value {
            flex: 1;
            text-align: right;
            font-weight: 500;
        }
        
        .alert-custom {
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
            background-color: rgba(51, 153, 187, 0.05);
        }
        
        .alert-custom i {
            color: var(--primary-color);
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 16px;
            color: var(--primary-color);
        }
        
        .btn-azania {
            background: var(--primary-color);
            color: white;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(51, 153, 187, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-azania:hover {
            background: #2980a5;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(51, 153, 187, 0.2);
        }
        
        .btn-azania:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn-azania:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-outline-azania {
            color: var(--primary-color);
            background: transparent;
            border: 1px solid var(--primary-color);
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-outline-azania:hover {
            background: rgba(51, 153, 187, 0.1);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(51, 153, 187, 0.1);
        }
        
        .btn-outline-azania:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(51, 153, 187, 0.05);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn-outline-azania:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .amount-highlight {
            font-size: 20px;
            font-weight: 600;
            color: var(--success-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .success-body {
                padding: 30px 20px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
        /* Enhanced Confetti Animation */
        .confetti-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }
        
        .confetti {
            position: absolute;
            opacity: 0;
            transform-origin: center;
        }
        
        .emoji-popper {
            position: fixed;
            z-index: 9999;
            width: 60px;
            height: 60px;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            animation: pop-emoji 1s ease-out 0.2s forwards;
        }
        
        @keyframes pop-emoji {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 0;
            }
            50% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 1;
            }
            70% {
                transform: translate(-50%, -50%) scale(1.5) rotate(10deg);
                opacity: 1;
            }
            100% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 0;
            }
        }
        
        /* Party popper emoji with animated lines */
        .party-popper {
            position: fixed;
            bottom: 20px;
            left: 20px;
            font-size: 40px;
            z-index: 1000;
            cursor: pointer;
            animation: shake 0.8s ease-in-out 1.5s infinite alternate;
        }
        
        .party-popper:hover {
            animation: shake 0.5s ease-in-out infinite alternate;
        }
        
        @keyframes shake {
            0% {
                transform: rotate(-5deg) scale(1);
            }
            100% {
                transform: rotate(5deg) scale(1.1);
            }
        }
        
        .popper-line {
            position: absolute;
            width: 3px;
            height: 40px;
            background-color: #FF9800;
            bottom: 50px;
            left: 50px;
            opacity: 0;
            transform-origin: bottom;
        }
    </style>
</head>
<body>
    <?php
    // Simple function to format currency amounts based on currency code
    function formatCurrency($amount, $currency) {
        switch ($currency) {
            case 'TZS':
                return 'TZS ' . number_format($amount, 0); // No decimal for TZS
            case 'USD':
                return '$' . number_format($amount, 2);
            case 'EUR':
                return '€' . number_format($amount, 2);
            case 'GBP':
                return '£' . number_format($amount, 2);
            default:
                return $currency . ' ' . number_format($amount, 2);
        }
    }
    ?>

    <!-- Hidden emoji popper that appears on page load -->
    <div class="emoji-popper" id="emojiPopper">🎊</div>

    <div class="success-container">
        <div class="success-card">
            <div class="success-header">
                <!-- Confetti animation container -->
                <div class="confetti-container" id="confettiContainer"></div>
                
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="success-title">Payment Successful!</h1>
                <p class="success-subtitle">Thank you for your payment of <?php echo formatCurrency($transaction->amount, $transaction->currency); ?></p>
            </div>
            
            <div class="success-body">
                <div class="details-card">
                    <h5><i class="fas fa-receipt"></i> Transaction Details</h5>
                    <div class="detail-row">
                        <div class="detail-label">Control Number</div>
                        <div class="detail-value"><?php echo $transaction->control_number; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Item</div>
                        <div class="detail-value"><?php echo $transaction->item_name; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Amount</div>
                        <div class="detail-value amount-highlight"><?php echo formatCurrency($transaction->amount, $transaction->currency); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date</div>
                        <div class="detail-value"><?php echo $transaction->updated_at->format('M d, Y H:i'); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Confirmation Sent To</div>
                        <div class="detail-value"><?php echo $transaction->buyer_email; ?></div>
                    </div>
                </div>
                
                <div class="alert-custom">
                    <div class="alert-title">
                        <i class="fas fa-info-circle me-2"></i> Next Steps
                    </div>
                    <p class="mb-2">
                        We've sent you an email with your transaction details. The seller will prepare your item for shipping.
                    </p>
                    <p class="mb-0">
                        <strong>Want full transaction protection?</strong> 
                        Create an account to track your order, confirm delivery, and access our dispute resolution process.
                    </p>
                </div>
                
                <div class="action-buttons">
                    <a href="<?php echo route('guest.verify'); ?>" class="btn btn-azania">
                        <i class="fas fa-search"></i> Check Another Control Number
                    </a>
                    <a href="<?php echo route('login'); ?>" class="btn btn-outline-azania">
                        <i class="fas fa-user"></i> Log In
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create enhanced confetti animation with different shapes and sizes
            const colors = ['#f2d74e', '#95d3e9', '#ff9d84', '#a3e4d7', '#f9e79f', '#aed6f1', '#f1948a', '#5dade2', '#7dcea0'];
            const shapes = ['circle', 'square', 'triangle', 'rectangle'];
            const confettiContainer = document.getElementById('confettiContainer');
            
            // Create and animate confetti with staggered timing
            for (let i = 0; i < 200; i++) {
                setTimeout(() => {
                    createConfetti(i);
                }, i * 10); // Stagger the creation
            }
            
            function createConfetti(i) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                
                const color = colors[Math.floor(Math.random() * colors.length)];
                const shape = shapes[Math.floor(Math.random() * shapes.length)];
                const size = Math.random() * 10 + 5;
                
                confetti.style.backgroundColor = color;
                confetti.style.width = `${size}px`;
                confetti.style.height = shape === 'rectangle' ? `${size * 1.5}px` : `${size}px`;
                
                if (shape === 'circle') {
                    confetti.style.borderRadius = '50%';
                } else if (shape === 'triangle') {
                    confetti.style.width = '0';
                    confetti.style.height = '0';
                    confetti.style.backgroundColor = 'transparent';
                    confetti.style.borderLeft = `${size/2}px solid transparent`;
                    confetti.style.borderRight = `${size/2}px solid transparent`;
                    confetti.style.borderBottom = `${size}px solid ${color}`;
                }
                
                const startLeft = Math.random() * 100;
                const startRotation = Math.random() * 360;
                
                confetti.style.left = `${startLeft}%`;
                confetti.style.top = '-5%';
                confetti.style.transform = `rotate(${startRotation}deg)`;
                
                confettiContainer.appendChild(confetti);
                
                // Create animation
                const animationDuration = Math.random() * 3 + 3;
                const fallDelay = Math.random() * 2;
                
                confetti.animate([
                    { // start
                        top: '-5%',
                        opacity: 0,
                        transform: `rotate(${startRotation}deg)`
                    },
                    { // become visible
                        top: '5%',
                        opacity: 1,
                        offset: 0.1
                    },
                    { // fall and spin
                        top: `${Math.random() * 50 + 50}%`, 
                        left: `${startLeft + (Math.random() * 20 - 10)}%`,
                        opacity: 1,
                        transform: `rotate(${startRotation + 360 * 2}deg)`,
                        offset: 0.8
                    },
                    { // fade out
                        top: '100%',
                        left: `${startLeft + (Math.random() * 40 - 20)}%`,
                        opacity: 0,
                        transform: `rotate(${startRotation + 360 * 3}deg)`
                    }
                ], {
                    duration: animationDuration * 1000,
                    delay: fallDelay * 1000,
                    easing: 'cubic-bezier(0.21, 0.98, 0.6, 0.99)',
                    fill: 'forwards'
                });
                
                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, (animationDuration + fallDelay) * 1000 + 100);
            }
            
            function createPopperLines() {
                // Create popper lines that shoot out
                const lineColors = ['#FF9800', '#FF5722', '#FFEB3B', '#4CAF50', '#2196F3'];
                
                for (let i = 0; i < 8; i++) {
                    const line = document.createElement('div');
                    line.className = 'popper-line';
                    line.style.backgroundColor = lineColors[Math.floor(Math.random() * lineColors.length)];
                    
                    // Random angle for the line
                    const angle = (i * 45) + Math.random() * 20 - 10;
                    line.style.transform = `rotate(${angle}deg)`;
                    
                    document.body.appendChild(line);
                    
                    // Animate the line shooting out
                    line.animate([
                        { // start
                            height: '0',
                            opacity: 0,
                        },
                        { // shoot out
                            height: '100px',
                            opacity: 1,
                            offset: 0.3
                        },
                        { // fade
                            height: '120px',
                            opacity: 0,
                        }
                    ], {
                        duration: 600,
                        easing: 'cubic-bezier(0.22, 0.61, 0.36, 1)',
                        fill: 'forwards'
                    });
                    
                    // Remove line after animation
                    setTimeout(() => {
                        line.remove();
                    }, 700);
                }
            }
        });
    </script>
</body>
</html>