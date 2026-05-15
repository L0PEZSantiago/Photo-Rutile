# Menu burger mobile (navbar)

Ce document décrit le fonctionnement du **menu burger** sur mobile : structure HTML, logique Stimulus, animation CSS et chargement des assets.

---

## 1. Vue d'ensemble

Sur les écrans **≤ 768 px**, la barre de navigation classique (liens horizontaux) est masquée. Un bouton **☰** (burger) apparaît à la place. Au clic :

1. Un **overlay plein écran** glisse depuis le haut (animation CSS).
2. L'utilisateur voit le logo, le nom du site, une croix de fermeture et la liste des liens.
3. Le scroll de la page est **bloqué** tant que le menu est ouvert.

La fermeture est possible via la **croix**, un **lien du menu**, ou la touche **Échap**.

Sur **desktop**, les liens `.navbar-links` restent visibles et le bouton burger est caché.

---

## 2. Fichiers concernés

| Rôle | Fichier |
|------|---------|
| Structure HTML et attributs Stimulus | `templates/layouts/navbar.html.twig` |
| Logique JavaScript (Stimulus) | `assets/controllers/burger_controller.js` |
| Styles et animation | `assets/styles/frontOffice/navbar.css` |
| Import des styles navbar | `assets/scripts/frontOffice/navbar.js` |
| Démarrage de Stimulus | `assets/stimulus_bootstrap.js` (via `assets/app.js`) |
| Inclusion navbar sur toutes les pages | `templates/base.html.twig` |
| Icône SVG du burger | `config/packages/twig.yaml` (`iconsCollection.burger`) |
| Entrées JavaScript chargées globalement | `importmap.php` (`app`, `navbar`) |

---

## 3. Stimulus : concepts utiles

Stimulus relie le **HTML** et le **JavaScript** via des attributs `data-*`. On n'a pas besoin de `document.querySelector` pour chaque élément.

| Attribut | Rôle |
|----------|------|
| `data-controller="burger"` | Active le contrôleur `burger` sur cet élément et ses descendants |
| `data-burger-target="overlay"` | Déclare une **cible** nommée `overlay` (accessible via `this.overlayTarget` en JS) |
| `data-action="click->burger#toggle"` | Au **clic**, appelle la méthode `toggle()` du contrôleur `burger` |

Le nom du contrôleur (`burger`) provient du fichier `burger_controller.js` : Symfony Stimulus Bundle l'enregistre automatiquement depuis `assets/controllers/`.

**Syntaxe d'une action :** `événement->contrôleur#méthode`

Exemple : `click->burger#close` = au clic, exécuter `close()` dans `burger_controller.js`.

---

## 4. Structure HTML (`navbar.html.twig`)

### 4.1 Conteneur principal

Tout le menu burger est regroupé dans un wrapper qui porte le contrôleur Stimulus :

```twig
<div class="navbar-wrapper" data-controller="burger">
```

Stimulus instancie `burger_controller.js` et appelle `connect()` au chargement de la page.

### 4.2 Navigation desktop + bouton burger

```twig
<nav class="navbar">
    <div class="container-navbar">
        <div class="logo-navbar">...</div>
        <ul class="navbar-links">...</ul>
        <button
            type="button"
            class="header-menu-icon"
            data-burger-target="toggle"
            data-action="click->burger#toggle"
            aria-label="Ouvrir le menu"
            aria-expanded="false"
            aria-controls="mobile-menu"
        >
            {{ iconsCollection.burger|raw }}
        </button>
    </div>
</nav>
```

| Élément | Rôle |
|---------|------|
| `.navbar-links` | Liens horizontaux (desktop uniquement, masqués en mobile via CSS) |
| `.header-menu-icon` | Bouton ☰ (visible uniquement en mobile) |
| `data-burger-target="toggle"` | Cible Stimulus : le bouton burger (`this.toggleTarget`) |
| `data-action="click->burger#toggle"` | Ouvre ou ferme le menu selon l'état actuel |
| `aria-expanded` | Indique aux technologies d'assistance si le menu est ouvert |
| `aria-controls="mobile-menu"` | Lie le bouton à l'overlay (`id="mobile-menu"`) |

### 4.3 Overlay mobile (menu plein écran)

```twig
<div
    id="mobile-menu"
    class="header-menu-mobile-overlay"
    data-burger-target="overlay"
    aria-hidden="true"
>
    <div class="header-menu-mobile-header">
        <!-- logo, nom du site, bouton fermer -->
    </div>
    <ul class="header-menu-mobile">
        <li>
            <a href="..." data-action="click->burger#close">Accueil</a>
        </li>
        ...
    </ul>
</div>
```

