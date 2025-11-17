import type { PageLoad } from "./$types";
import { fetchPermissionsList, fetchRolesList } from "$lib/api/admin";
import { hasPermission } from "$lib/utils/permissions";

export const load: PageLoad = async ({ fetch, parent }) => {
	const { viewer } = await parent();
	const authorized = hasPermission(viewer, "roles.manage");

	if (!authorized) {
		return {
			authorized: false,
			roles: [],
		permissions: [],
	};
	}

	const [roles, permissions] = await Promise.all([
		fetchRolesList({ fetchImpl: fetch }),
		fetchPermissionsList({ fetchImpl: fetch }),
	]);

	return { authorized: true, roles, permissions };
};
