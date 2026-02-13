class DetectedCookiesTable extends HTMLElement {
	constructor() {
		super();

		this.tableRows = [];
	}

	connectedCallback() {
		// console.log("detected cookies table init");
		// this.initTableRows();
	}

	// initTableRows() {
	// 	this.tableRows = [...this.querySelectorAll("[data-table-row]")];

	// 	this.tableRows.forEach((row) => {
	// 		const addButton = row.querySelector("[data-add-cookie-button]");

	// 		addButton.addEventListener("click", async () => {
	// 			await this.addCookie(row.dataset.cookieName);
	// 		});
	// 	});
	// }

	// async addCookie(cookieName) {
	// 	const response = await fetch(`cookie-banner/content/add-cookie?cookieName=${cookieName}`);
	// 	const data = await response.json();

	// 	console.log(data);
	// }
}

window.customElements.define("detected-cookies-table", DetectedCookiesTable);
