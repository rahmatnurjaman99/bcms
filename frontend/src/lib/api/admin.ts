import { graphqlRequest } from "./graphql";

export interface PaginatedResponse<T> {
	data: T[];
	paginatorInfo: {
		total: number;
		perPage: number;
		currentPage: number;
		lastPage: number;
	};
}

export interface UserListItem {
	id: string;
	name: string;
	email: string;
	google_id?: string | null;
	avatar_url?: string | null;
	status: boolean;
	roles: { id: string; name: string }[];
	permissions: { id: string; name: string }[];
	created_at: string;
}

export interface RoleListItem {
	id: string;
	name: string;
	guard_name: string;
	permissions: { id: string; name: string }[];
	updated_at: string;
}

export interface PermissionListItem {
	id: string;
	name: string;
	guard_name: string;
	updated_at: string;
}

const USERS_QUERY = `
	query Users($page: Int, $first: Int, $search: String) {
		users(page: $page, first: $first, search: $search) {
			data {
				id
				name
				email
				google_id
				avatar_url
				status
				created_at
				roles {
					id
					name
				}
				permissions {
					id
					name
				}
			}
			paginatorInfo {
				total
				perPage
				currentPage
				lastPage
			}
		}
	}
`;

const ROLES_QUERY = `
	query Roles {
		roles {
			id
			name
			guard_name
			updated_at
			permissions {
				id
				name
			}
		}
	}
`;

const PERMISSIONS_QUERY = `
	query Permissions {
		permissions {
			id
			name
			guard_name
			updated_at
		}
	}
`;

export async function fetchUsersList({
	page = 1,
	first = 10,
	search,
	fetchImpl,
}: {
	page?: number;
	first?: number;
	search?: string;
	fetchImpl?: typeof fetch;
}): Promise<PaginatedResponse<UserListItem>> {
	const data = await graphqlRequest<{ users: PaginatedResponse<UserListItem> }>({
		query: USERS_QUERY,
		variables: { page, first, search },
		fetchImpl,
	});

	return data.users;
}

export async function fetchRolesList({
	fetchImpl,
}: {
	fetchImpl?: typeof fetch;
} = {}): Promise<RoleListItem[]> {
	const data = await graphqlRequest<{ roles: RoleListItem[] }>({
		query: ROLES_QUERY,
		fetchImpl,
	});

	return data.roles;
}

export async function fetchPermissionsList({
	fetchImpl,
}: {
	fetchImpl?: typeof fetch;
} = {}): Promise<PermissionListItem[]> {
	const data = await graphqlRequest<{ permissions: PermissionListItem[] }>({
		query: PERMISSIONS_QUERY,
		fetchImpl,
	});

	return data.permissions;
}
