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

		this.manageConsentButtonElements = [];
		this.consentCheckboxElements = [];

		this.consent = {
			essentialCookies: true,
			functionalCookies: false,
			analyticalCookies: false,
			advertisementCookies: false,
			personalizationCookies: false,
		};
	}

	connectedCallback() {
		this.registerElements();
		this.registerEventListeners();

		const existingCookieConsent = JSON.parse(getCookie("cookie_consent"));

		if (existingCookieConsent) this.consent = existingCookieConsent;
		if (!existingCookieConsent) this.showBanner();

		this.initializeConsentCheckboxes();
		this.initializeBanner();

		if (existingCookieConsent) {
			const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
			gtag("consent", "update", googleConsentV2Object);
		}
	}

	acceptAll() {
		this.hideBanner();

		this.consent = {
			essentialCookies: true,
			functionalCookies: true,
			analyticalCookies: true,
			advertisementCookies: true,
			personalizationCookies: true,
		};

		document.cookie = `cookie_consent=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Lax`;

		const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
		gtag("consent", "update", googleConsentV2Object);

		this.logConsent("Accept all");
	}

	acceptSelected() {
		this.hideBanner();

		document.cookie = `cookie_consent=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Lax`;

		const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
		gtag("consent", "update", googleConsentV2Object);

		this.logConsent("Accept selected");
	}

	async logConsent(consentAction) {
		const response = await fetch(`/cookie-banner/consent-records/create`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-Token": window.craftCookieBanner.csrfTokenValue,
			},
			body: JSON.stringify({
				language: navigator.language,
				consentCategories: this.consent,
				consentAction,
			}),
		});

		const data = await response.json();
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
				this.updateConsentCheckboxes(property);
			});
		});
	}

	updateConsentCheckboxes(consentPropertyToUpdate) {
		const selector = `input[type="checkbox"][data-model=${consentPropertyToUpdate}]`;
		const consentCheckboxesToUpdate = this.querySelectorAll(selector);

		consentCheckboxesToUpdate.forEach((checkbox) => {
			checkbox.checked = this.consent[consentPropertyToUpdate];
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

		this.manageConsentButtonElements = [...document.querySelectorAll("[data-manage-consent-button]")];
	}

	registerEventListeners() {
		this.refuseAllButton.addEventListener("click", () => this.hideBanner());
		this.determinePreferencesButton.addEventListener("click", () => this.showDetailedSettings());
		this.determinePreferencesLink.addEventListener("click", () => this.showDetailedSettings());
		this.acceptSelectedButton.addEventListener("click", () => this.acceptSelected());
		this.acceptAllButton.addEventListener("click", () => this.acceptAll());

		this.manageConsentButtonElements.forEach((button) => {
			button.addEventListener("click", () => {
				this.showDetailedSettings();
				this.showBanner();
			});
		});
	}
}

window.customElements.define("cookie-banner", CookieBanner);
