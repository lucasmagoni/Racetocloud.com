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

        let headerHTML = '';
        if (obj.logo_url) {
            headerHTML += `<img src="${obj.logo_url}" alt="Logo" class="swiss-consent-logo">`;
        }
        if (obj.banner_headline) {
            headerHTML += `<h2 class="swiss-consent-title">${obj.banner_headline}</h2>`;
        }

        let linksHTML = '';
        if (obj.privacy_policy_url) {
            linksHTML += `<a href="${obj.privacy_policy_url}" target="_blank" class="swiss-consent-link">${obj.texts.privacy_policy}</a>`;
        }
        if (obj.impressum_url) {
            linksHTML += `<a href="${obj.impressum_url}" target="_blank" class="swiss-consent-link">${obj.texts.impressum}</a>`;
        }

        banner.innerHTML = `
            <div class="swiss-consent-glass">
                <div class="swiss-consent-content-wrapper">
                    ${headerHTML ? `<div class="swiss-consent-header">${headerHTML}</div>` : ''}
                    <div class="swiss-consent-body">
                        <p>${obj.texts.banner_text}</p>
                    </div>
                    <div class="swiss-consent-footer">
                        <div class="swiss-consent-links">
                            ${linksHTML}
                        </div>
                        <div class="swiss-consent-actions">
                            <button id="swiss-consent-settings" class="swiss-consent-btn swiss-consent-btn-secondary">${obj.texts.settings}</button>
                            <button id="swiss-consent-reject" class="swiss-consent-btn swiss-consent-btn-secondary">${obj.texts.reject_all}</button>
                            <button id="swiss-consent-accept" class="swiss-consent-btn swiss-consent-btn-primary">${obj.texts.accept_all}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(banner);

        // Show banner
        setTimeout(() => {
            banner.classList.add('visible');
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
            <div class="swiss-consent-modal-content swiss-consent-glass">
                <div class="swiss-consent-modal-header">
                    <h3>${obj.texts.settings}</h3>
                    <button id="swiss-consent-close" class="swiss-consent-close-btn">&times;</button>
                </div>
                <div class="swiss-consent-options">
                    <div class="swiss-consent-option">
                        <div class="swiss-consent-option-label">
                            <label for="swiss-consent-essential">${obj.texts.essential}</label>
                            <span class="swiss-consent-option-desc">Required for the website to function.</span>
                        </div>
                        <input type="checkbox" id="swiss-consent-essential" checked disabled>
                    </div>
                    <div class="swiss-consent-option">
                        <div class="swiss-consent-option-label">
                            <label for="swiss-consent-marketing">${obj.texts.marketing}</label>
                            <span class="swiss-consent-option-desc">Used for targeted advertising.</span>
                        </div>
                        <input type="checkbox" id="swiss-consent-marketing">
                    </div>
                    <div class="swiss-consent-option">
                        <div class="swiss-consent-option-label">
                            <label for="swiss-consent-statistics">${obj.texts.statistics}</label>
                            <span class="swiss-consent-option-desc">Used for analytics and performance.</span>
                        </div>
                        <input type="checkbox" id="swiss-consent-statistics">
                    </div>
                </div>
                <div class="swiss-consent-modal-actions">
                    <button id="swiss-consent-save" class="swiss-consent-btn swiss-consent-btn-primary">${obj.texts.save}</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('swiss-consent-save').addEventListener('click', saveSettings);
        document.getElementById('swiss-consent-close').addEventListener('click', closeSettings);

        // Close on click outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeSettings();
            }
        });
    }

    function openSettings() {
        renderModal();

        // Update checkboxes based on current consent
        const consent = getCookie(cookieName);
        const marketingBox = document.getElementById('swiss-consent-marketing');
        const statisticsBox = document.getElementById('swiss-consent-statistics');

        if (consent) {
            const consents = consent.split(',');
            if (marketingBox) marketingBox.checked = consents.includes('marketing');
            if (statisticsBox) statisticsBox.checked = consents.includes('statistics');
        } else {
            // Default to unchecked if no consent set yet
            if (marketingBox) marketingBox.checked = false;
            if (statisticsBox) statisticsBox.checked = false;
        }

        setTimeout(() => {
             document.getElementById('swiss-consent-modal').classList.add('active');
        }, 10);
    }

    function closeSettings() {
        const modal = document.getElementById('swiss-consent-modal');
        if(modal) {
            modal.classList.remove('active');
            // Optional: Remove from DOM after transition
            // setTimeout(() => modal.remove(), 300);
        }
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
        // Expose openSettings globaly so it can be called from a footer link for example
        window.swissConsentSettings = openSettings;

        if (!hasConsent()) {
            renderBanner();
        }
    });

})();
