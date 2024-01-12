<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('Turbo Laravel Test App') }}</title>

    {{ $head ?? null }}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    {{-- Use Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree'],
                    },
                    animation: {
                        'appear-then-fade-out': 'appear-then-fade-out 3s both',
                    },

                    keyframes: () => ({
                        ['appear-then-fade-out']: {
                            '0%, 100%': { opacity: 0 },
                            '10%, 80%': { opacity: 1 },
                        },
                    }),
                },
            },
            plugins: [
                ({ addVariant }) => addVariant('dialog', ['&dialog', 'dialog &']),
            ],
        }
    </script>

    {{-- Install Turbo via CDN --}}
    <script type="module">
        import * as Turbo from 'https://cdn.skypack.dev/@hotwired/turbo@7.3.0'
        import { Application, Controller } from 'https://cdn.skypack.dev/@hotwired/stimulus'
        import { install, uninstall } from 'https://cdn.skypack.dev/@github/hotkey'

        window.Stimulus = Application.start()

        Stimulus.register("modal", class extends Controller {
            static values = {
                open: { type: Boolean, default: false },
            }

            close() {
                this.openValue = false
            }

            closeAfterSubmitEndsSuccessfully(e) {
                if (! e.detail?.success || false) return

                this.close()
            }

            toggle() {
                this.openValue = ! this.openValue
            }

            openValueChanged() {
                if (this.openValue) {
                    this.element.showModal()
                } else {
                    this.element.close();
                }
            }
        })

        Stimulus.register("modal-trigger", class extends Controller {
            static outlets = ["modal"]

            toggle() {
                this.modalOutlet.toggle()
            }
        })

        Stimulus.register("remover", class extends Controller {
            remove() {
                this.element.remove()
            }
        })

        Stimulus.register("cancellable-form", class extends Controller {
            static targets = ["cancelTrigger"]

            connect() {
                this.originalData = this.currentState
            }

            cancel() {
                if (this.changed || ! this.hasCancelTriggerTarget) return

                this.cancelTriggerTarget.click()
            }

            get currentState() {
                return [...new FormData(this.element).values()]
            }

            get changed() {
                return JSON.stringify(this.originalData) !== JSON.stringify(this.currentState)
            }
        })

        Stimulus.register("hotkeys", class extends Controller {
            static values = {
                shortcut: String,
            }

            connect() {
                install(this.element, this.shortcutValue)
            }

            disconnect() {
                uninstall(this.element)
            }
        })
    </script>
</head>
<body class="bg-gray-100">
    <main class="max-w-xl mx-auto" {{ $attributes ?? '' }}>
        @include('partials._notifications')

        {{ $slot }}
    </main>
</body>
</html>
