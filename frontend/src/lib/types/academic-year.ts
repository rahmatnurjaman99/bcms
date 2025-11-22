export interface AcademicYear {
    id: string;
    name: string;
    start_date: string;
    end_date: string;
    is_active: boolean;
    description?: string;
    duration_in_days: number;
    created_at: string;
    updated_at: string;
}

export interface CreateAcademicYearInput {
    name: string;
    start_date: string;
    end_date: string;
    is_active?: boolean;
    description?: string;
}

export interface UpdateAcademicYearInput {
    id: string;
    name?: string;
    start_date?: string;
    end_date?: string;
    is_active?: boolean;
    description?: string;
}

export interface AcademicYearPaginator {
    data: AcademicYear[];
    paginatorInfo: {
        total: number;
        perPage: number;
        currentPage: number;
        lastPage: number;
    };
}
