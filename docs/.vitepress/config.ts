import { defineConfig } from "vitepress";

export default defineConfig({
	title: "Craft Cookie Banner",
	description: "An inituitive and GDPR compliant cookie banner for Craft CMS",
	themeConfig: {
		logo: "/icons/logo.svg",
		search: {
			provider: "local",
			options: {},
		},
		nav: [
			{ text: "Home", link: "/" },
			{ text: "Guide", link: "/plugin/installation-and-setup" },
			{ text: "Plugin store", link: "https://digitalastronaut.be/" },
			{ text: "Maintained by Digitalastronaut", link: "https://digitalastronaut.be/" },
		],

		sidebar: [
			{
				text: "Plugin",
				items: [
					{ text: "Installation and setup", link: "/plugin/installation-and-setup" },
					{ text: "Banner content", link: "/plugin/banner-content" },
					{ text: "Cookie & vendor management", link: "/plugin/cookie-and-vendor-management" },
					{ text: "Consent records", link: "/plugin/consent-records" },
					{ text: "Appearance and styling", link: "/plugin/appearance-and-styling" },
				],
			},
			{
				text: "Compliancy guide",
				items: [
					{ text: "Introduction", link: "/compliancy/introduction" },
					{ text: "Checklist", link: "/compliancy/checklist" },
				],
			},
		],

		socialLinks: [{ icon: "github", link: "https://github.com/digitalastronaut-be/craft-cookie-banner" }],
	},
	sitemap: {
		hostname: "https://cookie-banner.digitalastronaut.dev",
	},
});
