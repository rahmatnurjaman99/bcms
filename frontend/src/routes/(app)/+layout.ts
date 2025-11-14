import { redirect } from "@sveltejs/kit";
import type { LayoutLoad } from "./$types";
import { getStoredAuth, clearPersistedAuth } from "$lib/api/auth";
import { graphqlRequest } from "$lib/api/graphql";
import type { CurrentUser } from "$lib/types/user";

const VIEWER_QUERY = `
	query Viewer {
		me {
			id
			name
			email
			google_id
			avatar_url
			roles {
				id
				name
			}
			permissions {
				id
				name
			}
		}
	}
`;

export const ssr = false;

export const load: LayoutLoad = async ({ fetch, url }) => {
	const auth = getStoredAuth();

	if (!auth) {
		throw redirect(302, `/login?redirectTo=${encodeURIComponent(url.pathname)}`);
	}

	try {
		const data = await graphqlRequest<{ me: CurrentUser }>({
			query: VIEWER_QUERY,
			fetchImpl: fetch,
		});

		return {
			viewer: data.me,
		};
	} catch (error) {
		console.error("Failed to fetch viewer", error);
		clearPersistedAuth();
		throw redirect(302, "/login");
	}
};
