import React from 'react';
import { render } from '@wordpress/element';
import App from './App';

/**
 * Dynamically create a mount node so PHP doesn't need to output a specific div.
 * App renders the Redux Provider — this node is purely a React root for the useEffect to fire.
 */
const mount = document.createElement('div');
mount.id = 'lazytasks-performance-app';
document.body.appendChild(mount);

render(<App />, mount);
