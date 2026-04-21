import { buildGoogleConsentV2Object } from "./utils.js";
import { getCookie } from "./utils.js";

class CookieBanner extends HTMLElement {
	constructor() {
		super();

		this.detailedSettingsElement = null;
		this.categoriesListElement = null;

		this.refuseAllButton = null;
		this.determinePreferencesButton = null;
		this.determinePreferencesLink = null;
		this.acceptSelectedButton = null;
		this.acceptAllButton = null;

		this.manageConsentElements = [];
		this.consentCheckboxElements = [];

		this.consent = {
			necessaryCookies: true,
			preferenceCookies: false,
			analyticalCookies: false,
			marketingCookies: false,
			uncategorizedCookies: false,
		};
	}

	connectedCallback() {
		if (!window.dataLayer && typeof gtag !== "undefined") {
			console.warn(
				"%cCraft CMS plugin warning - Cookie banner\n%cThe dataLayer is not initialized.\nPlease load Google tag manager script first.",
				"font-weight: bold;",
				"font-weight: normal;",
			);

			return;
		}

		this.registerElements();
		this.registerEventListeners();

		const existingCookieConsent = JSON.parse(getCookie("craft-cookie-banner"));

		if (existingCookieConsent) this.consent = existingCookieConsent;
		if (!existingCookieConsent) this.showBanner();

		this.initializeConsentCheckboxes();
		this.initializeBanner();

		if (existingCookieConsent) {
			const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
			gtag("consent", "update", googleConsentV2Object);
			gtag("event", "cookie-banner", this.consent);
		}

		document.dispatchEvent(
			new CustomEvent("cookie-banner:consent-onload", {
				bubbles: true,
				composed: true,
				detail: { consent: this.consent },
			}),
		);
	}

	acceptAll() {
		this.consent = {
			necessaryCookies: true,
			preferenceCookies: true,
			analyticalCookies: true,
			marketingCookies: true,
			uncategorizedCookies: true,
		};

		this.hideBanner();
		this.updateConsentCheckboxes();

		document.cookie = `craft-cookie-banner=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Secure`;

		const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
		gtag("consent", "update", googleConsentV2Object);
		gtag("event", "cookie-banner", this.consent);

		this.dispatchConsentUpdate();
		this.logConsent("Accept all");
	}

	acceptSelected() {
		this.hideBanner();

		document.cookie = `craft-cookie-banner=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Secure`;

		const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
		gtag("consent", "update", googleConsentV2Object);
		gtag("event", "cookie-banner", this.consent);

		this.dispatchConsentUpdate();
		this.logConsent("Accept selected");
	}

	refuseAll() {
		this.consent = {
			necessaryCookies: true,
			preferenceCookies: false,
			analyticalCookies: false,
			marketingCookies: false,
			uncategorizedCookies: false,
		};

		this.hideBanner();
		this.updateConsentCheckboxes();

		document.cookie = `craft-cookie-banner=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Secure`;

		const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
		gtag("consent", "update", googleConsentV2Object);
		gtag("event", "cookie-banner", this.consent);

		this.dispatchConsentUpdate();
		this.logConsent("Refuse all");
	}

	async logConsent(consentAction) {
		let session = await fetch("/actions/users/session-info", {
			headers: {
				Accept: "application/json",
			},
		});

		if (!session.ok) throw new Error("Failed to fetch session info");

		session = await session.json();

		const response = await fetch(`actions/cookie-banner/consent-records/create`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-Token": session.csrfTokenValue,
			},
			body: JSON.stringify({
				language: navigator.language,
				consentCategories: this.consent,
				consentAction,
			}),
		});

		if (!response.ok) throw new Error("Failed to log consent");
	}

	dispatchConsentUpdate() {
		document.dispatchEvent(
			new CustomEvent("cookie-banner:consent-update", {
				bubbles: true,
				composed: true,
				detail: { consent: this.consent },
			}),
		);
	}

	showBanner() {
		this.classList.add("visible");
	}

	hideBanner() {
		this.classList.remove("visible");
	}

	hideDetailedSettings() {
		if (this.dataset.showCookieCategories) this.cookieCategoriesElement.classList.add("visible");
		if (this.dataset.preferencesAction === "button") this.determinePreferencesButton.classList.add("visible");
		if (this.dataset.preferencesAction === "link") this.determinePreferencesLink.classList.add("visible");

		this.detailedSettingsElement.classList.remove("visible");
		this.acceptSelectedButton.classList.remove("visible");
	}

	showDetailedSettings() {
		this.cookieCategoriesElement.classList.remove("visible");
		this.determinePreferencesButton.classList.remove("visible");
		this.determinePreferencesLink.classList.remove("visible");

		this.detailedSettingsElement.classList.add("visible");
		this.acceptSelectedButton.classList.add("visible");
	}

	initializeBanner() {
		if (this.dataset.showCookieCategories) this.cookieCategoriesElement.classList.add("visible");
		if (this.dataset.preferencesAction === "button") this.determinePreferencesButton.classList.add("visible");
		if (this.dataset.preferencesAction === "link") this.determinePreferencesLink.classList.add("visible");
	}

	initializeConsentCheckboxes() {
		this.checkboxes = this.querySelectorAll('input[type="checkbox"]');

		this.checkboxes.forEach((checkbox) => {
			const property = checkbox.dataset.model;
			checkbox.checked = this.consent[property];

			checkbox.addEventListener("click", (event) => {
				event.stopPropagation();
			});

			checkbox.addEventListener("change", (event) => {
				event.stopPropagation();
				this.consent[property] = event.target.checked;
				this.updateConsentCheckboxes();
			});
		});
	}

	updateConsentCheckboxes() {
		const consentCheckboxesToUpdate = this.querySelectorAll('input[type="checkbox"]');

		consentCheckboxesToUpdate.forEach((checkbox) => {
			const property = checkbox.dataset.model;
			checkbox.checked = this.consent[property];
		});
	}

	registerElements() {
		this.cookieCategoriesElement = this.querySelector("[data-cookie-categories]");
		this.detailedSettingsElement = this.querySelector("[data-detailed-settings]");

		this.refuseAllButton = this.querySelector("[data-refuse-all-button]");
		this.determinePreferencesButton = this.querySelector("[data-determine-preferences-button]");
		this.determinePreferencesLink = this.querySelector("[data-determine-preferences-link]");
		this.acceptSelectedButton = this.querySelector("[data-accept-selected-button]");
		this.acceptAllButton = this.querySelector("[data-accept-all-button]");

		this.manageConsentElements = [...document.querySelectorAll("[data-manage-consent]")];
	}

	registerEventListeners() {
		this.refuseAllButton.addEventListener("click", () => this.refuseAll());
		this.determinePreferencesButton.addEventListener("click", () => this.showDetailedSettings());
		this.determinePreferencesLink.addEventListener("click", () => this.showDetailedSettings());
		this.acceptSelectedButton.addEventListener("click", () => this.acceptSelected());
		this.acceptAllButton.addEventListener("click", () => this.acceptAll());

		this.manageConsentElements.forEach((element) => {
			element.addEventListener("click", (event) => {
				event.preventDefault();
				event.stopPropagation();

				this.showDetailedSettings();
				this.showBanner();
			});
		});
	}
}

window.customElements.define("cookie-banner", CookieBanner);