| Élément | Rôle |
|---------|------|
| `data-burger-target="overlay"` | Cible Stimulus : le panneau plein écran (`this.overlayTarget`) |
| `aria-hidden="true"` | Menu considéré comme caché tant qu'il n'est pas ouvert |
| `data-action="click->burger#close"` | Ferme le menu avant la navigation (liens et croix) |

---

## 5. Logique front-end (`burger_controller.js`)

### 5.1 Cibles déclarées

```js
static targets = ['overlay', 'toggle'];
```

| Propriété Stimulus | Élément HTML |
|--------------------|--------------|
| `this.overlayTarget` | `div.header-menu-mobile-overlay` |
| `this.toggleTarget` | `button.header-menu-icon` |
| `this.hasToggleTarget` | `true` si le bouton burger est présent dans le DOM |

### 5.2 Cycle de vie

| Méthode | Quand | Action |
|---------|-------|--------|
| `connect()` | L'élément `data-controller="burger"` apparaît dans la page | Ajoute un écouteur `keydown` sur `document` pour la touche **Échap** |
| `disconnect()` | L'élément disparaît (ex. navigation Turbo) | Retire l'écouteur `keydown` (évite les fuites mémoire) |

### 5.3 Méthodes publiques

#### `toggle()`

```js
toggle() {
    const isOpen = this.overlayTarget.classList.contains('is-open');
    isOpen ? this.close() : this.open();
}
```

Vérifie si la classe `is-open` est présente sur l'overlay : si oui → ferme, sinon → ouvre.

#### `open()`

| Action | Effet |
|--------|--------|
| `classList.add('is-open')` | Déclenche l'animation CSS (menu visible) |
| `aria-hidden="false"` | Menu annoncé comme visible |
| `aria-expanded="true"` sur le bouton ☰ | Bouton indique « menu ouvert » |
| `document.body.style.overflow = 'hidden'` | Empêche le scroll de la page derrière le menu |

#### `close()`

| Action | Effet |
|--------|--------|
| `classList.remove('is-open')` | Déclenche l'animation de fermeture |
| `aria-hidden="true"` | Menu annoncé comme caché |
| `aria-expanded="false"` sur le bouton ☰ | Bouton indique « menu fermé » |
| `document.body.style.overflow = ''` | Rétablit le scroll de la page |

### 5.4 Qui appelle quoi ?

| Interaction | Mécanisme |
|-------------|-----------|
| Clic sur ☰ | `data-action="click->burger#toggle"` |
| Clic sur × | `data-action="click->burger#close"` |
| Clic sur un lien du menu | `data-action="click->burger#close"` sur chaque `<a>` |
| Touche Échap | `connect()` → `keydown` → `close()` |

---

## 6. Styles et animation (`navbar.css`)

### 6.1 Principe

**Stimulus ne gère pas l'animation.** Il ajoute ou retire uniquement la classe `is-open`. Le CSS fait le reste avec `transform` et `transition`.

### 6.2 État fermé (par défaut)

```css
.header-menu-mobile-overlay {
    position: fixed;
    inset: 0;
    z-index: 1100;
    transform: translateY(-100%);
    visibility: hidden;
    pointer-events: none;
    transition:
        transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
        visibility 0s linear 0.35s;
}
```

| Propriété | Effet |
|-----------|--------|
| `translateY(-100%)` | Menu positionné **au-dessus** de l'écran (hors vue) |
| `visibility: hidden` | Invisible |
| `pointer-events: none` | Les clics traversent l'overlay (on ne clique pas un menu invisible) |
| `z-index: 1100` | Au-dessus de la navbar fixe (`z-index: 1000`) |

### 6.3 État ouvert (classe ajoutée par JS)

```css
.header-menu-mobile-overlay.is-open {
    transform: translateY(0);
    visibility: visible;
    pointer-events: auto;
    transition:
        transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
        visibility 0s linear 0s;
}
```

| Transition | Résultat visuel |
|------------|-----------------|
| `-100%` → `0` | Le menu **descend** depuis le haut (ouverture) |
| `0` → `-100%` | Le menu **remonte** et disparaît (fermeture) |

### 6.4 Responsive

```css
@media (max-width: 768px) {
    .navbar-links { display: none; }
    .header-menu-icon { display: flex; }
}
```

En dehors de cette media query, `.header-menu-icon` a `display: none` : le burger n'apparaît que sur mobile.

---

## 7. Chargement des assets

### 7.1 Sur chaque page (`base.html.twig`)

```twig
{{ importmap(['app', 'navbar']) }}
```

