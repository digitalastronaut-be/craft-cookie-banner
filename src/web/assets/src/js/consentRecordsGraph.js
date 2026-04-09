import Chart from "chart.js/auto";

class ConsentRecordsGraph extends HTMLElement {
	constructor() {
		super();
		this.chartElement = null;
	}

	async connectedCallback() {
		this.chartElement = this.querySelector(":scope > #consent-records-graph");

		if (!this.chartElement) {
			throw new Error("Component must define a valid chart canvas element");
		}

		const response = await fetch("/admin/cookie-banner/consent-records/get-chart-data");
		const json = await response.json();

		const labels = json.data.map((row) => row.date);
		const values = json.data.map((row) => row.count);

		new Chart(this.chartElement, {
			type: "line",
			data: {
				labels: labels,
				datasets: [
					{
						label: "Consent records",
						data: values,
						borderWidth: 3,
						pointRadius: 3,
						pointHoverRadius: 6,
						tension: 0,
						fill: "start",
						borderColor: "#4299E1",
						backgroundColor: "rgba(66,153,225, 0.1)",
						pointBackgroundColor: "#4299E1",
					},
				],
			},
			options: {
				interaction: {
					mode: "nearest",
					intersect: false,
				},
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false,
					},
					tooltip: {},
				},
				scales: {
					x: {
						display: true,
						ticks: {
							display: false,
						},
						grid: {
							display: false,
							tickLength: 0,
							drawBorder: false,
						},
					},
					y: {
						beginAtZero: true,
						display: true,
						suggestedMax: Math.max(...values) + 2,
						ticks: {
							maxTicksLimit: 8,
							callback: function (value) {
								return value >= 0 ? value : "";
							},
						},
					},
				},
			},
		});
	}
}

window.customElements.define("consent-records-graph", ConsentRecordsGraph);
