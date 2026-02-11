<script setup lang="ts">
import { Head, Link, Form } from '@inertiajs/vue3';
import { ArrowLeft, Bookmark, ExternalLink } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { bookmark as feedItemBookmark } from '@/routes/feed-items';
import { type BreadcrumbItem } from '@/types';

type FeedItemDetail = {
    id: number;
    title?: string | null;
    url?: string | null;
    summary?: string | null;
    content?: string | null;
    published_at?: string | null;
    is_bookmarked: boolean;
    feed: {
        id: number;
        title: string;
    };
};

type Props = {
    item: FeedItemDetail;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: props.item.title ?? 'Story',
    },
];

const formatDate = (value?: string | null) => {
    if (!value) {
        return null;
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
};
</script>

<template>
    <Head :title="item.title ?? 'Story'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-title>
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                    {{ item.feed.title }}
                </p>
                <h1 class="truncate text-lg font-semibold text-foreground">
                    {{ item.title ?? 'Untitled story' }}
                </h1>
            </div>
        </template>
        <template #header-actions>
            <Form v-bind="feedItemBookmark.form({ feedItem: item.id })">
                <button
                    type="submit"
                    class="flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm text-foreground transition hover:border-border/70"
                >
                    <Bookmark
                        class="h-4 w-4"
                        :class="item.is_bookmarked ? 'fill-current text-sky-400' : 'text-muted-foreground'"
                    />
                    <span class="hidden text-sm md:inline">Bookmark</span>
                </button>
            </Form>
            <a
                v-if="item.url"
                :href="item.url"
                target="_blank"
                rel="noreferrer"
                class="flex h-9 items-center gap-2 rounded-lg bg-sky-600 px-3 text-sm font-medium text-white transition hover:bg-sky-500"
            >
                <ExternalLink class="h-4 w-4" />
                <span class="hidden md:inline">Open original</span>
            </a>
        </template>

        <div class="flex flex-1 flex-col gap-6 px-6 py-6">
            <div class="rounded-2xl border border-border bg-card/60 p-6">
                <div class="space-y-2">
                    <Link
                        :href="dashboard()"
                        class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to dashboard
                    </Link>
                    <div class="space-y-1">
                        <h2 class="text-2xl font-semibold text-foreground md:text-3xl">
                            {{ item.title ?? 'Untitled story' }}
                        </h2>
                        <p v-if="item.published_at" class="text-sm text-muted-foreground">
                            {{ formatDate(item.published_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-card/60 p-6">
                <div
                    v-if="item.content"
                    class="prose max-w-none prose-a:text-sky-600 prose-a:no-underline hover:prose-a:text-sky-500 dark:prose-invert dark:prose-a:text-sky-200 dark:hover:prose-a:text-sky-100"
                    v-html="item.content"
                ></div>

                <div v-else class="rounded-lg border border-dashed border-border p-6">
                    <p class="text-sm text-muted-foreground">
                        This feed item does not include full content. Showing the summary instead.
                    </p>
                    <p v-if="item.summary" class="mt-3 text-base text-foreground">
                        {{ item.summary }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
