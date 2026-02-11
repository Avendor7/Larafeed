<script setup lang="ts">
import { computed } from 'vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
        title?: string;
    }>(),
    {
        breadcrumbs: () => [],
        title: undefined,
    },
);

const fallbackTitle = computed(() => {
    if (props.title) {
        return props.title;
    }

    return props.breadcrumbs?.at(-1)?.title ?? 'Dashboard';
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-border bg-card px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
    >
        <div class="flex min-w-0 items-center gap-3">
            <SidebarTrigger class="-ml-1 text-muted-foreground hover:text-foreground" />
            <div class="min-w-0">
                <slot name="title">
                    <h1 class="truncate text-lg font-semibold text-foreground">
                        {{ fallbackTitle }}
                    </h1>
                </slot>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <slot name="actions" />
        </div>
    </header>
</template>
