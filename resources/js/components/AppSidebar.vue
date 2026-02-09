<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bookmark, Clock, LayoutGrid, Rss } from 'lucide-vue-next';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Today',
        href: `${dashboard().url}#today`,
        icon: Clock,
    },
    {
        title: 'Feeds',
        href: `${dashboard().url}#feeds`,
        icon: Rss,
    },
    {
        title: 'Bookmarks',
        href: `${dashboard().url}#bookmarks`,
        icon: Bookmark,
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="sidebar"
        class="border-neutral-800 bg-neutral-950 text-neutral-200"
    >
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-lg font-semibold text-white"
                            >
                                R
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
