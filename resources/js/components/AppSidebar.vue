<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ListTree, Rss, Settings2 } from 'lucide-vue-next';
import { computed } from 'vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { show as feedShow } from '@/routes/feeds';
import { manage as feedManage } from '@/routes/feeds';
import AppLogo from './AppLogo.vue';

type SidebarFeed = {
    id: number;
    title: string;
};

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();

const feedNavItems = computed(() => (page.props.navigation.feeds as SidebarFeed[]) ?? []);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-fuchsia-500/10 bg-neutral-950">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup class="px-2 pt-0">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard())"
                            tooltip="All Stories"
                            class="border border-fuchsia-500/40 bg-fuchsia-500/15 text-fuchsia-100 hover:bg-fuchsia-500/25 hover:text-fuchsia-50 data-[active=true]:bg-fuchsia-400/30 data-[active=true]:text-white"
                        >
                            <Link :href="dashboard()">
                                <ListTree />
                                <span>All Stories</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Feeds</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="feed in feedNavItems"
                            :key="feed.id"
                        >
                            <SidebarMenuButton
                                as-child
                                :is-active="isCurrentUrl(feedShow(feed.id))"
                                :tooltip="feed.title"
                            >
                                <Link :href="feedShow(feed.id)">
                                    <Rss />
                                    <span>{{ feed.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem v-if="feedNavItems.length === 0">
                            <SidebarMenuButton
                                aria-disabled="true"
                                class="text-neutral-500"
                                tooltip="No feeds yet"
                            >
                                <Rss />
                                <span>No feeds yet</span>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(feedManage())"
                        tooltip="Manage Feeds"
                    >
                        <Link :href="feedManage()">
                            <Settings2 />
                            <span>Manage Feeds</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
