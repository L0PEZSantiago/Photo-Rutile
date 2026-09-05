# Le Sitemap XML dynamique

Ce document explique **ce qu'est un sitemap**, **pourquoi on en a créé un**, et **comment il fonctionne techniquement** de bout en bout — fichier par fichier, ligne par ligne. Il s'adresse à quelqu'un qui n'a aucune connaissance préalable du sujet.

---

## 1. C'est quoi un sitemap, et pourquoi en avoir un ?

### Le problème : comment Google trouve-t-il les pages de votre site ?

Quand Google (ou Bing, ou n'importe quel moteur de recherche) veut référencer votre site, il envoie un robot (appelé « crawler » ou « spider ») qui visite votre site. Ce robot part de votre page d'accueil, lit les liens (`<a href="...">`) présents sur la page, puis visite chaque lien, et ainsi de suite.

C'est un peu comme si vous lisiez un livre en suivant uniquement les renvois en bas de page.

**Le problème :** si une page n'est jamais liée depuis une autre page (ou si le lien est difficile à trouver), le robot risque de ne jamais la découvrir. Elle ne sera donc jamais référencée.

### La solution : le sitemap

Un **sitemap** (littéralement « carte du site ») est un fichier spécial, au format XML, que vous mettez à disposition des moteurs de recherche à une adresse connue : `/sitemap.xml`.

Ce fichier contient la **liste exhaustive de toutes les pages importantes** de votre site, avec des informations supplémentaires pour chaque page :

- `<loc>` — l'URL complète de la page
- `<lastmod>` — la date de dernière modification (pour que Google sache si la page a changé depuis sa dernière visite)
- `<changefreq>` — à quelle fréquence la page change (quotidiennement, hebdomadairement, etc.)
- `<priority>` — l'importance relative de la page par rapport aux autres (de 0.0 à 1.0)

En déclarant ensuite `Sitemap: https://photorutile.fr/sitemap.xml` dans votre fichier `robots.txt`, vous indiquez à Google exactement où trouver ce fichier dès sa première visite.

---

## 2. Sitemap statique vs sitemap dynamique

On aurait pu créer un simple fichier `sitemap.xml` à la main, posé dans le dossier `public/`. Cela aurait ressemblé à ceci :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://photorutile.fr/</loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://photorutile.fr/creations/42/details</loc>
        <priority>0.8</priority>
    </url>
    <!-- ... -->
</urlset>
```

**Le problème de cette approche :**
A chaque fois qu'une nouvelle création est ajoutée en base de données, il faudrait penser à modifier ce fichier à la main pour y ajouter la nouvelle URL. C'est fastidieux, source d'erreurs, et très vite on oublie de le faire.

**Notre approche : le sitemap dynamique.**
Le sitemap n'est pas un fichier figé sur le disque. C'est une **page web générée à la volée par Symfony** au moment où Google (ou n'importe qui) visite l'adresse `/sitemap.xml`. Symfony interroge alors la base de données pour récupérer la liste des créations publiées, puis construit le XML en temps réel. Ainsi, le sitemap est **toujours à jour** sans aucune intervention manuelle.

---

## 3. Les fichiers concernés

| Rôle | Fichier |
|------|---------|
| Controleur : reçoit la requête, construit les données, renvoie la réponse | `src/Controller/SitemapController.php` |
| Template : transforme les données en XML bien formaté | `templates/sitemap/sitemap.xml.twig` |
| Entité utilisée pour récupérer les créations | `src/Entity/Creation.php` |
| Repository : méthode de requête en base de données | `src/Repository/CreationRepository.php` (méthode `findBy`) |

---

## 4. Le flux de données, de la requête à la réponse

Voici ce qui se passe **dans l'ordre**, depuis le moment où Google visite `https://photorutile.fr/sitemap.xml` :

```
Google (ou navigateur)
        |
        | GET /sitemap.xml
        v
   Symfony (routeur)
        |
        | La route 'app.sitemap' correspond a /sitemap.xml
        | Symfony appelle SitemapController::index()
        v
   SitemapController
        |
        | 1. Construit la liste des pages statiques (accueil, contact, etc.)
        | 2. Interroge CreationRepository pour les créations publiées
        | 3. Ajoute chaque création à la liste d'URLs
        | 4. Passe toutes les URLs au template Twig
        v
   Template Twig (sitemap.xml.twig)
        |
        | Génère le XML en bouclant sur la liste d'URLs
        v
   Réponse HTTP
        |
        | Content-Type: application/xml
        | Corps : le XML généré
        v
   Google reçoit le XML et peut indexer toutes les pages
```

---

## 5. Explication du controleur — `SitemapController.php`

### Les imports en haut du fichier

```php
namespace App\Controller;

use App\Repository\CreationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
```

- **`namespace App\Controller`** : déclare que ce fichier appartient à l'espace de noms `App\Controller`. C'est une convention Symfony : tous les controleurs vivent dans `src/Controller/`.
- **`use ...`** : importe les classes dont on a besoin. Sans ces imports, PHP ne saurait pas ce qu'est un `Response` ou un `Route`. C'est l'équivalent d'un `import` en JavaScript.
- **`UrlGeneratorInterface`** : c'est l'outil de Symfony qui permet de générer des URLs absolues (avec le domaine complet, ex. `https://photorutile.fr/creations`) à partir d'un nom de route (ex. `app.creation.index`). C'est important pour le sitemap : Google a besoin d'URLs complètes, pas de chemins relatifs comme `/creations`.

### La classe et son héritage

```php
class SitemapController extends AbstractController
```

Ce controleur **hérite** de `AbstractController`, la classe de base de Symfony pour tous les controleurs. Cela lui donne accès à des méthodes pratiques comme `$this->generateUrl()`, `$this->render()`, `$this->renderView()`. Penser à un employé qui hérite des outils de son entreprise.

### La route

```php
#[Route('/sitemap.xml', name: 'app.sitemap', defaults: ['_format' => 'xml'])]
public function index(CreationRepository $creationRepository): Response
```

- **`#[Route('/sitemap.xml', ...)]`** : c'est un **attribut PHP** (une annotation moderne). Il indique à Symfony que lorsqu'une requête arrive sur l'URL `/sitemap.xml`, c'est la méthode `index()` qui doit être appelée.
- `name: 'app.sitemap'` : un identifiant interne pour cette route. On pourrait l'utiliser pour générer l'URL depuis Twig : `{{ path('app.sitemap') }}`.
- `defaults: ['_format' => 'xml']` : indique que le format attendu en sortie est XML. C'est documentaire, cela n'a pas d'effet technique visible ici.
- **`CreationRepository $creationRepository`** : c'est de **l'injection de dépendances**. Symfony voit que la méthode demande un `CreationRepository` en paramètre, et le lui fournit automatiquement. On n'a pas besoin d'écrire `new CreationRepository(...)` à la main. C'est Symfony qui s'en charge.

### La construction des URLs statiques

```php
$staticUrls = [
    [
        'loc'        => $this->generateUrl('app.home', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod'    => null,
        'changefreq' => 'weekly',
        'priority'   => '1.0',
    ],
    // ...
];
```

On construit un **tableau PHP** (une liste) contenant les pages dont l'URL ne change jamais : l'accueil, la liste des créations, le contact, la localisation.

**`$this->generateUrl('app.home', [], UrlGeneratorInterface::ABSOLUTE_URL)`** :
- `'app.home'` : le nom interne de la route, défini dans `HomeController.php` avec `#[Route('/', name: 'app.home')]`.
- `[]` : les paramètres de la route (ici aucun, car `/` ne prend aucun paramètre variable).
- `UrlGeneratorInterface::ABSOLUTE_URL` : demande à Symfony de générer l'URL **complète** avec le domaine. Sans cette option, on obtiendrait juste `/`, ce qui est inutilisable pour un sitemap.

**`'lastmod' => null`** : pour les pages statiques, on ne connaît pas de date de modification précise. Le template Twig n'affichera pas la balise `<lastmod>` dans ce cas.

**`'priority' => '1.0'`** : l'accueil est la page la plus importante du site.

### La récupération des créations en base de données

```php
$creations = $creationRepository->findBy(['isPublished' => true]);
```

**`findBy(['isPublished' => true])`** : interroge la base de données via Doctrine (l'ORM de Symfony). Cela exécute en coulisses une requête SQL équivalente à :

```sql
SELECT * FROM creation WHERE is_published = true;
```

On récupère uniquement les créations visibles par le public. Les créations non publiées ne doivent pas apparaître dans le sitemap.

### La boucle sur les créations

```php
foreach ($creations as $creation) {
    $lastmod = $creation->getUpdatedAt() ?? $creation->getCreatedAt();

    $dynamicUrls[] = [
        'loc'        => $this->generateUrl('app.creation.show', ['id' => $creation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod'    => $lastmod?->format('Y-m-d'),
        'changefreq' => 'monthly',
        'priority'   => '0.8',
    ];
}
```

On **boucle** sur chaque création. Pour chacune :

- **`$creation->getUpdatedAt() ?? $creation->getCreatedAt()`** : l'opérateur `??` signifie « si la valeur de gauche est `null`, prends celle de droite ». Autrement dit : on préfère la date de modification ; si la création n'a jamais été modifiée (`updatedAt` est null), on utilise la date de création.

- **`$this->generateUrl('app.creation.show', ['id' => $creation->getId()], ...)`** : génère l'URL `/creations/42/details` avec l'ID réel de la création. Le paramètre `['id' => $creation->getId()]` remplace `{id}` dans le pattern de la route `/creations/{id}/details`.

- **`$lastmod?->format('Y-m-d')`** : le `?->` est l'opérateur « nullsafe ». Si `$lastmod` vaut `null`, l'expression retourne `null` sans erreur. Sinon, `format('Y-m-d')` formate la date au format ISO 8601 (ex. `2026-09-05`), seul format accepté dans un sitemap XML.

- **`$dynamicUrls[]`** : le `[]` sans index ajoute un élément à la fin du tableau.

### La fusion des deux listes et la réponse

```php
$urls = array_merge($staticUrls, $dynamicUrls);
```

`array_merge` fusionne les deux tableaux en un seul. Le résultat contient d'abord les 4 pages statiques, puis toutes les créations. C'est cette liste qui sera envoyée au template.

```php
$response = new Response(
    $this->renderView('sitemap/sitemap.xml.twig', ['urls' => $urls]),
    Response::HTTP_OK,
    ['Content-Type' => 'application/xml']
);
```

On construit la **réponse HTTP** à la main pour pouvoir définir précisément les headers :

- **`$this->renderView(...)`** : fait exécuter le template Twig et retourne le résultat sous forme de chaîne de caractères (le XML). On lui passe `['urls' => $urls]` pour que la variable `urls` soit disponible dans le template.
- **`Response::HTTP_OK`** : le code HTTP 200 (succès).
- **`'Content-Type' => 'application/xml'`** : ce header indique au navigateur (ou au robot) que la réponse est du XML, pas du HTML.

### Le cache

```php
$response->setPublic();
$response->setMaxAge(86400);
```

Les robots de Google peuvent passer plusieurs fois par jour. À chaque visite, sans cache, Symfony ferait une requête en base de données.

- **`setPublic()`** : indique que la réponse peut être mise en cache par n'importe quel intermédiaire (serveur de cache, CDN, etc.).
- **`setMaxAge(86400)`** : la réponse est valide pendant 86 400 secondes (= 24 heures). Pendant ce délai, si quelqu'un redemande `/sitemap.xml`, le serveur peut répondre depuis son cache sans ré-exécuter le controleur.

---

## 6. Explication du template — `sitemap.xml.twig`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {% for url in urls %}
    <url>
        <loc>{{ url.loc }}</loc>
        {% if url.lastmod %}
        <lastmod>{{ url.lastmod }}</lastmod>
        {% endif %}
        <changefreq>{{ url.changefreq }}</changefreq>
        <priority>{{ url.priority }}</priority>
    </url>
    {% endfor %}
</urlset>
```

- **`<?xml version="1.0" encoding="UTF-8"?>`** : la déclaration standard de tout fichier XML. Elle indique la version XML utilisée et l'encodage des caractères.

- **`<urlset xmlns="...">`** : la balise racine du sitemap. L'attribut `xmlns` (« XML namespace ») est obligatoire : il indique que ce fichier respecte le format officiel des sitemaps défini sur `sitemaps.org`. Sans lui, Google refuserait le fichier.

- **`{% for url in urls %}`** : boucle Twig. Pour chaque élément du tableau `$urls` passé par le controleur, on génère un bloc `<url>...</url>`. `url` est le nom de la variable locale dans la boucle.

- **`{{ url.loc }}`** : affiche la valeur de la clé `loc` du tableau courant. En Twig, `url.loc` est équivalent à `$url['loc']` en PHP.

- **`{% if url.lastmod %}`** : si la clé `lastmod` n'est pas `null`, on affiche la balise `<lastmod>`. Pour les pages statiques où `lastmod` vaut `null`, cette balise est simplement omise — ce qui est tout à fait valide dans le format sitemap.

---

## 7. Récapitulatif visuel du flux de données

```
Base de données (table "creation")
         |
         | findBy(['isPublished' => true])
         v
   CreationRepository
         |
         | Retourne une liste d'objets Creation
         v
   SitemapController::index()
         |
         | Construit un tableau PHP :
         | [
         |   ['loc' => 'https://photorutile.fr/', 'priority' => '1.0', ...],
         |   ['loc' => 'https://photorutile.fr/creations', ...],
         |   ['loc' => 'https://photorutile.fr/creations/1/details', 'lastmod' => '2026-08-10', ...],
         |   ['loc' => 'https://photorutile.fr/creations/2/details', 'lastmod' => '2026-09-01', ...],
         |   ...
         | ]
         |
         | Passe ce tableau au template Twig
         v
   Template sitemap.xml.twig
         |
         | Génère le XML en bouclant :
         | <?xml version="1.0" encoding="UTF-8"?>
         | <urlset ...>
         |   <url><loc>https://photorutile.fr/</loc>...</url>
         |   <url><loc>https://photorutile.fr/creations/1/details</loc>...</url>
         |   ...
         | </urlset>
         v
   Réponse HTTP (Content-Type: application/xml)
         v
   Google lit le XML → indexe toutes les pages du site
```

---

## 8. Pourquoi un Controller et pas un simple fichier statique ?

Un fichier XML posé dans `public/sitemap.xml` aurait fonctionné, mais il aurait fallu :

1. **Le créer manuellement** au départ.
2. **Le mettre à jour à chaque ajout ou suppression** de création.
3. **Ne jamais oublier** de le faire après chaque modification de contenu.

En utilisant un controleur Symfony, le sitemap est **automatiquement synchronisé avec la base de données** à chaque requête. Ajouter une création en back-office suffit : le sitemap la contiendra automatiquement dès la prochaine visite de Google.

---

## 9. Aller plus loin

Pour que Google utilise ce sitemap, il faut lui indiquer son existence.

### Option A — `robots.txt`

Ajoutez cette ligne dans votre fichier `public/robots.txt` :

```
Sitemap: https://photorutile.fr/sitemap.xml
```

### Option B — Google Search Console

Rendez-vous sur [Google Search Console](https://search.google.com/search-console), ajoutez votre site, et soumettez manuellement l'URL du sitemap dans la section « Sitemaps ». C'est la méthode recommandée car elle vous donne aussi des statistiques d'indexation (combien de pages indexées, lesquelles ont des erreurs, etc.).
