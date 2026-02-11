<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Bookmark, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import FeedController from '@/actions/App/Http/Controllers/FeedController';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { bookmark as feedItemBookmark, show as feedItemShow } from '@/routes/feed-items';
import { type BreadcrumbItem } from '@/types';

type FeedSummary = {
    id: number;
    title: string;
    url: string;
    site_url?: string | null;
    description?: string | null;
    last_fetched_at?: string | null;
    items_count: number;
    unread_count: number;
};

type FeedItemSummary = {
    id: number;
    title?: string | null;
    url?: string | null;
    summary?: string | null;
    published_at?: string | null;
    is_bookmarked: boolean;
    feed: {
        id: number;
        title: string;
    };
};

type BookmarkSummary = {
    id: number;
    title?: string | null;
    published_at?: string | null;
    feed: {
        id: number;
        title: string;
    };
};

type DashboardStats = {
    total_articles: number;
    new_this_week: number;
    unread: number;
    unread_priority: number;
    bookmarked: number;
    bookmarked_today: number;
    active_feeds: number;
    failing_feeds: number;
};

type Props = {
    feeds: FeedSummary[];
    items: FeedItemSummary[];
    bookmarks: BookmarkSummary[];
    stats: DashboardStats;
    status?: string | null;
    search?: string;
    filter?: string;
};

const props = withDefaults(defineProps<Props>(), {
    search: '',
    filter: 'today',
});

