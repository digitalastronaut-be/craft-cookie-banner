class CollapsibleContent extends HTMLElement {
	constructor() {
		super();

		this.toggleElement = null;
		this.contentElement = null;
	}

	connectedCallback() {
		this.toggleElement = this.querySelector(":scope > [data-collapse-toggle]");
		this.contentElement = this.querySelector(":scope > [data-collapse-content]");

		if (!this.toggleElement || !this.contentElement) {
			throw new Error("Component must define a valid toggleElement and contentElement");
		}

		this.registerEventListeners();
	}

	registerEventListeners() {
		this.toggleElement.addEventListener("click", () => {
			this.classList.toggle("open");
		});
	}
}

window.customElements.define("collapsible-content", CollapsibleContent);
