class DefenitionStatusChips extends HTMLElement {
	constructor() {
		super();

		this.chipElements = [];
		this.toggleElement = null;
		this.expanded = false;
		this.visibleCount = 3;
	}

	connectedCallback() {
		this.chipElements = [...this.querySelectorAll(":scope > .status-chip:not(.chip-controls)")];
		this.toggleElement = this.querySelector(":scope > .chip-controls");

		if (!this.chipElements.length && !this.toggleElement) {
			throw new Error("Component must have chip elements as children and have a valid control element");
		}

		this.updateVisibility();
		this.registerEventListeners();
	}

	registerEventListeners() {
		this.toggleElement.addEventListener("click", () => {
			this.expanded = !this.expanded;
			this.updateVisibility();
		});
	}

	updateVisibility() {
		this.chipElements.forEach((chip, index) => {
			if (this.expanded || index < this.visibleCount) chip.style.display = "";
			else chip.style.display = "none";
		});

		if (this.toggleElement) {
			const hiddenElementsCount = this.chipElements.length - this.visibleCount;
			this.toggleElement.textContent = this.expanded ? `Show less` : `Show ${hiddenElementsCount} more`;
		}
	}
}

window.customElements.define("defenition-status-chips", DefenitionStatusChips);
