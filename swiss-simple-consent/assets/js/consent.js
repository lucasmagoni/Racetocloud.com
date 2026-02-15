(function() {
    'use strict';

    const cookieName = 'privacy_consent_v1';
    const obj = window.swiss_consent_obj;

    // Cookie Helper Functions
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function hasConsent() {
        return getCookie(cookieName) !== null;
    }

    // UI Rendering
    function renderBanner() {
        if (hasConsent()) return;

        const banner = document.createElement('div');
        banner.id = 'swiss-consent-banner';
        banner.innerHTML = `
            <div class="swiss-consent-container">
                <p>${obj.texts.banner_text}</p>
                <div class="swiss-consent-actions">
                    <button id="swiss-consent-settings" class="swiss-consent-btn">${obj.texts.settings}</button>
                    <button id="swiss-consent-reject" class="swiss-consent-btn">${obj.texts.reject_all}</button>
                    <button id="swiss-consent-accept" class="swiss-consent-btn swiss-consent-btn-primary">${obj.texts.accept_all}</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);

        // Show banner
        setTimeout(() => {
            banner.style.display = 'block';
        }, 100);

        // Event Listeners
        document.getElementById('swiss-consent-accept').addEventListener('click', acceptAll);
        document.getElementById('swiss-consent-reject').addEventListener('click', rejectAll);
        document.getElementById('swiss-consent-settings').addEventListener('click', openSettings);
    }

    function renderModal() {
        if (document.getElementById('swiss-consent-modal')) return;

        const modal = document.createElement('div');
        modal.id = 'swiss-consent-modal';
        modal.innerHTML = `
            <div class="swiss-consent-modal-content">
                <h3>${obj.texts.settings}</h3>
                <div class="swiss-consent-option">
                    <input type="checkbox" checked disabled>
                    <label>${obj.texts.essential}</label>
                </div>
                <div class="swiss-consent-option">
                    <input type="checkbox" id="swiss-consent-marketing">
                    <label>${obj.texts.marketing}</label>
                </div>
                <div class="swiss-consent-option">
                    <input type="checkbox" id="swiss-consent-statistics">
                    <label>${obj.texts.statistics}</label>
                </div>
                <div class="swiss-consent-modal-actions">
                    <button id="swiss-consent-save" class="swiss-consent-btn swiss-consent-btn-primary">${obj.texts.save}</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('swiss-consent-save').addEventListener('click', saveSettings);

        // Close on click outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    function openSettings() {
        renderModal();
        document.getElementById('swiss-consent-modal').classList.add('active');
    }

    // Actions
    function acceptAll() {
        const consent = ['essential', 'marketing', 'statistics'];
        saveConsent(consent);
    }

    function rejectAll() {
        const consent = ['essential'];
        saveConsent(consent);
    }

    function saveSettings() {
        const consent = ['essential'];
        if (document.getElementById('swiss-consent-marketing').checked) {
            consent.push('marketing');
        }
        if (document.getElementById('swiss-consent-statistics').checked) {
            consent.push('statistics');
        }
        saveConsent(consent);
    }

    function saveConsent(consentArray) {
        const consentString = consentArray.join(',');
        setCookie(cookieName, consentString, 365); // 1 year

        // Log to DB via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', obj.ajax_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                // Reload regardless of success to apply scripts
                window.location.reload();
            }
        };
        xhr.send(`action=swiss_consent_log&nonce=${obj.nonce}&consent_level=${consentString}`);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        if (!hasConsent()) {
            renderBanner();
        }
    });

})();
