<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | 404</title>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --error: #ef233c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            background-image: radial-gradient(circle at 10% 20%, rgba(67, 97, 238, 0.1) 0%, rgba(248, 249, 250, 1) 90%);
        }
        
        .container {
            max-width: 800px;
            width: 100%;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            position: relative;
            display: inline-block;
        }
        
        .error-code::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 5px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 5px;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
            color: #555;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        
        .btn {
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background-color: rgba(67, 97, 238, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.1);
        }
        
        .search-box {
            max-width: 500px;
            width: 100%;
            margin: 0 auto 3rem;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            border: 1px solid #ddd;
            font-size: 1rem;
            padding-right: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 5px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            transform: scale(1.1);
        }
        
        .animation {
            margin-bottom: 3rem;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="animation">
            <svg id="404-animation" width="300" height="200" viewBox="0 0 300 200" xmlns="http://www.w3.org/2000/svg">
                <!-- This will be our interactive SVG animation -->
                <rect x="0" y="0" width="300" height="200" fill="transparent"></rect>
                <path id="broken-line" d="M50,100 L120,100 L150,50 L180,100 L250,100" stroke="#4361ee" stroke-width="3" fill="none" stroke-dasharray="300" stroke-dashoffset="300"></path>
                <circle id="moving-dot" cx="50" cy="100" r="5" fill="#ef233c"></circle>
            </svg>
        </div>
        
        <div class="error-code">404</div>
        <h1>Oops! Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved. Try searching or go back to our homepage.</p>
        
        <div class="search-box">
            <input type="text" class="search-input" placeholder="Search our website...">
            <button class="search-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </div>
        
        <div class="cta-buttons">
            <a href="home" class="btn btn-primary">Go to Homepage</a>
           
        </div>
    </div>

    <script>
        // Animation for the 404 graphic
        document.addEventListener('DOMContentLoaded', function() {
            const brokenLine = document.getElementById('broken-line');
            const movingDot = document.getElementById('moving-dot');
            
            // Animate the broken path
            brokenLine.style.animation = "dash 2s ease-in-out forwards";
            
            // Animate the moving dot along the path
            let pathLength = brokenLine.getTotalLength();
            let startTime = null;
            
            function animateDot(timestamp) {
                if (!startTime) startTime = timestamp;
                const progress = (timestamp - startTime) / 2000; // 2 seconds
                
                if (progress < 1) {
                    const point = brokenLine.getPointAtLength(progress * pathLength);
                    movingDot.setAttribute('cx', point.x);
                    movingDot.setAttribute('cy', point.y);
                    requestAnimationFrame(animateDot);
                } else {
                    // Bounce effect at the end
                    movingDot.style.animation = "bounce 0.5s ease 2";
                }
            }
            
            // Add CSS animation via JavaScript
            const style = document.createElement('style');
            style.textContent = `
                @keyframes dash {
                    to {
                        stroke-dashoffset: 0;
                    }
                }
                @keyframes bounce {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-10px); }
                }
            `;
            document.head.appendChild(style);
            
            requestAnimationFrame(animateDot);
            
            // Add hover effect to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Search functionality
            const searchBtn = document.querySelector('.search-btn');
            const searchInput = document.querySelector('.search-input');
            
            searchBtn.addEventListener('click', function() {
                if (searchInput.value.trim() !== '') {
                    alert(`Searching for: ${searchInput.value}\n(Note: This is a demo. Implement actual search functionality as needed.)`);
                }
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && this.value.trim() !== '') {
                    alert(`Searching for: ${this.value}\n(Note: This is a demo. Implement actual search functionality as needed.)`);
                }
            });
        });
    </script>
</body>
</html>