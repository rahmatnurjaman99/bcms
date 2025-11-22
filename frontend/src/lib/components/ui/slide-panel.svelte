<script lang="ts">
		import { Button } from "$lib/components/ui/button";
		import { X } from "@lucide/svelte";
		import { fade, fly } from "svelte/transition";
		import { quintOut } from "svelte/easing";
		import type { Snippet } from "svelte";

		let {
			open,
			title,
			description = "",
			onClose,
			children,
			footer,
		} = $props<{
			open: boolean;
			title: string;
			description?: string;
			onClose?: () => void;
			children?: Snippet;
			footer?: Snippet;
		}>();

	function handleBackdropClick(event: MouseEvent) {
		if (event.target === event.currentTarget) {
			onClose?.();
		}
	}
</script>

{#if open}
	<div
		class="fixed inset-0 z-50 flex justify-end bg-black/40 backdrop-blur-sm"
		role="presentation"
		onclick={handleBackdropClick}
		transition:fade={{ duration: 180, easing: quintOut }}
	>
		<div
			class="flex h-full max-h-screen w-full max-w-xl flex-col overflow-hidden border-l bg-card text-foreground shadow-2xl"
			role="dialog"
			aria-modal="true"
			aria-label={title}
			transition:fly={{ x: 48, duration: 260, easing: quintOut }}
		>
			<div class="flex items-start justify-between border-b px-5 py-4">
				<div class="space-y-1">
					<h2 class="text-lg font-semibold leading-tight">{title}</h2>
					{#if description}
						<p class="text-sm text-muted-foreground">{description}</p>
					{/if}
				</div>
				<Button variant="ghost" size="icon" aria-label="Close panel" onclick={onClose}>
					<X class="h-5 w-5" />
				</Button>
			</div>
			<div class="flex-1 overflow-y-auto px-5 py-4">
				{@render children?.()}
			</div>
			<div class="border-t px-5 py-4">
				{@render footer?.()}
			</div>
		</div>
	</div>
{/if}
