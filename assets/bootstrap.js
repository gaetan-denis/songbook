import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';

// Démarre l'application Stimulus
const application = Application.start();

// Charge automatiquement les contrôleurs depuis le dossier ./controllers
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));

console.log('Stimulus app started (via Webpack)');
