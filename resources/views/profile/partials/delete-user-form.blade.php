<section class="space-y-6">
    <header class="mb-8 border-b-2 border-brand-red pb-4">
        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-red">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-2 font-mono text-xs uppercase tracking-widest text-brand-charcoal">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-sm uppercase font-bold tracking-widest px-8 py-4 transition-colors shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-y-1 hover:translate-x-1"
    >{{ __('Delete Account') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 border-4 border-brand-red bg-brand-white">
            @csrf
            @method('delete')

            <h2 class="font-display text-2xl uppercase tracking-tight text-brand-red mb-4">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="font-mono text-xs uppercase tracking-widest text-brand-charcoal mb-6 leading-relaxed">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mb-8">
                <label for="password" class="sr-only">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full bg-brand-offwhite border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0"
                    placeholder="{{ __('Enter Password to Confirm') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
            </div>

            <div class="flex justify-end space-x-4 border-t-2 border-brand-red pt-6">
                <button type="button" x-on:click="$dispatch('close')" class="text-brand-grey hover:text-brand-charcoal font-mono text-xs font-bold uppercase tracking-widest px-6 py-4 transition-colors">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="bg-brand-red hover:bg-brand-charcoal text-brand-white font-mono text-xs uppercase font-bold tracking-widest px-8 py-4 transition-colors">
                    {{ __('Permanently Delete') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
