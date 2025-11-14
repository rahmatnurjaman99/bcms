import { browser } from "$app/environment";
import { graphqlRequest } from "./graphql";

export interface AuthPayload {
	token: string;
	user: {
		id: string;
		name: string;
		email: string;
		google_id?: string | null;
		avatar_url?: string | null;
	};
}

const LOGIN_MUTATION = `
	mutation Login($email: String!, $password: String!, $deviceName: String) {
		login(email: $email, password: $password, deviceName: $deviceName) {
			token
			user {
				id
				name
				email
				google_id
				avatar_url
			}
		}
	}
`;

const REGISTER_MUTATION = `
	mutation Register($input: RegisterInput!) {
		register(input: $input) {
			token
			user {
				id
				name
				email
				google_id
				avatar_url
			}
		}
	}
`;

const SOCIAL_LOGIN_MUTATION = `
	mutation SocialLogin($provider: SocialProvider!, $accessToken: String!, $deviceName: String) {
		socialLogin(provider: $provider, accessToken: $accessToken, deviceName: $deviceName) {
			token
			user {
				id
				name
				email
				google_id
				avatar_url
			}
		}
	}
`;

const LOGOUT_MUTATION = `
	mutation Logout {
		logout
	}
`;

const TOKEN_STORAGE_KEY = "bcms.auth.token";
const USER_STORAGE_KEY = "bcms.auth.user";

export async function login({
	email,
	password,
	deviceName = "frontend"
}: {
	email: string;
	password: string;
	deviceName?: string;
}): Promise<AuthPayload> {
	const data = await graphqlRequest<{ login: AuthPayload }>({
		query: LOGIN_MUTATION,
		variables: { email, password, deviceName }
	});

	return data.login;
}

export async function register(input: {
	name: string;
	email: string;
	password: string;
	passwordConfirmation: string;
	deviceName?: string;
}): Promise<AuthPayload> {
	const data = await graphqlRequest<{ register: AuthPayload }>({
		query: REGISTER_MUTATION,
		variables: {
			input: {
				name: input.name,
				email: input.email,
				password: input.password,
				passwordConfirmation: input.passwordConfirmation,
				deviceName: input.deviceName ?? "frontend"
			}
		}
	});

	return data.register;
}

export async function socialLogin({
	provider,
	accessToken,
	deviceName = "frontend-google",
}: {
	provider: "GOOGLE";
	accessToken: string;
	deviceName?: string;
}): Promise<AuthPayload> {
	const data = await graphqlRequest<{ socialLogin: AuthPayload }>({
		query: SOCIAL_LOGIN_MUTATION,
		variables: {
			provider,
			accessToken,
			deviceName,
		},
	});

	return data.socialLogin;
}

export function persistAuth(payload: AuthPayload): void {
	if (!browser) {
		return;
	}

	localStorage.setItem(TOKEN_STORAGE_KEY, payload.token);
	localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(payload.user));
}

export function clearPersistedAuth(): void {
	if (!browser) {
		return;
	}

	localStorage.removeItem(TOKEN_STORAGE_KEY);
	localStorage.removeItem(USER_STORAGE_KEY);
}

export async function logout(): Promise<void> {
	try {
		await graphqlRequest<{ logout: boolean }>({
			query: LOGOUT_MUTATION,
		});
	} finally {
		clearPersistedAuth();
	}
}

export function getStoredAuth(): AuthPayload | null {
	if (!browser) {
		return null;
	}

	const token = localStorage.getItem(TOKEN_STORAGE_KEY);
	const userRaw = localStorage.getItem(USER_STORAGE_KEY);

	if (!token || !userRaw) {
		return null;
	}

	try {
		const user = JSON.parse(userRaw) as AuthPayload["user"];
		return { token, user };
	} catch {
		return null;
	}
}
