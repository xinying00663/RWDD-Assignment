// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // ====== TYPING EFFECT ======
    function initTypingEffect() {
        // For homePage.php - typing the user's name
        const nameElement = document.getElementById('profileSummaryName');
        if (nameElement) {
            const fullText = nameElement.textContent;
            nameElement.textContent = '';
            nameElement.style.borderRight = '2px solid #2d8d60';
            nameElement.style.paddingRight = '5px';
            
            let index = 0;
            const typingSpeed = 100;
            
            function typeCharacter() {
                if (index < fullText.length) {
                    nameElement.textContent += fullText.charAt(index);
                    index++;
                    setTimeout(typeCharacter, typingSpeed);
                } else {
                    setTimeout(() => {
                        nameElement.style.borderRight = 'none';
                    }, 500);
                }
            }
            
            setTimeout(typeCharacter, 300);
        }
        
        // For landingPage.html - typing "Welcome to EcoGo!"
        const landingHeading = document.querySelector('.about-us h1');
        if (landingHeading && landingHeading.textContent.includes('Welcome to EcoGo!')) {
            const fullText = 'Welcome to EcoGo!';
            landingHeading.textContent = '';
            landingHeading.style.borderRight = '3px solid #2d8d60';
            landingHeading.style.paddingRight = '8px';
            landingHeading.style.display = 'inline-block';
            
            let index = 0;
            const typingSpeed = 80;
            
            function typeCharacter() {
                if (index < fullText.length) {
                    landingHeading.textContent += fullText.charAt(index);
                    index++;
                    setTimeout(typeCharacter, typingSpeed);
                } else {
                    setTimeout(() => {
                        landingHeading.style.borderRight = 'none';
                    }, 500);
                }
            }
            
            setTimeout(typeCharacter, 500);
        }
    }
    
    // ====== SCROLL ANIMATIONS (landing sections) ======
    function initScrollAnimations() {
        const sections = document.querySelectorAll('.info-section, .community-section, .faq, #contact-us');
        
        if (sections.length === 0) {
            return;
        }
        
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                } else {
                    entry.target.classList.remove('animate-in');
                }
            });
        }, observerOptions);
        
        sections.forEach(section => {
            section.classList.add('scroll-animate');
            observer.observe(section);
        });
    }
    
    // Initialize typing and scroll animations
    initTypingEffect();
    initScrollAnimations();
    
    console.log('typingEffect.js loaded successfully');
});
