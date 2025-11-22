import { graphqlRequest } from "./graphql";
import type {
	AcademicYear,
	AcademicYearPaginator,
	CreateAcademicYearInput,
	UpdateAcademicYearInput
} from "$lib/types/academic-year";

// Queries
const GET_ACADEMIC_YEARS = `
	query GetAcademicYears($page: Int, $first: Int, $orderBy: [OrderByClause!]) {
		academicYears(page: $page, first: $first, orderBy: $orderBy) {
			data {
				id
				name
				start_date
				end_date
				is_active
				description
				duration_in_days
				created_at
				updated_at
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

const GET_ACADEMIC_YEAR = `
	query GetAcademicYear($id: ID!) {
		academicYear(id: $id) {
			id
			name
			start_date
			end_date
			is_active
			description
			duration_in_days
			created_at
			updated_at
		}
	}
`;

const GET_ACTIVE_ACADEMIC_YEAR = `
	query GetActiveAcademicYear {
		activeAcademicYear {
			id
			name
			start_date
			end_date
			is_active
			description
			duration_in_days
			created_at
			updated_at
		}
	}
`;

// Mutations
const CREATE_ACADEMIC_YEAR = `
	mutation CreateAcademicYear($input: CreateAcademicYearInput!) {
		createAcademicYear(input: $input) {
			id
			name
			start_date
			end_date
			is_active
			description
			duration_in_days
			created_at
			updated_at
		}
	}
`;

const UPDATE_ACADEMIC_YEAR = `
	mutation UpdateAcademicYear($input: UpdateAcademicYearInput!) {
		updateAcademicYear(input: $input) {
			id
			name
			start_date
			end_date
			is_active
			description
			duration_in_days
			created_at
			updated_at
		}
	}
`;

const DELETE_ACADEMIC_YEAR = `
	mutation DeleteAcademicYear($id: ID!) {
		deleteAcademicYear(id: $id) {
			id
			name
		}
	}
`;

const SET_ACTIVE_ACADEMIC_YEAR = `
	mutation SetActiveAcademicYear($id: ID!) {
		setActiveAcademicYear(id: $id) {
			id
			name
			start_date
			end_date
			is_active
			description
			duration_in_days
			created_at
			updated_at
		}
	}
`;

// API Functions
export async function getAcademicYears({
	fetchImpl,
	page,
	first,
	orderBy
}: {
	fetchImpl?: typeof fetch;
	page?: number;
	first?: number;
	orderBy?: { column: string; order?: "ASC" | "DESC" }[];
} = {}): Promise<AcademicYearPaginator> {
	const data = await graphqlRequest<{ academicYears: AcademicYearPaginator }>({
		query: GET_ACADEMIC_YEARS,
		variables: {
			page,
			first,
			orderBy
		},
		fetchImpl
	});
	return data.academicYears;
}

export async function getAcademicYear(id: string): Promise<AcademicYear> {
    const data = await graphqlRequest<{ academicYear: AcademicYear }>({
        query: GET_ACADEMIC_YEAR,
        variables: { id }
    });
    return data.academicYear;
}

export async function getActiveAcademicYear(): Promise<AcademicYear | null> {
    const data = await graphqlRequest<{ activeAcademicYear: AcademicYear | null }>({
        query: GET_ACTIVE_ACADEMIC_YEAR
    });
    return data.activeAcademicYear;
}

export async function createAcademicYear(input: CreateAcademicYearInput): Promise<AcademicYear> {
    const data = await graphqlRequest<{ createAcademicYear: AcademicYear }>({
        query: CREATE_ACADEMIC_YEAR,
        variables: { input }
    });
    return data.createAcademicYear;
}

export async function updateAcademicYear(input: UpdateAcademicYearInput): Promise<AcademicYear> {
    const data = await graphqlRequest<{ updateAcademicYear: AcademicYear }>({
        query: UPDATE_ACADEMIC_YEAR,
        variables: { input }
    });
    return data.updateAcademicYear;
}

export async function deleteAcademicYear(id: string): Promise<void> {
    await graphqlRequest<{ deleteAcademicYear: { id: string } }>({
        query: DELETE_ACADEMIC_YEAR,
        variables: { id }
    });
}

export async function setActiveAcademicYear(id: string): Promise<AcademicYear> {
    const data = await graphqlRequest<{ setActiveAcademicYear: AcademicYear }>({
        query: SET_ACTIVE_ACADEMIC_YEAR,
        variables: { id }
    });
    return data.setActiveAcademicYear;
}
