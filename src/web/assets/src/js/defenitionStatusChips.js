class DefenitionStatusChips extends HTMLElement {
	constructor() {
		super();

		this.chipElements = null;
	}

	// Implement a toggle for many chips so only max 5 show initially and when toggled the rest show just like craft cms chips
	connectedCallback() {
		this.chipElements = [...this.querySelectorAll(":scope > .status-chip")];

		if (!this.chipElements.length) {
			throw new Error("Component must define a valid toggleElement and contentElement");
		}

		console.log(this.chipElements);
		this.registerEventListeners();
	}

	registerEventListeners() {}
}

window.customElements.define("defenition-status-chips", DefenitionStatusChips);
