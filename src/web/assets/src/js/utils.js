export const googleConsentV2Map = {
	essentialCookies: ["functionality_storage", "security_storage"],
	functionalCookies: ["functionality_storage"],
	analyticalCookies: ["analytics_storage"],
	advertisementCookies: ["ad_storage", "ad_personalization", "ad_user_data"],
	personalizationCookies: ["personalization_storage"],
};

export const buildGoogleConsentV2Object = (consentCategories) => {
	const result = {};

	for (const [category, value] of Object.entries(consentCategories)) {
		const keys = googleConsentV2Map[category] || [];

		for (const key of keys) {
			if (category === "essentialCookies") {
				result[key] = "granted";
			} else {
				result[key] = value ? "granted" : "denied";
			}
		}
	}

	return result;
};

export function getCookie(key) {
	let cookies = document.cookie.split(";");
	for (let i = 0; i < cookies.length; i++) {
		let cookie = cookies[i].split("=");
		if (key == cookie[0].trim()) {
			return decodeURIComponent(cookie[1]);
		}
	}
	return null;
}
