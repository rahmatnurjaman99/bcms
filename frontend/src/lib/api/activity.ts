import { graphqlRequest } from "./graphql";
import type { ActivityPaginator } from "$lib/types/activity";

const ACTIVITY_QUERY = `
	query Activities(
		$page: Int
		$first: Int
		$search: String
		$logName: String
		$event: String
		$createdFrom: Date
		$createdTo: Date
	) {
		activities(
			page: $page
			first: $first
			search: $search
			logName: $logName
			event: $event
			createdFrom: $createdFrom
			createdTo: $createdTo
		) {
			data {
				id
				log_name
				description
				event
				subject_type
				subject_id
				causer_type
				causer_id
				properties
				batch_uuid
				created_at
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

export async function fetchActivityLogs({
	page = 1,
	first = 20,
	search,
	logName,
	event,
	createdFrom,
	createdTo,
	fetchImpl,
}: {
	page?: number;
	first?: number;
	search?: string | null;
	logName?: string | null;
	event?: string | null;
	createdFrom?: string | null;
	createdTo?: string | null;
	fetchImpl?: typeof fetch;
}): Promise<ActivityPaginator> {
	const data = await graphqlRequest<{ activities: ActivityPaginator }>({
		query: ACTIVITY_QUERY,
		variables: {
			page,
			first,
			search,
			logName,
			event,
			createdFrom,
			createdTo,
		},
		fetchImpl,
	});

	return data.activities;
}
