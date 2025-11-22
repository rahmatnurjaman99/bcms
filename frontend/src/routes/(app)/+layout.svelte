<script lang="ts">
    import type { LayoutData } from "./$types";
    import type { Role } from "$lib/types/user";
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from "$lib/components/ui/avatar";
    import { Button } from "$lib/components/ui/button";
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from "$lib/components/ui/dropdown-menu";
    import { logout as apiLogout } from "$lib/api/auth";
    import { hasPermission } from "$lib/utils/permissions";
    import {
        ChevronDown,
        ChevronRight,
        KeyRound,
        LayoutDashboard,
        LogOut,
        Moon,
        PanelLeftClose,
        PanelLeftOpen,
        History,
        Sun,
        CalendarClock,
        Shield,
        Users,
    } from "@lucide/svelte";
    import { APP_NAME } from "$lib/config/app";
    import { goto } from "$app/navigation";
    import { page } from "$app/stores";
    import { mode, toggleMode } from "mode-watcher";

    let { data, children } = $props<{ data: LayoutData; children: any }>();

    const viewer = data.viewer;
    const viewerInitials = viewer.name
        .split(/\s+/)
        .map((part: string) => part?.[0])
        .filter(Boolean)
        .slice(0, 2)
        .join("")
        .toUpperCase();
    const viewerRoleSummary = viewer.roles
        .map((role: Role) => role.name)
        .join(", ");
    let sidebarCollapsed = $state(false);
    let mobileNavOpen = $state(false);

    const navItems = [
        {
            type: "item",
            label: "Dashboard",
            href: "/dashboard",
            icon: LayoutDashboard,
            permission: null as string | null,
        },
        {
            type: "item",
            label: "Academic Years",
            href: "/academic-years",
            icon: CalendarClock,
            permission: null as string | null,
            },
        {
            type: "item",
            label: "Activity",
            href: "/activity",
            icon: History,
            permission: "activity.view" as string | null,
        },
        {
            type: "group",
            label: "User Management",
            icon: Users,
            permission: "users.view" as string | null,
            items: [
                {
                    label: "Users",
                    href: "/users",
                    icon: Users,
                    permission: "users.view",
                },
                {
                    label: "Roles",
                    href: "/roles",
                    icon: Shield,
                    permission: "roles.manage",
                },
                {
                    label: "Permissions",
                    href: "/permissions",
                    icon: KeyRound,
                    permission: "roles.manage",
                },
            ],
        },
    ] as const;

    const filteredNav = navItems
        .map((item) => {
            if (item.type === "item") {
                return !item.permission ||
                    hasPermission(viewer, item.permission)
                    ? item
                    : null;
            }

            // Group: filter its children
            const filteredChildren = item.items.filter(
                (child) =>
                    !child.permission ||
                    hasPermission(viewer, child.permission),
            );
            if (
                !filteredChildren.length ||
                (item.permission && !hasPermission(viewer, item.permission))
            ) {
                return null;
            }

            return { ...item, items: filteredChildren };
        })
        .filter(Boolean);

    let userMgmtOpen = $state(true);

    async function handleSignOut() {
        await apiLogout();
        goto("/login");
    }

    function toggleSidebar() {
        sidebarCollapsed = !sidebarCollapsed;
    }

    function toggleMobileNav() {
        mobileNavOpen = !mobileNavOpen;
    }

    function closeMobileNav() {
        mobileNavOpen = false;
    }

    function handleOverlayKeydown(event: KeyboardEvent) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            closeMobileNav();
        }
    }
</script>

<svelte:head>
    <title>{APP_NAME} | Dashboard</title>
</svelte:head>

