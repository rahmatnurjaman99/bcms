import type { PageLoad } from "./$types";
import { fetchPermissionsList, fetchRolesList, fetchUsersList } from "$lib/api/admin";
import { hasPermission } from "$lib/utils/permissions";

export const load: PageLoad = async ({ fetch, parent, url }) => {
	const { viewer } = await parent();
	const allowed = hasPermission(viewer, "users.view");
	const canManageUsers = hasPermission(viewer, "users.manage");
	const canManageRoles = hasPermission(viewer, "roles.manage");

	const search = url.searchParams.get("search") ?? "";
	const page = Number(url.searchParams.get("page") ?? "1");

	if (!allowed) {
		return {
			authorized: false,
			users: null,
			search,
			page,
			canManageUsers,
			canManageRoles,
			roles: [],
			permissions: [],
		};
	}

	const [users, roles, permissions] = await Promise.all([
		fetchUsersList({
			page: Number.isNaN(page) ? 1 : page,
			first: 10,
			search: search || undefined,
			fetchImpl: fetch,
		}),
		canManageRoles ? fetchRolesList({ fetchImpl: fetch }) : Promise.resolve([]),
		canManageRoles ? fetchPermissionsList({ fetchImpl: fetch }) : Promise.resolve([]),
	]);

	return {
		authorized: true,
		users,
		search,
		page,
		canManageUsers,
		canManageRoles,
		roles,
		permissions,
	};
};
