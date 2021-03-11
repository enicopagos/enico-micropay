import axios from 'axios';

const settingsPage = (function () {
    return {
        init: function () {
            const migrateBtn = document.querySelectorAll('.migrateBtn');
            if (migrateBtn) {
                migrateBtn.forEach(_btn => {
                    _btn.addEventListener("click", function (e) {
                        e.preventDefault();
                        // Save button value for later
                        const _originalBtnText = _btn.value;
                        
                        // Change button text and disable to avoid doble click
                        _btn.value = 'Aguarde...';
                        _btn.disabled = true;
    
                        // Process checkout
                        const url = `${enico_vars.restURL}/migrate`;
                        axios.post(url, {
                                'action': _btn.dataset.action,
                            })
                            .then(res => {
                                _btn.value = _originalBtnText;
                                _btn.disabled = false;
                                
                                if (res.data !== '')
                                    alert(res.data);
                            })
                            .catch(err => {
                                alert('Error inesperado.');
                            });
                    });    
                })
            } 
        }
    }
})();

const metaBox = (function () {
    return {
        init: function () {
            /** Change post options display on init */
            metaBox.optionsDisplay();
            
            /** Change post options displat on click */
            eniActivePayment.addEventListener("click", function () {
                metaBox.optionsDisplay();
            });
        },
        optionsDisplay: function () {
            let styleDisplay = (eniActivePayment.checked) ? 'block' : 'none';
            eniPostOptions.style.display = styleDisplay;
        }
    }
})();

/**
 * Load functions when DOM ready
 */
document.addEventListener("DOMContentLoaded", () => {
    if (enico_vars.currentScreen && enico_vars.currentScreen.id === 'settings_page_enico-micropay') {
        settingsPage.init();
    }
    /** If POST is the curren screen */
    if (enico_vars.currentScreen && enico_vars.currentScreen.id === 'post') {
        metaBox.init();
    }
    
}, false);