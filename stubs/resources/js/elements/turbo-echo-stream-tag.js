import { connectStreamSource, disconnectStreamSource } from '@hotwired/turbo'
import Echo from '../echo'

const subscribeTo = (type, channel) => {
    if (type === "presence") {
        return Echo.join(channel)
    }

    return Echo[type](channel)
}

class TurboEchoStreamSourceElement extends HTMLElement {
    async connectedCallback() {
        connectStreamSource(this)
        this.subscription = subscribeTo(this.type, this.channel)
            .listen('.Tonysm\\TurboLaravel\\Events\\TurboStreamModelCreated', (e) => {
                this.dispatchMessageEvent(e.message)
            })
            .listen('.Tonysm\\TurboLaravel\\Events\\TurboStreamModelUpdated', (e) => {
                this.dispatchMessageEvent(e.message)
            })
            .listen('.Tonysm\\TurboLaravel\\Events\\TurboStreamModelDeleted', (e) => {
                this.dispatchMessageEvent(e.message)
            })
    }

    disconnectedCallback() {
        disconnectStreamSource(this)
        if (this.subscription) {
            Echo.leave(this.channel)
            this.subscription = null
        }
    }

    dispatchMessageEvent(data) {
        const event = new MessageEvent("message", { data })
        return this.dispatchEvent(event)
    }

    get channel() {
        return this.getAttribute("channel")
    }

    get type() {
        return this.getAttribute("type") || "private"
    }
}

customElements.define("turbo-echo-stream-source", TurboEchoStreamSourceElement)
