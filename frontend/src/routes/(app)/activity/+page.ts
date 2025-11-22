import type { PageLoad } from "./$types";
import { fetchActivityLogs } from "$lib/api/activity";
import { hasPermission } from "$lib/utils/permissions";

export const load: PageLoad = async ({ fetch, url, parent }) => {
	const { viewer } = await parent();
	const authorized = hasPermission(viewer, "activity.view");

	const pageParam = Number(url.searchParams.get("page") ?? "1");
	const page = Number.isFinite(pageParam) && pageParam > 0 ? pageParam : 1;
	const search = url.searchParams.get("search") || null;
	const logName = url.searchParams.get("log") || null;
	const event = url.searchParams.get("event") || null;
	const createdFrom = url.searchParams.get("createdFrom") || null;
	const createdTo = url.searchParams.get("createdTo") || null;

	if (!authorized) {
		return {
			authorized: false,
			activities: null,
			filters: { search, log: logName, event, createdFrom, createdTo },
			error: null,
		};
	}

	try {
		const activities = await fetchActivityLogs({
			page,
			first: 20,
			search,
			logName,
			event,
			createdFrom,
			createdTo,
			fetchImpl: fetch,
		});

		return {
			authorized: true,
			activities,
			filters: { search, log: logName, event, createdFrom, createdTo },
			error: null,
		};
	} catch (error) {
		return {
			authorized: true,
			activities: null,
			filters: { search, log: logName, event, createdFrom, createdTo },
			error:
				error instanceof Error
					? error.message
					: "Unable to load activity logs",
		};
	}
};
