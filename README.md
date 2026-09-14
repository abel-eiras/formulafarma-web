# Fórmula Farma

El portal de un movimiento de software libre para farmacia. Nada de
propuesta comercial, nada de "contacta con ventas" — solo la puerta de
entrada a los programas que un farmacéutico con teclado va soltando en
abierto.

Esto **no** es la aplicación. Es la web que los presenta: el manifiesto,
las fichas de cada programa y el formulario de contacto. El código de
verdad vive en sus propios repos:

- **[Fórmula Care](https://github.com/abel-eiras/formula-care)** — servicios
  asistenciales de farmacia (dermocosmética, parámetros bioquímicos, fichas
  de paciente). App de escritorio, código público, licencia MIT.
- **SPD** — gestión documental del servicio de Sistemas Personalizados de
  Dosificación para farmacias gallegas que lo hacen a mano. En desarrollo,
  repo aún no público.

## Por qué esto es libre también

Sería raro predicar software libre para farmacia con una web de código
cerrado. Así que esto también es MIT — mira el `LICENSE`, cópialo, ríete
del copy si quieres, o mándame un PR si ves una errata o un enlace roto.

## Cómo levantarlo en local

```bash
npm install
npm run dev
```

Y ya está en `http://localhost:4321`. Nada de bases de datos, nada de
variables de entorno obligatorias — es un sitio estático.

## Qué hay debajo

- **[Astro](https://astro.build)** + **Tailwind CSS**, sin frameworks de
  más peso porque esto es una web, no una aplicación.
- Componentes con estética brutalista (`brutal-border`, `brutal-hover`...
  sí, se llaman literalmente así) en `src/components/`.
- Un formulario de contacto que habla con `public/contact.php` — sí, PHP,
  porque el hosting es compartido y no hace falta más para mandar un email.

```
src/
├── components/       # Hero, Navbar, Footer, y las fichas de cada programa
│   ├── care/         # Ficha de Fórmula Care
│   └── spd/          # Ficha de SPD
├── layouts/
├── pages/             # Rutas: /, /formula-care, /spd, legales...
└── styles/
```

## Deploy

Push a `master` y GitHub Actions lo sube por FTP a producción, solo. La
guía completa (por si algún día hay que tocarlo o replicarlo) está en
[`GUIA_DEPLOY_ASTRO_RAIOLA.md`](GUIA_DEPLOY_ASTRO_RAIOLA.md).

## Licencia

MIT. Ver [`LICENSE`](LICENSE).
