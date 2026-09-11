import { startStimulusApp } from '@symfony/stimulus-bridge';

// Registriert ALLE Stimulus-Controller aus assets/controllers/ und bündelt sie über
// Encore. ⚠ Ohne diesen require.context lädt startStimulusApp keine lokalen Controller —
// dann bleiben data-controller-Elemente (mobiles Menü `nav`, News-Formular, Löschen-
// Bestätigung) tot. (Vor der Encore-only-Umstellung liefen sie über die AssetMapper-
// importmap; siehe BF-13.)
const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/,
));

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
