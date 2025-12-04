import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

import "./collapse.js";
import "./bannerv2.js";

import { banner } from "./banner.js";

Alpine.prefix("ccb-");

Alpine.plugin(collapse);

Alpine.data("banner", banner);

Alpine.start();
