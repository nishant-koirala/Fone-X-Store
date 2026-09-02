<section>
    <header class="mb-8 border-b-2 border-brand-charcoal pb-4">
        <h2 class="font-display text-2xl uppercase tracking-tight text-brand-charcoal">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-2 font-mono text-xs uppercase tracking-widest text-brand-grey">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2 text-brand-red font-mono text-xs font-bold" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="font-mono text-xs font-bold uppercase tracking-widest text-brand-charcoal block mb-2">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="w-full bg-brand-white border-2 border-brand-charcoal px-4 py-3 font-mono font-bold text-sm focus:border-brand-red focus:ring-0" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-2 text-brand-red font-mono text-xs font-bold" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 p-4 border-2 border-brand-red bg-brand-red/5">
                    <p class="text-sm font-sans text-brand-charcoal">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="mt-2 font-mono text-xs font-bold uppercase tracking-widest text-brand-red hover:text-brand-charcoal underline decoration-brand-red hover:decoration-brand-charcoal underline-offset-4 transition-colors">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-mono text-xs font-bold text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-6 border-t-2 border-brand-charcoal flex items-center gap-6">
            <button type="submit" class="bg-brand-charcoal hover:bg-brand-red text-brand-white font-mono text-sm uppercase font-bold tracking-widest px-8 py-4 transition-colors shadow-[4px_4px_0px_0px_rgba(42,42,44,1)] hover:shadow-none hover:translate-y-1 hover:translate-x-1">
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
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
