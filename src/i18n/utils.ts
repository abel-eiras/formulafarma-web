import { ui, defaultLang } from './ui';

export function getLangFromUrl(url: URL) {
  const [, lang] = url.pathname.split('/');
  if (lang in ui) return lang as keyof typeof ui;
  return defaultLang;
}

export function useTranslations(lang: keyof typeof ui) {
  return function t(key: keyof typeof ui[typeof defaultLang]) {
    return ui[lang][key] || ui[defaultLang][key];
  }
}

export function useTranslatedPath(lang: keyof typeof ui) {
  return function translatePath(path: string, l: string = lang) {
    // Traducir nombres de rutas según el idioma
    const routeTranslations: Record<string, { gl: string; es: string }> = {
      '/sobre-mi': { gl: '/sobre-min', es: '/sobre-mi' },
      '/sobre-min': { gl: '/sobre-min', es: '/sobre-mi' },
      '/privacidad': { gl: '/privacidade', es: '/privacidad' },
      '/privacidade': { gl: '/privacidade', es: '/privacidad' },
    };
    
    // Si la ruta tiene traducción, usar la versión correcta
    if (routeTranslations[path]) {
      const translatedPath = routeTranslations[path][l as 'gl' | 'es'];
      return l === defaultLang ? translatedPath : `/${l}${translatedPath}`;
    }
    
    // Ruta normal sin traducción
    return l === defaultLang ? path : `/${l}${path}`;
  }
}

export function getRouteFromUrl(url: URL): { lang: keyof typeof ui; slug: string } {
  const pathname = new URL(url).pathname;
  const path = pathname.split('/');
  const pathIsEmpty = pathname === '/';

  if (path[1] && path[1] in ui) {
    const lang = path[1] as keyof typeof ui;
    const slug = pathIsEmpty ? 'index' : path.slice(2).join('/');
    return { lang, slug };
  }

  const slug = pathIsEmpty ? 'index' : path.slice(1).join('/');
  return { lang: defaultLang, slug };
}

