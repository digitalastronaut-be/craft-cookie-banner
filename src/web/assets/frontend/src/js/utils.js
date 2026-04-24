export const googleConsentV2Map = {
	necessaryCookies: ["security_storage"],
	preferenceCookies: ["functionality_storage"],
	analyticalCookies: ["analytics_storage"],
	marketingCookies: ["ad_storage", "ad_personalization", "ad_user_data"],
	uncategorizedCookies: ["personalization_storage"],
};

export const buildGoogleConsentV2Object = (consentCategories) => {
	const result = {};

	for (const [category, value] of Object.entries(consentCategories)) {
		const keys = googleConsentV2Map[category] || [];

		for (const key of keys) {
			if (category === "necessaryCookies") {
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
