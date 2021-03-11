import axios from 'axios';

const enicoFrontend = (function () {
    return {
        init: function () {
            const enicoPayBtn = document.querySelector('#enicoPayBtn');
            if (enicoPayBtn) {
                // Save payment button value for later
                const payBtnText = enicoPayBtn.value;

                enicoPayBtn.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Change button text and disable to avoid doble click
                    enicoPayBtn.value = 'Aguarde...';
                    enicoPayBtn.disabled = true;

                    // Process checkout
                    const url = `${enico_vars.restURL}/checkout`;
                    axios.post(url, {
                        'postID': enicoPostID.value,
                        'postPrice': enicoPostPrice.value,
                        'userEmail': enicoUserEmail.value
                    })
                        .then(res => {
                            if (res.data !== '')
                                window.location.replace(res.data);
                        })
                        .catch(err => {
                            // Show error message
                            enicoFrontend.errorMessage(`${err.response.data.message}`);
                            
                            // Animate message
                            enicoFrontend.animate(enicoMessage, 'bounce');
                            
                            // On error enable button
                            enicoPayBtn.value = payBtnText;
                            enicoPayBtn.disabled = false;

                        });
                });    
            } 
        },
        errorMessage: function (msg) {
            // Add message
            enicoMessage.innerHTML = msg;
            
            // Add message classes
            enicoMessage.classList.remove('enico-hidden');
            enicoMessage.classList.add('enico-error');

        },
        animate: function (element, animation, prefix = 'animate__') {
            const animationName = `${prefix}${animation}`;
            element.classList.add(`${prefix}animated`, animationName);

            // When the animation ends, we clean the classes and resolve the Promise
            function handleAnimationEnd() {
                element.classList.remove(`${prefix}animated`, animationName);
            }

            element.addEventListener('animationend', handleAnimationEnd, {once: true});
        }
    }
})();

/**
 * Load functions when DOM ready
 */
document.addEventListener("DOMContentLoaded", () => {
    /** If POST is the curren screen */
    if (!enico_vars.currentScreen) {
        enicoFrontend.init();
    }
}, false);