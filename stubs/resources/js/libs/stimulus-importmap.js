import { Application } from '@hotwired/stimulus'
import registerControllers from 'controllers'

const App = Application.start()
window.Stimulus = App

registerControllers(Stimulus);

export default App
