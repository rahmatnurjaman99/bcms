import type { PageLoad } from "./$types";
import { fetchUsersList } from "$lib/api/admin";
import { hasPermission } from "$lib/utils/permissions";

export const load: PageLoad = async ({ fetch, parent, url }) => {
	const { viewer } = await parent();
	const allowed = hasPermission(viewer, "users.view");

	const search = url.searchParams.get("search") ?? "";
	const page = Number(url.searchParams.get("page") ?? "1");

	if (!allowed) {
		return {
			authorized: false,
			users: null,
			search,
			page,
		};
	}

	const users = await fetchUsersList({
		page: Number.isNaN(page) ? 1 : page,
		first: 10,
		search: search || undefined,
		fetchImpl: fetch,
	});

	return {
		authorized: true,
		users,
		search,
		page,
	};
};
