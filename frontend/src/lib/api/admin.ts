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

const ROLE_FIELDS = `
	id
	name
	guard_name
	updated_at
	permissions {
		id
		name
	}
`;

const USER_FIELDS = `
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
`;

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

const CREATE_ROLE_MUTATION = `
	mutation CreateRole($input: CreateRoleInput!) {
		createRole(input: $input) {
			${ROLE_FIELDS}
		}
	}
`;

const UPDATE_ROLE_MUTATION = `
	mutation UpdateRole($id: ID!, $input: UpdateRoleInput!) {
		updateRole(id: $id, input: $input) {
			${ROLE_FIELDS}
		}
	}
`;

const DELETE_ROLE_MUTATION = `
	mutation DeleteRole($id: ID!) {
		deleteRole(id: $id)
	}
`;

const CREATE_PERMISSION_MUTATION = `
	mutation CreatePermission($input: CreatePermissionInput!) {
		createPermission(input: $input) {
			id
			name
			guard_name
			updated_at
		}
	}
`;

const UPDATE_PERMISSION_MUTATION = `
	mutation UpdatePermission($id: ID!, $input: UpdatePermissionInput!) {
		updatePermission(id: $id, input: $input) {
			id
			name
			guard_name
			updated_at
		}
	}
`;

const DELETE_PERMISSION_MUTATION = `
	mutation DeletePermission($id: ID!) {
		deletePermission(id: $id)
	}
`;

const REGISTER_USER_MUTATION = `
	mutation RegisterUser($input: RegisterInput!) {
		register(input: $input) {
			user {
				${USER_FIELDS}
			}
		}
	}
`;

const UPDATE_USER_MUTATION = `
	mutation UpdateUser($id: ID!, $input: UpdateUserInput!) {
		updateUser(id: $id, input: $input) {
			${USER_FIELDS}
		}
	}
`;

const DELETE_USER_MUTATION = `
	mutation DeleteUser($id: ID!) {
		deleteUser(id: $id)
	}
`;

type FetchLike = { fetchImpl?: typeof fetch };

export async function createRole({
	fetchImpl,
	...input
}: { name: string; permissions?: string[]; guard?: string } & FetchLike): Promise<RoleListItem> {
	const data = await graphqlRequest<{ createRole: RoleListItem }>({
		query: CREATE_ROLE_MUTATION,
		variables: { input },
		fetchImpl,
	});

	return data.createRole;
}

export async function updateRole({
	fetchImpl,
	...params
}: { id: string; name?: string; permissions?: string[]; guard?: string } & FetchLike): Promise<RoleListItem> {
	const { id, ...input } = params;

	const data = await graphqlRequest<{ updateRole: RoleListItem }>({
		query: UPDATE_ROLE_MUTATION,
		variables: { id, input },
		fetchImpl,
	});

	return data.updateRole;
}

export async function deleteRole({ id, fetchImpl }: { id: string } & FetchLike): Promise<boolean> {
	const data = await graphqlRequest<{ deleteRole: boolean }>({
		query: DELETE_ROLE_MUTATION,
		variables: { id },
		fetchImpl,
	});

	return data.deleteRole;
}

export async function createPermission({
	fetchImpl,
	...input
}: { name: string; guard?: string } & FetchLike): Promise<PermissionListItem> {
	const data = await graphqlRequest<{ createPermission: PermissionListItem }>({
		query: CREATE_PERMISSION_MUTATION,
		variables: { input },
		fetchImpl,
	});

	return data.createPermission;
}

export async function updatePermission({
	fetchImpl,
	...params
}: { id: string; name?: string; guard?: string } & FetchLike): Promise<PermissionListItem> {
	const { id, ...input } = params;

	const data = await graphqlRequest<{ updatePermission: PermissionListItem }>({
		query: UPDATE_PERMISSION_MUTATION,
		variables: { id, input },
		fetchImpl,
	});

	return data.updatePermission;
}

export async function deletePermission({ id, fetchImpl }: { id: string } & FetchLike): Promise<boolean> {
	const data = await graphqlRequest<{ deletePermission: boolean }>({
		query: DELETE_PERMISSION_MUTATION,
		variables: { id },
		fetchImpl,
	});

	return data.deletePermission;
}

export interface CreateUserInput {
	name: string;
	email: string;
	password: string;
	passwordConfirmation: string;
	deviceName?: string;
	roles?: string[];
	status?: boolean;
}

export async function createUser(
	{ fetchImpl, ...input }: CreateUserInput & FetchLike
): Promise<UserListItem> {
	const { roles, status, ...registerInput } = input;

	const data = await graphqlRequest<{ register: { user: UserListItem } }>({
		query: REGISTER_USER_MUTATION,
		variables: {
			input: {
				name: registerInput.name,
				email: registerInput.email,
				password: registerInput.password,
				passwordConfirmation: registerInput.passwordConfirmation,
				deviceName: registerInput.deviceName ?? "admin-console",
			},
		},
		fetchImpl,
	});

	let user = data.register.user;

	const shouldUpdate =
		(Boolean(roles && roles.length > 0)) || (typeof status === "boolean" && status !== user.status);

	if (shouldUpdate) {
		user = await updateUser({
			id: user.id,
			roles,
			status,
			fetchImpl,
		});
	}

	return user;
}

export interface UpdateUserInput {
	id: string;
	name?: string;
	email?: string;
	status?: boolean;
	password?: string;
	roles?: string[];
}

export async function updateUser({
	fetchImpl,
	...params
}: UpdateUserInput & FetchLike): Promise<UserListItem> {
	const { id, ...input } = params;

	const data = await graphqlRequest<{ updateUser: UserListItem }>({
		query: UPDATE_USER_MUTATION,
		variables: { id, input },
		fetchImpl,
	});

	return data.updateUser;
}

export async function deleteUser({ id, fetchImpl }: { id: string } & FetchLike): Promise<boolean> {
	const data = await graphqlRequest<{ deleteUser: boolean }>({
		query: DELETE_USER_MUTATION,
		variables: { id },
		fetchImpl,
	});

	return data.deleteUser;
}
