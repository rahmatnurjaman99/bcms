import type { PageLoad } from "./$types";
import { fetchPermissionsList, fetchRolesList, fetchUsersList } from "$lib/api/admin";
import { hasPermission } from "$lib/utils/permissions";

export const load: PageLoad = async ({ fetch, parent }) => {
	const { viewer } = await parent();

	const canViewUsers = hasPermission(viewer, "users.view");
	const canManageRoles = hasPermission(viewer, "roles.manage");

	let userSummary = null;
	if (canViewUsers) {
		const users = await fetchUsersList({ first: 5, fetchImpl: fetch });
		userSummary = {
			total: users.paginatorInfo.total,
			recent: users.data,
		};
	}

	let roles = null;
	let permissions = null;

	if (canManageRoles) {
		roles = await fetchRolesList({ fetchImpl: fetch });
		permissions = await fetchPermissionsList({ fetchImpl: fetch });
	}

	return {
		canViewUsers,
		canManageRoles,
		userSummary,
		roles,
		permissions,
	};
};
