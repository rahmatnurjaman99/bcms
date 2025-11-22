import type { PageLoad } from "./$types";
import { getAcademicYears } from "$lib/api/academic-years";

const SORTABLE_COLUMNS = [
	"name",
	"start_date",
	"end_date",
	"duration_in_days",
	"is_active",
	"created_at",
] as const;

const DEFAULT_SORT_FIELD = "start_date";
const DEFAULT_SORT_DIRECTION = "DESC";

export const load: PageLoad = async ({ fetch, url }) => {
	const pageParam = Number(url.searchParams.get("page") ?? "1");
	const page = Number.isFinite(pageParam) && pageParam > 0 ? pageParam : 1;
	const sortParam = url.searchParams.get("sort") ?? DEFAULT_SORT_FIELD;
	const sortField = SORTABLE_COLUMNS.includes(sortParam as (typeof SORTABLE_COLUMNS)[number])
		? sortParam
		: DEFAULT_SORT_FIELD;
	const directionParam = url.searchParams.get("direction")?.toUpperCase() === "ASC" ? "ASC" : "DESC";

	try {
		const academicYears = await getAcademicYears({
			fetchImpl: fetch,
			page,
			orderBy: [{ column: sortField, order: directionParam ?? DEFAULT_SORT_DIRECTION }],
		});

		return {
			academicYears: academicYears.data,
			paginator: academicYears.paginatorInfo,
			error: null,
			page,
			sortField,
			sortDirection: directionParam ?? DEFAULT_SORT_DIRECTION,
		};
	} catch (error) {
		const message = error instanceof Error ? error.message : "Failed to load academic years";
		return {
			academicYears: [],
			paginator: null,
			error: message,
			page,
			sortField,
			sortDirection: directionParam ?? DEFAULT_SORT_DIRECTION,
		};
	}
};
