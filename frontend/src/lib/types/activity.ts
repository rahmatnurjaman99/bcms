export interface ActivityLog {
	id: string;
	log_name?: string | null;
	description: string;
	event?: string | null;
	subject_type?: string | null;
	subject_id?: string | null;
	causer_type?: string | null;
	causer_id?: string | null;
	properties?: Record<string, unknown> | null;
	batch_uuid?: string | null;
	created_at: string;
}

export interface ActivityPaginator {
	data: ActivityLog[];
	paginatorInfo: {
		total: number;
		perPage: number;
		currentPage: number;
		lastPage: number;
	};
}
