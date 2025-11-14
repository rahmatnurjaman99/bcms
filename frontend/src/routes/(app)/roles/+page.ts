import type { PageLoad } from "./$types";
import { fetchRolesList } from "$lib/api/admin";
import { hasPermission } from "$lib/utils/permissions";

export const load: PageLoad = async ({ fetch, parent }) => {
	const { viewer } = await parent();
	const authorized = hasPermission(viewer, "roles.manage");

	if (!authorized) {
		return {
			authorized: false,
			roles: [],
		};
	}

	const roles = await fetchRolesList({ fetchImpl: fetch });

	return { authorized: true, roles };
};
