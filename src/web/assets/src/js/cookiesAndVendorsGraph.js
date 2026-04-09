import Chart from "chart.js/auto";

function createUnitDatasets(label, values, color) {
	const datasets = [];

	values.forEach((value, index) => {
		for (let i = 0; i < value; i++) {
			const data = [0, 0];
			data[index] = 1;

			datasets.push({
				label,
				data,
				backgroundColor: color,
				stack: index === 0 ? "cookies" : "vendors",
			});
		}
	});

	return datasets;
}

class CookiesAndVendorsGraph extends HTMLElement {
	constructor() {
		super();
		this.chartElement = null;
	}

	async connectedCallback() {
		this.chartElement = this.querySelector(":scope > #cookies-and-vendors-graph");

		if (!this.chartElement) {
			throw new Error("Component must define a valid chart canvas element");
		}

		// const response = await fetch("/admin/cookie-banner/cookies-and-vendors/get-chart-data");
		// const json = await response.json();

		new Chart(this.chartElement, {
			type: "bar",
			data: {
				labels: ["Cookies", "Vendors"],
				datasets: [
					...createUnitDatasets("Disabled", [3, 2], "#d8e2ee"),
					...createUnitDatasets("Defined", [11, 1], "#10b981"),
					...createUnitDatasets("Defined incomplete", [6, 0], "#facc15"),
					...createUnitDatasets("Detected automatically", [4, 1], "#4299E1"),
				],
			},

			options: {
				datasets: {
					bar: {
						barThickness: 64,
						borderWidth: 2,
						borderColor: "#f3f7fc",
					},
				},
				layout: {
					padding: 4,
				},
				plugins: {
					legend: {
						display: false,
					},
				},
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					x: {
						stacked: true,
						display: false,
					},
					y: {
						stacked: true,
						display: false,
					},
				},
			},
		});
	}
}

window.customElements.define("cookies-and-vendors-graph", CookiesAndVendorsGraph);
