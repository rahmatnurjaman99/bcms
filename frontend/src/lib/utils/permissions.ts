import type { CurrentUser } from "$lib/types/user";

export function hasPermission(user: CurrentUser | null | undefined, permission: string): boolean {
	if (!user?.permissions) {
		return false;
	}

	return user.permissions.some((entry) => entry.name === permission);
}
