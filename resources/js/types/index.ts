export * from './auth';
export * from './navigation';
export * from './ui';

import type { Auth } from './auth';

type SidebarFeed = {
    id: number;
    title: string;
};

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    navigation: {
        feeds: SidebarFeed[];
    };
    sidebarOpen: boolean;
    [key: string]: unknown;
};
