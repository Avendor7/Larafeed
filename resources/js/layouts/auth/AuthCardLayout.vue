<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-neutral-950 p-6 text-neutral-100 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium text-neutral-100"
            >
                <div class="flex h-9 w-9 items-center justify-center rounded-md border border-fuchsia-500/30 bg-fuchsia-500/10">
                    <AppLogoIcon
                        class="size-6 fill-current text-fuchsia-200"
                    />
                </div>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-xl border border-fuchsia-500/20 bg-neutral-950">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription class="text-neutral-400">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
