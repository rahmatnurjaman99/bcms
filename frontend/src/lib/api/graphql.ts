import { browser } from "$app/environment";
import { env } from "$env/dynamic/public";

const GRAPHQL_URL = env.PUBLIC_GRAPHQL_URL || "/graphql";
const CLIENT_ID = env.PUBLIC_CLIENT_ID;
const CLIENT_KEY = env.PUBLIC_CLIENT_KEY;
const TOKEN_STORAGE_KEY = "bcms.auth.token";

interface GraphQLRequestOptions {
	query: string;
	variables?: Record<string, unknown>;
	fetchImpl?: typeof fetch;
	headers?: HeadersInit;
}

interface GraphQLError {
	message?: string;
}

interface GraphQLResponse<T> {
	data?: T;
	errors?: GraphQLError[];
}

export async function graphqlRequest<T>({
	query,
	variables,
	fetchImpl,
	headers = {}
}: GraphQLRequestOptions): Promise<T> {
	const executor = fetchImpl ?? fetch;

	const response = await executor(GRAPHQL_URL, {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
			...(CLIENT_ID ? { "x-client-id": CLIENT_ID } : {}),
			...(CLIENT_KEY ? { "x-client-key": CLIENT_KEY } : {}),
			...getAuthorizationHeader(),
			...headers
		},
		body: JSON.stringify({
			query,
			variables
		})
	});

	if (!response.ok) {
		throw new Error("Unable to reach the API. Please try again.");
	}

	const payload = (await response.json()) as GraphQLResponse<T>;

	if (payload.errors && payload.errors.length > 0) {
		throw new Error(payload.errors[0]?.message ?? "Unexpected error from API.");
	}

	if (!payload.data) {
		throw new Error("No data returned by API.");
	}

	return payload.data;
}

function getAuthorizationHeader(): HeadersInit {
	if (!browser) {
		return {};
	}

	const token = localStorage.getItem(TOKEN_STORAGE_KEY);

	if (!token) {
		return {};
	}

	return { Authorization: `Bearer ${token}` };
}
