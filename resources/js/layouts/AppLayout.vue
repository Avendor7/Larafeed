<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-title>
            <slot name="header-title" />
        </template>
        <template #header-actions>
            <slot name="header-actions" />
        </template>
        <slot />
    </AppLayout>
</template>
