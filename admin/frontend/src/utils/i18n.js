export function translate(key) {
    return window.appLocalizerPerformance?.i18n?.[key] || key;
}
