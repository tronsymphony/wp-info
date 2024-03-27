const {createRoot} = wp.element;
import App from './App.js';

// Check if the element exists before rendering
const rootElement = document.getElementById( 'my-react-app' );
if (rootElement) {
	// Use createRoot to manage the root of your app
	const root = createRoot( rootElement );
	root.render( <App /> );
}
