import { buildGoogleConsentV2Object } from "./utils.js";
import { getCookie } from "./utils.js";

export function banner() {
	return {
		detailedMode: false,
		loading: false,
		visible: false,
		consent: {
			essentialCookies: true,
			functionalCookies: false,
			analyticalCookies: false,
			advertisementCookies: false,
			personalizationCookies: false,
		},

		init() {
			const existingCookieConsent = JSON.parse(getCookie("cookie_consent"));

			if (!existingCookieConsent) this.visible = true;

			if (existingCookieConsent) {
				this.consent = existingCookieConsent;

				const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
				gtag("consent", "update", googleConsentV2Object);
			}
		},

		acceptAll() {
			this.visible = false;
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
		},

		acceptSelected() {
			this.visible = false;
			document.cookie = `cookie_consent=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Lax`;

			const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
			gtag("consent", "update", googleConsentV2Object);

			this.logConsent("Accept selected");
		},

		refuseAll() {
			this.visible = false;
			this.consent = {
				essentialCookies: true,
				functionalCookies: false,
				analyticalCookies: false,
				advertisementCookies: false,
				personalizationCookies: false,
			};

			document.cookie = `cookie_consent=${JSON.stringify(this.consent)}; path=/; max-age=31536000; SameSite=Lax`;

			const googleConsentV2Object = buildGoogleConsentV2Object(this.consent);
			gtag("consent", "update", googleConsentV2Object);

			this.logConsent("Refuse all");
		},

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
		},
	};
}
