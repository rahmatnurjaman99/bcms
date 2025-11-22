<script lang="ts">
	let { class: className = "" } = $props<{ class?: string }>();
</script>

<div class={`animate-pulse rounded-md bg-muted ${className}`}></div>