<div class="min-h-screen overflow-x-hidden bg-muted/30 text-foreground">
    <div class="flex min-h-screen">
        {#if mobileNavOpen}
            <div
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
                role="button"
                tabindex="0"
                onclick={closeMobileNav}
                onkeydown={handleOverlayKeydown}
                aria-label="Close navigation"
            ></div>
        {/if}
        <aside
            class={`fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full transform flex-col border-r bg-card/80 px-4 py-6 shadow-lg transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0 lg:border-r lg:bg-card/80 lg:shadow-sm ${sidebarCollapsed ? "lg:w-20 lg:px-3" : "lg:w-64 lg:px-6"} ${mobileNavOpen ? "translate-x-0" : ""}`}
            aria-label="Primary"
        >
            <div
                class={`mb-8 ${sidebarCollapsed ? "flex flex-col items-center gap-2 text-center" : ""}`}
            >
                <p
                    class="text-xs font-semibold uppercase tracking-[0.4em] text-primary"
                >
                    {APP_NAME}
                </p>
                <h1
                    class={`text-xl font-semibold ${sidebarCollapsed ? "sr-only" : ""}`}
                >
                    Control Center
                </h1>
            </div>

            <nav class="space-y-1">
                {#each filteredNav as entry}
                    {#if entry?.type === "item"}
                        {@const Icon = entry.icon}
                        <a
                            href={entry.href}
                            class={`flex items-center rounded-lg py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground ${
                                sidebarCollapsed
                                    ? "justify-center px-2"
                                    : "gap-3 px-3"
                            } ${$page.url.pathname === entry.href ? "bg-primary/10 text-primary" : "text-muted-foreground"}`}
                            aria-current={$page.url.pathname === entry.href
                                ? "page"
                                : undefined}
                            aria-label={sidebarCollapsed
                                ? entry.label
                                : undefined}
                            title={sidebarCollapsed ? entry.label : undefined}
                        >
                            <Icon class="h-4 w-4" />
                            <span class={sidebarCollapsed ? "sr-only" : ""}
                                >{entry.label}</span
                            >
                        </a>
                    {:else if entry}
                        {@const Icon = entry.icon}
                        <button
                            type="button"
                            class={`flex w-full items-center rounded-lg py-2 text-sm font-semibold transition-colors hover:bg-accent hover:text-accent-foreground ${
                                sidebarCollapsed
                                    ? "justify-center px-2"
                                    : "gap-3 px-3"
                            } ${userMgmtOpen ? "text-foreground" : "text-muted-foreground"}`}
                            onclick={() => (userMgmtOpen = !userMgmtOpen)}
                            aria-expanded={userMgmtOpen}
                        >
                            <Icon class="h-4 w-4" />
                            <span class={sidebarCollapsed ? "sr-only" : ""}
                                >{entry.label}</span
                            >
                            {#if !sidebarCollapsed}
                                {#if userMgmtOpen}
                                    <ChevronDown class="ml-auto h-4 w-4" />
                                {:else}
                                    <ChevronRight class="ml-auto h-4 w-4" />
                                {/if}
                            {/if}
                        </button>
                        {#if userMgmtOpen}
                            <div
                                class={`${sidebarCollapsed ? "pl-0" : "pl-3"} space-y-1`}
                            >
                                {#each entry.items as child}
                                    {@const IconChild = child.icon}
                                    <a
                                        href={child.href}
                                        class={`flex items-center rounded-lg py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground ${
                                            sidebarCollapsed
                                                ? "justify-center px-2"
                                                : "gap-3 px-3"
                                        } ${$page.url.pathname === child.href ? "bg-primary/10 text-primary" : "text-muted-foreground"}`}
                                        aria-current={$page.url.pathname ===
                                        child.href
                                            ? "page"
                                            : undefined}
                                        aria-label={sidebarCollapsed
                                            ? child.label
                                            : undefined}
                                        title={sidebarCollapsed
                                            ? child.label
                                            : undefined}
                                    >
                                        <IconChild class="h-4 w-4" />
                                        <span
                                            class={sidebarCollapsed
                                                ? "sr-only"
                                                : ""}>{child.label}</span
                                        >
                                    </a>
                                {/each}
                            </div>
                        {/if}
                    {/if}
                {/each}
            </nav>

            <div
                class={`mt-auto rounded-lg border border-dashed p-4 text-xs text-muted-foreground ${
                    sidebarCollapsed ? "text-center flex flex-col items-center gap-2" : ""
                }`}
            >
                {#if sidebarCollapsed}
                    <Avatar class="flex h-10 w-10 items-center justify-center border">
                        {#if viewer.avatar_url}
                            <AvatarImage
                                src={viewer.avatar_url}
                                alt={`${viewer.name} avatar`}
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                crossorigin="anonymous"
                            />
                        {/if}
                        <AvatarFallback
                            class="bg-primary/10 text-sm font-semibold text-primary"
                        >
                            {viewerInitials}
                        </AvatarFallback>
                    </Avatar>
                    <p class="mt-2 text-xs font-semibold text-foreground">
                        {viewer.name}
                    </p>
                {:else}
                    <div class="flex items-center gap-3">
                        <Avatar class="h-12 w-12 border">
                            {#if viewer.avatar_url}
                                <AvatarImage
                                    src={viewer.avatar_url}
                                    alt={`${viewer.name} avatar`}
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                    crossorigin="anonymous"
                                />
                            {/if}
                            <AvatarFallback
                                class="bg-primary/10 text-sm font-semibold text-primary"
                            >
                                {viewerInitials}
                            </AvatarFallback>
                        </Avatar>
                        <div class="space-y-0.5">
                            <p class="font-semibold text-foreground">
                                Logged in as
                            </p>
                            <p class="text-sm font-medium text-foreground">
                                {viewer.name}
                            </p>
                            <p>{viewer.email}</p>
                        </div>
                    </div>
                {/if}
            </div>
        </aside>

        <div class="flex flex-1 flex-col min-w-0">
            <header
                class="flex items-center gap-3 border-b bg-background/90 px-4 py-3 backdrop-blur"
            >
                <Button
                    variant="outline"
                    size="icon"
                    class="inline-flex lg:hidden"
                    onclick={toggleMobileNav}
                    aria-label={mobileNavOpen ? "Close navigation" : "Open navigation"}
                    aria-pressed={mobileNavOpen}
                >
                    {#if mobileNavOpen}
                        <PanelLeftClose class="h-4 w-4" />
                    {:else}
                        <PanelLeftOpen class="h-4 w-4" />
                    {/if}
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    class="hidden lg:inline-flex"
                    onclick={toggleSidebar}
                    aria-label={sidebarCollapsed
                        ? "Expand sidebar"
                        : "Collapse sidebar"}
                    aria-pressed={sidebarCollapsed}
                >
                    {#if sidebarCollapsed}
                        <PanelLeftOpen class="h-4 w-4" />
                    {:else}
                        <PanelLeftClose class="h-4 w-4" />
                    {/if}
                </Button>
                <div class="lg:hidden">
                    <p class="text-lg font-semibold">{APP_NAME}</p>
                    <p class="text-xs text-muted-foreground">
                        Welcome back, {viewer.name}
                    </p>
                </div>
                <div class="ml-auto flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="rounded-full border"
                        onclick={toggleMode}
                        aria-label={`Switch to ${mode.current === "dark" ? "light" : "dark"} mode`}
                        aria-pressed={mode.current === "dark"}
                    >
                        {#if mode.current === "dark"}
                            <Sun class="h-4 w-4" />
                        {:else}
                            <Moon class="h-4 w-4" />
                        {/if}
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            class="flex items-center gap-3 rounded-full border px-2 py-1.5 text-left text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <div class="hidden flex-col text-right sm:flex">
                                <span class="text-sm font-semibold"
                                    >{viewer.name}</span
                                >
                                <span class="text-xs text-muted-foreground"
                                    >{viewerRoleSummary}</span
                                >
                            </div>
                            <Avatar class="h-10 w-10 border">
                                {#if viewer.avatar_url}
                                    <AvatarImage
                                        src={viewer.avatar_url}
                                        alt={`${viewer.name} avatar`}
                                        loading="lazy"
                                        referrerpolicy="no-referrer"
                                        crossorigin="anonymous"
                                    />
                                {/if}
                                <AvatarFallback
                                    class="bg-primary/10 text-sm font-semibold text-primary"
                                >
                                    {viewerInitials}
                                </AvatarFallback>
                            </Avatar>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-60" align="end">
                            <DropdownMenuLabel class="flex flex-col gap-1">
                                <span
                                    class="text-sm font-semibold text-foreground"
                                    >{viewer.name}</span
                                >
                                <span
                                    class="text-xs text-muted-foreground break-all"
                                    >{viewer.email}</span
                                >
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem onSelect={() => goto("/profile")}>
                                Profile
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                variant="destructive"
                                onSelect={handleSignOut}
                            >
                                <LogOut class="h-4 w-4" />
                                Sign out
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <main class="flex-1 min-w-0 px-4 py-6 sm:px-8 overflow-x-auto">
                {@render children?.()}
            </main>
        </div>
    </div>
</div>
