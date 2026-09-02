<section>
    <header class="mb-8 border-b-2 border-brand-charcoal pb-4">
        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-2 font-mono text-xs uppercase tracking-widest text-brand-grey">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
        </div>

        <div>
            <label for="update_password_password" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-brand-red font-mono text-xs font-bold" />
        </div>

        <div class="pt-6 border-t-2 border-brand-charcoal flex items-center gap-6">
            <button type="submit" class="bg-brand-charcoal hover:bg-brand-red text-brand-white font-mono text-sm uppercase font-bold tracking-widest px-8 py-4 transition-colors shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-y-1 hover:translate-x-1">
                {{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="font-mono text-xs font-bold uppercase tracking-widest text-green-600"
                >{{ __('Saved successfully.') }}</p>
            @endif
        </div>
    </form>
</section>
