import { DurableObject } from "cloudflare:workers";

const PORT = 80;
const INACTIVITY_TIMEOUT_MS = 10 * 60 * 1000;

// Secrets and settings forwarded from the Worker to the Laravel container.
const CONTAINER_ENV_KEYS = [
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
	"LOG_LEVEL",
] as const;

export class TalentApp extends DurableObject<Env> {
	private starting: Promise<void> | undefined;
	private monitoring: Promise<void> | undefined;

	async fetch(request: Request): Promise<Response> {
		this.starting ??= this.startAndWaitForPort().finally(() => {
			this.starting = undefined;
		});
		await this.starting;

		const url = new URL(request.url);
		url.protocol = "http:";
		url.host = "container";
		const forwarded = new Request(url, request);
		forwarded.headers.delete("host");
		return this.ctx.container!.getTcpPort(PORT).fetch(forwarded);
	}

	private async startAndWaitForPort(): Promise<void> {
		const container = this.ctx.container!;

		if (!container.running) {
			const env: Record<string, string> = {
				APP_ENV: "production",
				APP_DEBUG: "false",
				SESSION_DRIVER: "database",
				CACHE_STORE: "database",
				QUEUE_CONNECTION: "database",
			};
			for (const key of CONTAINER_ENV_KEYS) {
				const value = this.env[key];
				if (typeof value === "string" && value !== "") {
					env[key] = value;
				}
			}

			container.start({
				image: container.images.base,
				instance: (this.env.CONTAINER_INSTANCE as "lite") ?? "lite",
				enableInternet: true,
				env,
			});
		}

		this.observeExit();
		await container.setInactivityTimeout(INACTIVITY_TIMEOUT_MS);

		const port = container.getTcpPort(PORT);
		for (let attempt = 0; attempt < 150; attempt++) {
			try {
				const response = await port.fetch("http://container/up", {
					signal: AbortSignal.timeout(1000),
				});
				await response.body?.cancel();
				if (response.ok) return;
			} catch {
				// Not listening yet; keep probing.
			}
			await scheduler.wait(200);
		}
		throw new Error(`Container did not become ready on port ${PORT}`);
	}

	private observeExit(): void {
		if (this.monitoring) return;
		this.monitoring = this.ctx
			.container!.monitor()
			.then(() => console.log("Container exited"))
			.catch((error: unknown) => console.error("Container failed:", error))
			.finally(() => {
				this.monitoring = undefined;
			});
		this.ctx.waitUntil(this.monitoring);
	}
}

export default {
	async fetch(request: Request, env: Env): Promise<Response> {
		return env.TALENT_APP.getByName("main").fetch(request);
	},
} satisfies ExportedHandler<Env>;
