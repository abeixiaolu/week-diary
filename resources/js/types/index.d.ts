import { Config } from 'ziggy-js';

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
}

export interface UserProfile {
  id: number;
  avatar: string;
  bio: string;
  social_links: Record<string, string>;
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: User;
  };
  profile: UserProfile;
  ziggy: Config & { location: string };
};
