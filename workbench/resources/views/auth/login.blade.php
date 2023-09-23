<x-app-layout>
    <x-slot name="title">{{ __('Login') }}</x-slot>

    <form action="{{ route('login.store') }}" method="post">
        <div>
            <label for="email">{{ __('Email') }}</label>
            <input type="email" name="email" />
            @error('email')
            <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password">{{ __('Password') }}</label>
            <input type="password" name="password" />
            @error('password')
            <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <button type="submit">{{ __('Login') }}</button>
        </div>
    </form>
</x-app-layout>
