<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;
const profile = usePage().props.profile;

const form = useForm({
    _method: 'PATCH',
    name: user.name,
    email: user.email,
    avatar: null as File | null,
    bio: profile?.bio ?? '',
    social_links: profile?.social_links ?? {}
});

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);

const selectNewAvatar = () => {
    avatarInput.value?.click();
};

const updateAvatarPreview = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    if (form.avatar) {
        form.post(route('profile.update'), {
            preserveScroll: true,
            preserveState: true,
        });
    } else {
        form.patch(route('profile.update'), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                    autocomplete="name" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="bio" value="Bio" />
                <textarea id="bio" v-model="form.bio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    rows="3" />
                <InputError :message="form.errors.bio" class="mt-2" />
            </div>

            <!-- Avatar Section -->
            <div class="flex items-center space-x-6">
                <div class="shrink-0">
                    <img v-if="avatarPreview" :src="avatarPreview" class="h-16 w-16 rounded-full object-cover"
                        alt="Avatar preview" />
                    <img v-else-if="profile?.avatar" :src="`/storage/${profile.avatar}`"
                        class="h-16 w-16 rounded-full object-cover" alt="Current avatar" />
                    <div v-else class="h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-700" />
                </div>

                <div class="mt-1 flex items-center gap-x-3">
                    <input ref="avatarInput" type="file" class="hidden" @change="updateAvatarPreview"
                        accept="image/*" />

                    <button type="button"
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        @click="selectNewAvatar">
                        Change avatar
                    </button>

                    <button v-if="avatarPreview" type="button" class="text-sm text-red-500 hover:text-red-700" @click="() => {
                        form.avatar = null;
                        avatarPreview = null;
                        if (avatarInput) avatarInput.value = '';
                    }">
                        Remove
                    </button>
                </div>
            </div>

            <InputError :message="form.errors.avatar" class="mt-2" />


            <div>
                <InputLabel value="Social Links" />
                <div v-for="platform in ['github', 'twitter', 'linkedin']" :key="platform">
                    <TextInput :id="platform" type="text" class="mt-1 block w-full"
                        v-model="form.social_links[platform]" :placeholder="platform" />
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800">
                    Click here to re-send the verification email.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