| Entrée | Rôle |
|--------|------|
| `app` | Charge `assets/app.js` → `stimulus_bootstrap.js` → démarre Stimulus et enregistre les contrôleurs |
| `navbar` | Charge `assets/scripts/frontOffice/navbar.js` → importe `navbar.css` |

Le contrôleur `burger` est découvert automatiquement : **aucun import manuel** de `burger_controller.js` n'est nécessaire dans `navbar.js`.

### 7.2 Inclusion du template

```twig
{% include 'layouts/navbar.html.twig' %}
```

Le menu burger est donc disponible sur **toutes les pages** qui étendent `base.html.twig`.

---

## 8. Schéma du flux complet

### Ouverture

```
Clic sur ☰
    → Stimulus : click->burger#toggle
    → toggle() : pas de classe is-open
    → open()
        → overlay.classList.add('is-open')
        → aria-hidden / aria-expanded mis à jour
        → body overflow hidden
    → CSS : translateY(-100%) → translateY(0)
    → Menu visible, slide du haut vers le bas
```

### Fermeture

```
Clic sur × / lien / Échap
    → close()
        → overlay.classList.remove('is-open')
        → aria-hidden / aria-expanded remis
        → body overflow rétabli
    → CSS : translateY(0) → translateY(-100%)
    → Menu remonte et disparaît
```

---

## 9. Schéma des relations HTML ↔ JS ↔ CSS

```
┌──────────────────────────────────────────────────────────────┐
│  div.navbar-wrapper  [data-controller="burger"]                 │
│                                                               │
│  ┌─ nav.navbar ──────────────────────────────────────────┐  │
│  │  ul.navbar-links              (desktop, caché mobile)  │  │
│  │  button.header-menu-icon                               │  │
│  │    [data-burger-target="toggle"]  ──► toggleTarget     │  │
│  │    [data-action="click->burger#toggle"] ──► toggle()   │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌─ div.header-menu-mobile-overlay ────────────────────────┐  │
│  │    [data-burger-target="overlay"]  ──► overlayTarget    │  │
│  │                                                         │  │
│  │    sans is-open : translateY(-100%)  [fermé]             │  │
│  │    avec is-open  : translateY(0)     [ouvert]  ◄── JS   │  │
│  │                                                         │  │
│  │    bouton ×  [data-action="click->burger#close"]       │  │
│  │    liens     [data-action="click->burger#close"]       │  │
│  └─────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## 10. Modifier le menu

### Ajouter un lien mobile

Dans `navbar.html.twig`, dans `<ul class="header-menu-mobile">` :

```twig
<li>
    <a href="{{ path('ma_route') }}" data-action="click->burger#close">Mon lien</a>
</li>
```

N'oublie pas `data-action="click->burger#close"` pour fermer le menu avant la navigation.

### Ajouter un bouton qui ferme le menu

À l'intérieur de `data-controller="burger"` :

```twig
<button type="button" data-action="click->burger#close">Fermer</button>
```

### Changer la vitesse d'animation

Dans `navbar.css`, modifier `0.35s` dans les propriétés `transition` de `.header-menu-mobile-overlay` et `.header-menu-mobile-overlay.is-open`.

### Changer le breakpoint mobile / desktop

Modifier la valeur `768px` dans la media query `@media (max-width: 768px)` de `navbar.css`.

---

## 11. Dépannage

| Problème | Cause probable | Piste |
|----------|----------------|-------|
| Le clic sur ☰ ne fait rien | Stimulus non chargé | Vérifier que `importmap(['app', ...])` inclut `app` dans `base.html.twig` |
| Erreur console « burger controller not found » | Fichier mal nommé ou hors de `assets/controllers/` | Le fichier doit s'appeler `burger_controller.js` |
| Le menu est toujours visible | Classe `is-open` présente au chargement | Vérifier qu'aucun CSS/HTML n'ajoute `is-open` par défaut |
| Pas d'animation | CSS non chargé | Vérifier que `navbar` est dans l'importmap |
| Le menu s'ouvre mais la page scroll derrière | `open()` non appelé ou surchargé | Vérifier `document.body.style.overflow` dans `open()` |
| Échap ne ferme pas | `connect()` non exécuté | Vérifier que `data-controller="burger"` entoure bien l'overlay |

---

## 12. Récapitulatif

| Couche | Responsabilité |
|--------|----------------|
| **Twig** | Structure, liens, attributs `data-controller`, `data-target`, `data-action` |
| **Stimulus** | Ouvrir / fermer : ajouter ou retirer `is-open`, ARIA, bloquer le scroll |
| **CSS** | Apparence, responsive, animation slide vertical |
| **État ouvert / fermé** | Présence ou absence de la classe `is-open` sur `.header-menu-mobile-overlay` |
