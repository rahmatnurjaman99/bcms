import { browser } from "$app/environment";
import { env } from "$env/dynamic/public";

const GOOGLE_SCRIPT_SRC = "https://accounts.google.com/gsi/client";
const GOOGLE_SCOPE = "openid email profile";
const CLIENT_ID = env.PUBLIC_GOOGLE_CLIENT_ID ?? "";

let scriptPromise: Promise<void> | null = null;

async function loadGoogleScript(): Promise<void> {
	if (!browser) {
		return;
	}

	if (scriptPromise) {
		return scriptPromise;
	}

	if (document.querySelector(`script[src="${GOOGLE_SCRIPT_SRC}"]`)) {
		scriptPromise = Promise.resolve();
		return scriptPromise;
	}

	scriptPromise = new Promise((resolve, reject) => {
		const script = document.createElement("script");
		script.src = GOOGLE_SCRIPT_SRC;
		script.async = true;
		script.defer = true;
		script.onload = () => resolve();
		script.onerror = () => reject(new Error("Failed to load Google Identity Services."));
		document.head.appendChild(script);
	});

	return scriptPromise;
}

export async function requestGoogleAccessToken(): Promise<string> {
	if (!browser) {
		throw new Error("Google sign-in only works in the browser.");
	}

	if (!CLIENT_ID) {
		throw new Error("Missing PUBLIC_GOOGLE_CLIENT_ID.");
	}

	await loadGoogleScript();

	const google = (window as typeof window & { google?: any }).google;

	if (!google?.accounts?.oauth2?.initTokenClient) {
		throw new Error("Google authentication is unavailable.");
	}

	return new Promise((resolve, reject) => {
		const client = google.accounts.oauth2.initTokenClient({
			client_id: CLIENT_ID,
			scope: GOOGLE_SCOPE,
			callback: (response: { access_token?: string; error?: string }) => {
				if (response.error) {
					reject(new Error(response.error));
					return;
				}

				if (!response.access_token) {
					reject(new Error("No Google access token returned."));
					return;
				}

				resolve(response.access_token);
			},
			error_callback: (error: { error: string }) => {
				reject(new Error(error.error));
			},
		});

		client.requestAccessToken();
	});
}
