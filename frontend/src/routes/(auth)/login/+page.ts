import { redirect } from "@sveltejs/kit";
import type { PageLoad } from "./$types";
import { getStoredAuth } from "$lib/api/auth";

export const ssr = false;

export const load: PageLoad = ({ url }) => {
	const redirectTo = url.searchParams.get("redirectTo") ?? "/dashboard";
	const auth = getStoredAuth();

	if (auth?.token) {
		throw redirect(302, redirectTo);
	}

	return {
		redirectTo,
	};
};
