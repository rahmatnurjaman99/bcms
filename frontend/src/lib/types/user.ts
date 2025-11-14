export interface Role {
	id: string;
	name: string;
}

export interface Permission {
	id: string;
	name: string;
}

export interface CurrentUser {
	id: string;
	name: string;
	email: string;
	google_id?: string | null;
	avatar_url?: string | null;
	roles: Role[];
	permissions: Permission[];
}
