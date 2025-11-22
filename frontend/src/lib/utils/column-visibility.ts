import { browser } from "$app/environment";

export type ColumnVisibilityOption = {
    id: string;
    label: string;
    defaultVisible?: boolean;
    alwaysVisible?: boolean;
};

export type ColumnVisibilityMap = Record<string, boolean>;

export function buildDefaultVisibility(
    columns: ColumnVisibilityOption[],
): ColumnVisibilityMap {
    return columns.reduce<ColumnVisibilityMap>((acc, column) => {
        acc[column.id] = column.defaultVisible ?? true;
        return acc;
    }, {});
}

export function loadColumnVisibility(
    storageKey: string,
    columns: ColumnVisibilityOption[],
): ColumnVisibilityMap {
    const defaults = buildDefaultVisibility(columns);
    if (!browser) {
        return defaults;
    }

    try {
        const stored = localStorage.getItem(storageKey);
        if (!stored) {
            return defaults;
        }

        const parsed = JSON.parse(stored) as ColumnVisibilityMap;
        return { ...defaults, ...parsed };
    } catch {
        return defaults;
    }
}

export function persistColumnVisibility(
    storageKey: string,
    visibility: ColumnVisibilityMap,
) {
    if (!browser) return;

    try {
        localStorage.setItem(storageKey, JSON.stringify(visibility));
    } catch {
        // ignore write errors (storage quota / private mode, etc)
    }
}
