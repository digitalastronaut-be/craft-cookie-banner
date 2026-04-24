import Chart from "chart.js/auto";

class CookiesAndVendorsGraph extends HTMLElement {
	constructor() {
		super();

		this.cookiesChartElement = null;
		this.vendorsChartElement = null;
	}

	async connectedCallback() {
		this.cookiesChartElement = this.querySelector(":scope #cookies-graph");
		this.vendorsChartElement = this.querySelector(":scope #vendors-graph");

		if (!this.cookiesChartElement || !this.vendorsChartElement) {
			throw new Error("Component must define a valid chart canvas elements");
		}

		const cookiesRawData = JSON.parse(this.dataset.cookies);
		const cookiesChartData = {
			labels: cookiesRawData.map((item) => item.vendors),
			datasets: [
				{
					label: "Cookies",
					data: cookiesRawData.map(() => 1),
					backgroundColor: cookiesRawData.map((item) => item.backgroundColor),
					hoverOffset: 4,
					borderRadius: 4,
					borderWidth: 0,
					spacing: 12,
				},
			],
		};

		const vendorsRawData = JSON.parse(this.dataset.vendors);
		const vendorsChartData = {
			labels: vendorsRawData.map((item) => item.label),
			datasets: [
				{
					label: "Vendors",
					data: vendorsRawData.map(() => 1),
					backgroundColor: vendorsRawData.map((item) => item.backgroundColor),
					hoverOffset: 4,
					borderRadius: 4,
					borderWidth: 0,
					spacing: 12,
				},
			],
		};

		const createChartOptions = (dataSource, titleText) => ({
			responsive: true,
			maintainAspectRatio: false,
			cutout: "65%",

			plugins: {
				legend: {
					display: false,
				},
				title: {
					display: false,
					text: titleText,
				},
				tooltip: {
					callbacks: {
						label: function (context) {
							const item = dataSource[context.dataIndex];
							return item.data;
						},
					},
				},
			},
		});

		new Chart(this.cookiesChartElement, {
			type: "doughnut",
			data: cookiesChartData,
			options: createChartOptions(cookiesRawData, "Cookie definitions"),
		});

		new Chart(this.vendorsChartElement, {
			type: "doughnut",
			data: vendorsChartData,
			options: createChartOptions(vendorsRawData, "Vendor definitions"),
		});
	}
}

window.customElements.define("cookies-and-vendors-graph", CookiesAndVendorsGraph);