const isAddFeedOpen = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const feedBadgeStyles = [
    'bg-orange-900/50 text-orange-400',
    'bg-red-900/50 text-red-400',
    'bg-emerald-900/50 text-emerald-400',
    'bg-sky-900/50 text-sky-400',
    'bg-purple-900/50 text-purple-400',
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

const formatRelativeTime = (value?: string | null) => {
    if (!value) {
        return null;
    }

    const diffMs = Date.now() - new Date(value).getTime();
    const minutes = Math.max(1, Math.floor(diffMs / 60000));

    if (minutes < 60) {
        return `${minutes}m`;
    }

    const hours = Math.floor(minutes / 60);

    if (hours < 24) {
        return `${hours}h`;
    }

    const days = Math.floor(hours / 24);

    return `${days}d`;
};

const totalItemsLabel = computed(() => props.stats.new_this_week > 0
    ? `+${props.stats.new_this_week} this week`
    : 'No new stories this week');

const isSearching = computed(() => props.search.trim().length > 0);
const activeFilter = computed(() => (isSearching.value ? 'search' : props.filter));
const filterLabel = computed(() => {
    switch (activeFilter.value) {
        case 'bookmarks':
            return 'Bookmarked';
        case 'all':
            return 'All stories';
        case 'search':
            return 'Search results';
        default:
            return 'Today';
    }
});

const filterLinks = computed(() => [
    { label: 'Today', value: 'today' },
    { label: 'All', value: 'all' },
    { label: 'Bookmarks', value: 'bookmarks' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-title>
            <h1 class="text-lg font-semibold text-foreground">Dashboard</h1>
        </template>
        <template #header-actions>
            <Form
                :action="dashboard().url"
                method="get"
                class="relative hidden items-center md:flex"
            >
                <Search class="absolute left-3 h-4 w-4 text-muted-foreground" />
                <input
                    name="search"
                    :value="search"
                    type="search"
                    placeholder="Search feeds..."
                    class="w-64 rounded-lg border border-border bg-background px-9 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-sky-500 focus:outline-none"
                />
            </Form>
            <Dialog v-model:open="isAddFeedOpen">
                <DialogTrigger as-child>
                    <button
                        type="button"
                        class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-500"
                    >
                        + Add Feed
                    </button>
                </DialogTrigger>
                <DialogContent class="border border-border bg-card text-foreground sm:max-w-md">
                    <DialogHeader class="space-y-2">
                        <DialogTitle class="text-lg">Add a feed</DialogTitle>
                        <DialogDescription class="text-sm text-muted-foreground">
                            Paste an RSS or Atom feed URL to start collecting stories.
                        </DialogDescription>
                    </DialogHeader>
                    <Form
                        v-bind="FeedController.store.form()"
                        :options="{
                            preserveScroll: true,
                            onSuccess: () => {
                                isAddFeedOpen = false;
                            },
                        }"
                        reset-on-success
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div class="space-y-2">
                            <label for="url" class="text-sm font-medium text-muted-foreground">
                                Feed URL
                            </label>
                            <input
                                id="url"
                                name="url"
                                type="url"
                                placeholder="https://example.com/rss.xml"
                                required
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-sky-500 focus:outline-none"
                            />
                            <InputError :message="errors.url" />
                            <InputError :message="errors.url_hash" />
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Add feed
                            </button>
                        </div>
                    </Form>
                </DialogContent>
            </Dialog>
        </template>

        <div class="flex flex-1 flex-col gap-6 px-6 py-6">
            <div
                v-if="status"
                class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-200"
            >
                {{ status }}
            </div>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" id="stats">
                <div class="rounded-xl border border-border bg-card/70 p-4">
                    <div class="text-sm text-muted-foreground">Total Articles</div>
                    <div class="mt-1 text-2xl font-semibold text-foreground">
                        {{ stats.total_articles.toLocaleString() }}
                    </div>
                    <div class="mt-1 text-xs text-emerald-500 dark:text-emerald-400">
                        {{ totalItemsLabel }}
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card/70 p-4">
                    <div class="text-sm text-muted-foreground">Unread</div>
                    <div class="mt-1 text-2xl font-semibold text-foreground">
                        {{ stats.unread.toLocaleString() }}
                    </div>
                    <div class="mt-1 text-xs text-amber-500 dark:text-amber-400">
                        {{ stats.unread_priority }} high priority
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card/70 p-4">
                    <div class="text-sm text-muted-foreground">Bookmarked</div>
                    <div class="mt-1 text-2xl font-semibold text-foreground">
                        {{ stats.bookmarked.toLocaleString() }}
                    </div>
                    <div class="mt-1 text-xs text-muted-foreground">
                        {{ stats.bookmarked_today }} added today
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card/70 p-4">
                    <div class="text-sm text-muted-foreground">Active Feeds</div>
                    <div class="mt-1 text-2xl font-semibold text-foreground">
                        {{ stats.active_feeds.toLocaleString() }}
                    </div>
                    <div class="mt-1 text-xs text-muted-foreground">
                        {{ stats.failing_feeds }} failing
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="space-y-4" id="today">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">
                            {{ filterLabel }}
                        </h2>
                        <div class="flex items-center gap-2 text-xs">
                            <Link
                                v-for="link in filterLinks"
                                :key="link.value"
                                :href="dashboard({ query: { filter: link.value } })"
                                class="rounded-full border border-border px-3 py-1 text-muted-foreground transition hover:text-foreground"
                                :class="activeFilter === link.value ? 'border-sky-500/40 text-foreground' : ''"
                            >
                                {{ link.label }}
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="items.length === 0"
                        class="rounded-xl border border-dashed border-border bg-card/60 p-4 text-sm text-muted-foreground"
                    >
                        <span v-if="activeFilter === 'bookmarks'">No bookmarks yet.</span>
                        <span v-else-if="activeFilter === 'all'">No stories yet. Add a feed to get started.</span>
                        <span v-else-if="activeFilter === 'search'">No stories match that search yet.</span>
                        <span v-else>No stories yet today. Check back after your next feed refresh.</span>
                    </div>

                    <div v-else class="space-y-3">
                        <article
                            v-for="(item, index) in items"
                            :key="item.id"
                            class="rounded-xl border border-border bg-card/60 p-4 transition hover:border-border/70"
                        >
                            <div class="flex gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg font-semibold"
                                    :class="feedBadgeStyles[index % feedBadgeStyles.length]"
                                >
                                    {{ item.feed.title.slice(0, 1).toUpperCase() }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <Link
                                            :href="feedItemShow(item.id)"
                                            class="truncate font-medium text-foreground transition hover:text-foreground/80"
                                        >
                                            {{ item.title ?? 'Untitled story' }}
                                        </Link>
                                        <span class="text-xs text-muted-foreground">
                                            {{ formatRelativeTime(item.published_at) ?? 'Now' }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="item.summary"
                                        class="mt-1 line-clamp-1 text-sm text-muted-foreground"
                                    >
                                        {{ item.summary }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="rounded bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                                            {{ item.feed.title }}
                                        </span>
                                        <span
                                            v-if="item.is_bookmarked"
                                            class="rounded bg-sky-500/10 px-2 py-0.5 text-xs text-sky-700 dark:bg-sky-900/50 dark:text-sky-300"
                                        >
                                            Bookmarked
                                        </span>
                                    </div>
                                </div>
                                <Form
                                    v-bind="feedItemBookmark.form({ feedItem: item.id })"
                                    :options="{ preserveScroll: true }"
                                >
                                    <button
                                        type="submit"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground transition hover:border-border/70 hover:text-foreground"
                                        :class="item.is_bookmarked ? 'text-sky-400' : ''"
                                        aria-label="Toggle bookmark"
                                    >
                                        <Bookmark
                                            class="h-4 w-4"
                                            :class="item.is_bookmarked ? 'fill-current' : ''"
                                        />
                                    </button>
                                </Form>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border border-border bg-card/60 p-4" id="feeds">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Feeds</h3>
                        <div class="space-y-2">
                            <a
                                v-for="feed in feeds"
                                :key="feed.id"
                                :href="feed.site_url ?? feed.url"
                                target="_blank"
                                rel="noreferrer"
                                class="flex items-center justify-between rounded-lg px-2 py-2 text-sm text-foreground transition hover:bg-muted/70"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded bg-muted text-xs font-semibold text-muted-foreground">
                                        {{ feed.title.slice(0, 2).toUpperCase() }}
                                    </div>
                                    <span class="truncate">{{ feed.title }}</span>
                                </div>
                                <span
                                    class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                >
                                    {{ feed.unread_count }}
                                </span>
                            </a>
                            <div
                                v-if="feeds.length === 0"
                                class="rounded-lg border border-dashed border-border bg-card/60 p-3 text-xs text-muted-foreground"
                            >
                                Add a feed to start tracking new items.
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-border bg-card/60 p-4"
                        id="bookmarks"
                    >
                        <h3 class="mb-3 text-sm font-semibold text-foreground">
                            Recent Bookmarks
                        </h3>
                        <div class="space-y-3">
                            <Link
                                v-for="bookmark in bookmarks"
                                :key="bookmark.id"
                                :href="feedItemShow(bookmark.id)"
                                class="block rounded-lg px-2 py-2 transition hover:bg-muted/70"
                            >
                                <div class="text-sm font-medium text-foreground line-clamp-1">
                                    {{ bookmark.title ?? 'Untitled story' }}
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    {{ bookmark.feed.title }}
                                    <span v-if="bookmark.published_at">
                                        · {{ formatDate(bookmark.published_at) }}
                                    </span>
                                </div>
                            </Link>
                            <div
                                v-if="bookmarks.length === 0"
                                class="rounded-lg border border-dashed border-border bg-card/60 p-3 text-xs text-muted-foreground"
                            >
                                Save stories to see them here.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
