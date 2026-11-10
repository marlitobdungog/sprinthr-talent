var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/index.ts
import { DurableObject } from "cloudflare:workers";
var PORT = 80;
var INACTIVITY_TIMEOUT_MS = 10 * 60 * 1e3;
var CONTAINER_ENV_KEYS = [
  "APP_NAME",
  "APP_KEY",
  "APP_URL",
  "APP_TIMEZONE",
  "DB_CONNECTION",
  "DB_HOST",
  "DB_PORT",
  "DB_DATABASE",
  "DB_USERNAME",
  "DB_PASSWORD",
  "DB_SSLMODE",
  "SESSION_DRIVER",
  "CACHE_STORE",
  "QUEUE_CONNECTION",
  "FILESYSTEM_DISK",
  "TALENT_API_KEY",
  "TALENT_LOCAL_AUTH_ENABLED",
  "TALENT_PRIVACY_POLICY_URL",
  "TALENT_PRIVACY_POLICY_VERSION",
  "TALENT_CONSENT_POLICY_VERSION",
  "TALENT_RESUME_DISK",
  "BUCKET_API_URL",
  "BUCKET_API_KEY",
  "BUCKET_NAME",
  "GOOGLE_CLIENT_ID",
  "GOOGLE_CLIENT_SECRET",
  "GOOGLE_REDIRECT_URI",
  "MAIL_MAILER",
  "LOG_LEVEL"
];
var TalentApp = class extends DurableObject {
  static {
    __name(this, "TalentApp");
  }
  starting;
  monitoring;
  async fetch(request) {
    this.starting ??= this.startAndWaitForPort().finally(() => {
      this.starting = void 0;
    });
    await this.starting;
    const url = new URL(request.url);
    url.protocol = "http:";
    url.host = "container";
    const forwarded = new Request(url, request);
    forwarded.headers.delete("host");
    return this.ctx.container.getTcpPort(PORT).fetch(forwarded);
  }
  async startAndWaitForPort() {
    const container = this.ctx.container;
    if (!container.running) {
      const env = {
        APP_ENV: "production",
        APP_DEBUG: "false",
        SESSION_DRIVER: "database",
        CACHE_STORE: "database",
        QUEUE_CONNECTION: "database"
      };
      for (const key of CONTAINER_ENV_KEYS) {
        const value = this.env[key];
        if (typeof value === "string" && value !== "") {
          env[key] = value;
        }
      }
      container.start({
        image: container.images.base,
        instance: this.env.CONTAINER_INSTANCE ?? "lite",
        enableInternet: true,
        env
      });
    }
    this.observeExit();
    await container.setInactivityTimeout(INACTIVITY_TIMEOUT_MS);
    const port = container.getTcpPort(PORT);
    for (let attempt = 0; attempt < 150; attempt++) {
      try {
        const response = await port.fetch("http://container/up", {
          signal: AbortSignal.timeout(1e3)
        });
        await response.body?.cancel();
        if (response.ok) return;
      } catch {
      }
      await scheduler.wait(200);
    }
    throw new Error(`Container did not become ready on port ${PORT}`);
  }
  observeExit() {
    if (this.monitoring) return;
    this.monitoring = this.ctx.container.monitor().then(() => console.log("Container exited")).catch((error) => console.error("Container failed:", error)).finally(() => {
      this.monitoring = void 0;
    });
    this.ctx.waitUntil(this.monitoring);
  }
};
var index_default = {
  async fetch(request, env) {
    return env.TALENT_APP.getByName("main").fetch(request);
  }
};
export {
  TalentApp,
  index_default as default
};
//# sourceMappingURL=index.js.map
