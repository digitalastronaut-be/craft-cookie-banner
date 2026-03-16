class DefenitionStatusChips extends HTMLElement {
	constructor() {
		super();

		this.chipElements = null;
	}

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
